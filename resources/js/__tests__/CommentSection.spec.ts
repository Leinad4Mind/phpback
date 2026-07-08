import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest'
import { flushPromises, mount } from '@vue/test-utils'
import CommentSection from '@/components/CommentSection.vue'
import { resetCsrf } from '@/lib/csrf'

function jsonResponse(body: unknown, ok = true, status = 200) {
  return { ok, status, json: () => Promise.resolve(body) } as Response
}

// The AlertDialog content teleports into document.body via reka-ui's portal,
// so modal buttons must be located outside the wrapper.
function findPortalButton(label: string): HTMLButtonElement {
  const btn = Array.from(document.body.querySelectorAll('button')).find(
    b => b.textContent?.trim() === label,
  )
  if (!btn) throw new Error(`portal button "${label}" not found`)
  return btn as HTMLButtonElement
}

// Factory: CommentSection keeps a live reference to initialComments, so each
// test needs a fresh array or mutations leak between tests.
const makeProps = () => ({
  ideaId: 7,
  isLoggedIn: true,
  isAdmin: false,
  initialComments: [
    { id: 1, user: 'Ana', userid: 2, date: '2026-01-01', content: 'First!' },
  ],
  csrfTokenName: 'csrf_token',
  initialCsrfHash: 'hash-1',
  submitUrl: '/action/comment',
  deleteUrl: '/adminaction/deletecomment',
  flagUrl: '/action/flag',
  baseUrl: 'http://localhost/',
  labels: {
    comments: 'Comentários',
    leaveComment: 'Deixe um comentário',
    submit: 'Enviar',
    submitting: 'A enviar...',
    delete: 'Eliminar',
    flag: 'Denunciar',
    noComments: 'Ainda não há comentários.',
    sureDelete: 'Eliminar?',
    sureFlag: 'Denunciar?',
    flagged: 'Denunciado.',
    error: 'Algo correu mal.',
  },
})

beforeEach(() => {
  resetCsrf()
  vi.restoreAllMocks()
})

afterEach(() => {
  document.body.innerHTML = ''
})

describe('CommentSection', () => {
  it('renders comments, translated heading and profile links', () => {
    const wrapper = mount(CommentSection, { props: makeProps() })
    expect(wrapper.text()).toContain('Comentários (1)')
    expect(wrapper.text()).toContain('First!')
    expect(wrapper.find('a').attributes('href')).toBe('http://localhost/home/profile/2')
  })

  it('shows the empty state when there are no comments', () => {
    const wrapper = mount(CommentSection, { props: { ...makeProps(), initialComments: [] } })
    expect(wrapper.text()).toContain('Ainda não há comentários.')
  })

  it('hides the form for guests', () => {
    const wrapper = mount(CommentSection, { props: { ...makeProps(), isLoggedIn: false } })
    expect(wrapper.find('form').exists()).toBe(false)
  })

  it('submits a comment and appends the response to the list', async () => {
    const fetchMock = vi.fn().mockResolvedValue(jsonResponse({
      success: true,
      comment: { id: 2, user: 'Rui', userid: 3, date: '2026-01-02', content: 'Nice idea' },
      csrfHash: 'hash-2',
    }))
    vi.stubGlobal('fetch', fetchMock)

    const wrapper = mount(CommentSection, { 
      props: makeProps(),
      global: { stubs: { WysiwygEditorIsland: true } }
    })
    const editor = wrapper.findComponent({ name: 'WysiwygEditorIsland' })
    await editor.vm.$emit('update:modelValue', 'Nice idea')
    await wrapper.find('form').trigger('submit')
    await flushPromises()

    expect(wrapper.text()).toContain('Comentários (2)')
    expect(wrapper.text()).toContain('Nice idea')
    expect(editor.props('modelValue')).toBe('')

    const body = fetchMock.mock.calls[0][1].body as FormData
    expect(body.get('idea_id')).toBe('7')
    expect(body.get('content')).toBe('Nice idea')
    expect(body.get('csrf_token')).toBe('hash-1')
  })

  it('deletes a comment (admin) after confirming in the modal', async () => {
    const fetchMock = vi.fn().mockResolvedValue(jsonResponse({ success: true, csrfHash: 'hash-2' }))
    vi.stubGlobal('fetch', fetchMock)

    const wrapper = mount(CommentSection, { props: { ...makeProps(), isAdmin: true } })
    const deleteButton = wrapper.findAll('button').find(b => b.text() === 'Eliminar')!
    expect(deleteButton.classes()).toContain('cursor-pointer')
    await deleteButton.trigger('click')
    await flushPromises()

    expect(document.body.textContent).toContain('Eliminar?')
    findPortalButton('Delete').click()
    await flushPromises()

    expect(fetchMock.mock.calls[0][0]).toBe('/adminaction/deletecomment')
    expect(wrapper.text()).toContain('Comentários (0)')
    wrapper.unmount()
  })

  it('does nothing when the confirmation is dismissed', async () => {
    const fetchMock = vi.fn()
    vi.stubGlobal('fetch', fetchMock)

    const wrapper = mount(CommentSection, { props: { ...makeProps(), isAdmin: true } })
    await wrapper.findAll('button').find(b => b.text() === 'Eliminar')!.trigger('click')
    await flushPromises()

    findPortalButton('Cancel').click()
    await flushPromises()

    expect(fetchMock).not.toHaveBeenCalled()
    wrapper.unmount()
  })

  it('flags a comment (logged-in non-admin) after confirming in the modal', async () => {
    const alertSpy = vi.spyOn(window, 'alert').mockImplementation(() => {})
    const fetchMock = vi.fn().mockResolvedValue(jsonResponse({ success: true, csrfHash: 'hash-2' }))
    vi.stubGlobal('fetch', fetchMock)

    const wrapper = mount(CommentSection, { props: makeProps() })
    await wrapper.findAll('button').find(b => b.text().includes('Denunciar'))!.trigger('click')
    await flushPromises()

    expect(document.body.textContent).toContain('Denunciar?')
    findPortalButton('Flag Comment').click()
    await flushPromises()

    expect(fetchMock.mock.calls[0][0]).toBe('/action/flag')
    const body = fetchMock.mock.calls[0][1].body as FormData
    expect(body.get('cid')).toBe('1')
    expect(alertSpy).toHaveBeenCalledWith('Denunciado.')
    wrapper.unmount()
  })

  it('surfaces submit errors inline', async () => {
    vi.stubGlobal('fetch', vi.fn().mockResolvedValue(jsonResponse({ success: false, error: 'Empty content', csrfHash: 'h' })))

    const wrapper = mount(CommentSection, { 
      props: makeProps(),
      global: { stubs: { WysiwygEditorIsland: true } }
    })
    const editor = wrapper.findComponent({ name: 'WysiwygEditorIsland' })
    await editor.vm.$emit('update:modelValue', 'x')
    await wrapper.find('form').trigger('submit')
    await flushPromises()

    expect(wrapper.text()).toContain('Empty content')
  })
})
