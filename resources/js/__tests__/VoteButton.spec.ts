import { beforeEach, describe, expect, it, vi } from 'vitest'
import { flushPromises, mount } from '@vue/test-utils'
import VoteButton from '@/components/VoteButton.vue'
import { csrfState, resetCsrf } from '@/lib/csrf'

function jsonResponse(body: unknown, ok = true, status = 200) {
  return { ok, status, json: () => Promise.resolve(body) } as Response
}

const baseProps = {
  ideaId: 7,
  initialTotalVotes: 5,
  initialUserVoteId: null,
  initialUserVoteAmount: 0,
  csrfTokenName: 'csrf_token',
  initialCsrfHash: 'hash-1',
  voteUrl: '/action/vote',
  unvoteUrl: '/action/unvote',
  labels: {
    votes: 'Votos',
    vote: 'Votar',
    removeVote: 'Remover voto',
    error: 'Algo correu mal.',
  },
}

beforeEach(() => {
  resetCsrf()
  vi.restoreAllMocks()
})

describe('VoteButton', () => {
  it('renders the count, translated labels and pointer cursors', () => {
    const wrapper = mount(VoteButton, { props: baseProps })
    expect(wrapper.text()).toContain('5')
    expect(wrapper.text()).toContain('Votos')
    expect(wrapper.text()).toContain('Votar')
    for (const button of wrapper.findAll('button')) {
      expect(button.classes()).toContain('cursor-pointer')
    }
  })

  it('posts a vote with the CSRF token and updates count and selection', async () => {
    const fetchMock = vi.fn().mockResolvedValue(jsonResponse({
      success: true,
      totalVotes: 7,
      userVoteId: 42,
      votes: 2,
      csrfHash: 'hash-2',
    }))
    vi.stubGlobal('fetch', fetchMock)

    const wrapper = mount(VoteButton, { props: baseProps })
    await wrapper.findAll('button')[1].trigger('click') // "2"
    await flushPromises()

    expect(wrapper.text()).toContain('7')
    expect(wrapper.text()).toContain('Remover voto')
    expect(csrfState().tokenHash).toBe('hash-2')

    const body = fetchMock.mock.calls[0][1].body as FormData
    expect(body.get('ideaid')).toBe('7')
    expect(body.get('votes')).toBe('2')
    expect(body.get('csrf_token')).toBe('hash-1')
  })

  it('removes the vote via the unvote endpoint', async () => {
    const fetchMock = vi.fn().mockResolvedValue(jsonResponse({
      success: true,
      totalVotes: 3,
      csrfHash: 'hash-2',
    }))
    vi.stubGlobal('fetch', fetchMock)

    const wrapper = mount(VoteButton, {
      props: { ...baseProps, initialUserVoteId: 42, initialUserVoteAmount: 2 },
    })
    await wrapper.find('button.text-destructive').trigger('click')
    await flushPromises()

    expect(fetchMock.mock.calls[0][0]).toBe('/action/unvote')
    expect((fetchMock.mock.calls[0][1].body as FormData).get('id')).toBe('42')
    expect(wrapper.text()).toContain('3')
    expect(wrapper.text()).toContain('Votar')
  })

  it('shows a visible error message when the request fails', async () => {
    vi.stubGlobal('fetch', vi.fn().mockRejectedValue(new Error('network down')))

    const wrapper = mount(VoteButton, { props: baseProps })
    await wrapper.findAll('button')[0].trigger('click')
    await flushPromises()

    expect(wrapper.find('[role="alert"]').text()).toBe('Algo correu mal.')
    expect(wrapper.text()).toContain('5') // count unchanged
  })

  it('ignores clicks on the already-selected amount', async () => {
    const fetchMock = vi.fn()
    vi.stubGlobal('fetch', fetchMock)

    const wrapper = mount(VoteButton, {
      props: { ...baseProps, initialUserVoteId: 42, initialUserVoteAmount: 1 },
    })
    await wrapper.findAll('button')[0].trigger('click') // "1" again
    await flushPromises()

    expect(fetchMock).not.toHaveBeenCalled()
  })
})
