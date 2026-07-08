import { beforeEach, describe, expect, it, vi } from 'vitest'
import { csrfState, initCsrf, postForm, resetCsrf, updateCsrfHash } from '@/lib/csrf'

function jsonResponse(body: unknown, ok = true, status = 200) {
  return {
    ok,
    status,
    json: () => Promise.resolve(body),
  } as Response
}

beforeEach(() => {
  resetCsrf()
  vi.restoreAllMocks()
})

describe('csrf store', () => {
  it('keeps the first token registered by an island', () => {
    initCsrf('csrf_token', 'hash-1')
    initCsrf('other_name', 'hash-other')
    expect(csrfState()).toEqual({ tokenName: 'csrf_token', tokenHash: 'hash-1' })
  })

  it('updates the shared hash for every island', () => {
    initCsrf('csrf_token', 'hash-1')
    updateCsrfHash('hash-2')
    expect(csrfState().tokenHash).toBe('hash-2')
  })

  it('ignores empty hash updates', () => {
    initCsrf('csrf_token', 'hash-1')
    updateCsrfHash(undefined)
    expect(csrfState().tokenHash).toBe('hash-1')
  })
})

describe('postForm', () => {
  it('appends fields and the shared CSRF token, and refreshes the hash', async () => {
    initCsrf('csrf_token', 'hash-1')
    const fetchMock = vi.fn().mockResolvedValue(jsonResponse({ success: true, csrfHash: 'hash-2' }))
    vi.stubGlobal('fetch', fetchMock)

    const data = await postForm('/action/vote', { ideaid: 5, votes: 2 })

    expect(data.success).toBe(true)
    expect(csrfState().tokenHash).toBe('hash-2')

    const [url, options] = fetchMock.mock.calls[0]
    expect(url).toBe('/action/vote')
    expect(options.headers['X-Requested-With']).toBe('XMLHttpRequest')
    const body = options.body as FormData
    expect(body.get('ideaid')).toBe('5')
    expect(body.get('votes')).toBe('2')
    expect(body.get('csrf_token')).toBe('hash-1')
  })

  it('shares the refreshed hash with subsequent posts from other islands', async () => {
    initCsrf('csrf_token', 'hash-1')
    const fetchMock = vi
      .fn()
      .mockResolvedValueOnce(jsonResponse({ success: true, csrfHash: 'hash-2' }))
      .mockResolvedValueOnce(jsonResponse({ success: true, csrfHash: 'hash-3' }))
    vi.stubGlobal('fetch', fetchMock)

    await postForm('/action/vote', { ideaid: 1 })
    await postForm('/action/comment', { idea_id: 1, content: 'hi' })

    const secondBody = fetchMock.mock.calls[1][1].body as FormData
    expect(secondBody.get('csrf_token')).toBe('hash-2')
    expect(csrfState().tokenHash).toBe('hash-3')
  })

  it('throws on non-JSON error responses but still refreshes the hash on JSON errors', async () => {
    initCsrf('csrf_token', 'hash-1')
    vi.stubGlobal('fetch', vi.fn().mockResolvedValue({
      ok: false,
      status: 403,
      json: () => Promise.reject(new Error('not json')),
    } as unknown as Response))

    await expect(postForm('/action/vote', {})).rejects.toThrow('403')

    vi.stubGlobal('fetch', vi.fn().mockResolvedValue(
      jsonResponse({ success: false, error: 'Invalid status', csrfHash: 'hash-9' }, false, 400),
    ))
    await expect(postForm('/action/status', {})).rejects.toThrow('Invalid status')
    expect(csrfState().tokenHash).toBe('hash-9')
  })
})
