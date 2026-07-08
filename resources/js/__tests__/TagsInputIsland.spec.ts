import { describe, expect, it } from 'vitest'
import { mount } from '@vue/test-utils'
import TagsInputIsland from '@/components/TagsInputIsland.vue'

function hiddenInput(wrapper: ReturnType<typeof mount>) {
  return wrapper.find('input[type="hidden"]').element as HTMLInputElement
}

describe('TagsInputIsland', () => {
  it('parses initialTags into chips and the hidden input', () => {
    const wrapper = mount(TagsInputIsland, {
      props: { initialTags: ' ui, performance , ', inputName: 'tags' },
    })

    expect(wrapper.text()).toContain('ui')
    expect(wrapper.text()).toContain('performance')
    expect(hiddenInput(wrapper).name).toBe('tags')
    expect(hiddenInput(wrapper).value).toBe('ui,performance')
  })

  it('adds a tag on Enter and syncs the hidden input', async () => {
    const wrapper = mount(TagsInputIsland, { props: { inputName: 'tags' } })

    const input = wrapper.find('input[type="text"], input:not([type="hidden"])')
    await input.setValue('api')
    await input.trigger('keydown', { key: 'Enter' })

    expect(hiddenInput(wrapper).value).toBe('api')
  })

  it('removes a tag via the delete button (pointer cursor included)', async () => {
    const wrapper = mount(TagsInputIsland, {
      props: { initialTags: 'ui,api', inputName: 'tags' },
      attachTo: document.body,
      // reka-ui's removeTag emit crashes in jsdom (collection lookup);
      // the removal itself happens before the emit, so swallow that error.
      global: { config: { errorHandler: () => {} } },
    })

    const deleteButtons = wrapper.findAll('button')
    expect(deleteButtons.length).toBeGreaterThanOrEqual(2)
    expect(deleteButtons[0].classes()).toContain('cursor-pointer')

    await deleteButtons[0].trigger('click')
    expect(hiddenInput(wrapper).value).toBe('api')
  })

  it('uses the translated placeholder from props', () => {
    const wrapper = mount(TagsInputIsland, {
      props: { placeholder: 'Adicionar uma etiqueta...' },
    })
    expect(wrapper.find('input:not([type="hidden"])').attributes('placeholder')).toBe('Adicionar uma etiqueta...')
  })
})
