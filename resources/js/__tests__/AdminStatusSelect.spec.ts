import { beforeEach, describe, expect, it, vi } from 'vitest'
import { flushPromises, mount } from '@vue/test-utils'
import AdminStatusSelect from '@/components/AdminStatusSelect.vue'
import { resetCsrf } from '@/lib/csrf'

function jsonResponse(body: unknown, ok = true, status = 200) {
  return { ok, status, json: () => Promise.resolve(body) } as Response
}

const baseProps = {
  ideaId: 7,
  initialStatus: 'considered',
  csrfTokenName: 'csrf_token',
  initialCsrfHash: 'hash-1',
  updateUrl: '/adminaction/ideastatus',
  statuses: {
    declined: 'Declined',
    considered: 'Considered',
    planned: 'Planned',
    started: 'Started',
    completed: 'Completed',
  },
  errorLabel: 'Algo correu mal.',
}

beforeEach(() => {
  resetCsrf()
  vi.restoreAllMocks()
})

describe('AdminStatusSelect', () => {
  it('renders every status option and a pointer cursor', () => {
    const wrapper = mount(AdminStatusSelect, { props: baseProps })
    expect(wrapper.findAll('option')).toHaveLength(5)
    expect(wrapper.find('select').element.value).toBe('considered')
    expect(wrapper.find('select').classes()).toContain('cursor-pointer')
  })

  it('posts the new status and keeps it on success', async () => {
    const fetchMock = vi.fn().mockResolvedValue(jsonResponse({
      success: true,
      newStatus: 'planned',
      csrfHash: 'hash-2',
    }))
    vi.stubGlobal('fetch', fetchMock)

    const wrapper = mount(AdminStatusSelect, { props: baseProps })
    await wrapper.find('select').setValue('planned')
    await flushPromises()

    const body = fetchMock.mock.calls[0][1].body as FormData
    expect(body.get('id')).toBe('7')
    expect(body.get('status')).toBe('planned')
    expect(wrapper.find('select').element.value).toBe('planned')
    expect(wrapper.find('[role="alert"]').exists()).toBe(false)
  })

  it('reverts the selection and shows the error on failure', async () => {
    vi.stubGlobal('fetch', vi.fn().mockResolvedValue(
      jsonResponse({ success: false, error: 'Invalid status', csrfHash: 'hash-2' }),
    ))

    const wrapper = mount(AdminStatusSelect, { props: baseProps })
    await wrapper.find('select').setValue('declined')
    await flushPromises()

    expect(wrapper.find('select').element.value).toBe('considered')
    expect(wrapper.find('[role="alert"]').text()).toBe('Invalid status')
  })

  it('reverts with the generic label when the request blows up', async () => {
    vi.stubGlobal('fetch', vi.fn().mockRejectedValue(new Error('down')))

    const wrapper = mount(AdminStatusSelect, { props: baseProps })
    await wrapper.find('select').setValue('completed')
    await flushPromises()

    expect(wrapper.find('select').element.value).toBe('considered')
    expect(wrapper.find('[role="alert"]').text()).toBe('Algo correu mal.')
  })
})
