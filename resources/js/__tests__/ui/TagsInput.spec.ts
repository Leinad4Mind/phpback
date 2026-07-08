import { describe, expect, it } from 'vitest'
import { defineComponent, ref } from 'vue'
import { mount } from '@vue/test-utils'
import {
  TagsInput,
  TagsInputInput,
  TagsInputItem,
  TagsInputItemDelete,
  TagsInputItemText,
} from '@/components/ui/tags-input'

const Harness = defineComponent({
  components: { TagsInput, TagsInputInput, TagsInputItem, TagsInputItemDelete, TagsInputItemText },
  setup() {
    const tags = ref(['ui', 'api'])
    return { tags }
  },
  template: `
    <TagsInput v-model="tags">
      <TagsInputItem v-for="tag in tags" :key="tag" :value="tag">
        <TagsInputItemText />
        <TagsInputItemDelete />
      </TagsInputItem>
      <TagsInputInput placeholder="Add a tag..." />
    </TagsInput>
  `,
})

describe('ui/TagsInput primitives', () => {
  it('renders one chip per tag with its text', () => {
    const wrapper = mount(Harness)
    expect(wrapper.text()).toContain('ui')
    expect(wrapper.text()).toContain('api')
    expect(wrapper.findAll('button')).toHaveLength(2)
  })

  it('delete buttons carry the pointer cursor and remove their tag', async () => {
    const wrapper = mount(Harness, {
      attachTo: document.body,
      // reka-ui's removeTag emit crashes in jsdom (collection lookup);
      // the removal itself happens before the emit, so swallow that error.
      global: { config: { errorHandler: () => {} } },
    })
    const first = wrapper.findAll('button')[0]
    expect(first.classes()).toContain('cursor-pointer')

    await first.trigger('click')
    expect(wrapper.text()).not.toContain('ui')
    expect(wrapper.text()).toContain('api')
  })

  it('adds a tag when Enter is pressed in the input', async () => {
    const wrapper = mount(Harness)
    const input = wrapper.find('input')
    await input.setValue('performance')
    await input.trigger('keydown', { key: 'Enter' })

    expect(wrapper.text()).toContain('performance')
  })
})
