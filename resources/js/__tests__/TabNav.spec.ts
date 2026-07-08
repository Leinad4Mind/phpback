import { beforeEach, describe, expect, it } from 'vitest'
import { mount } from '@vue/test-utils'
import TabNav from '@/components/TabNav.vue'

const tabs = [
  { id: 'one', label: 'One' },
  { id: 'two', label: 'Two', count: 4, countClass: 'bg-primary/20 text-primary' },
]

function setupPanels() {
  document.body.innerHTML = `
    <div id="one-panel" class="block">first</div>
    <div id="two-panel" class="hidden">second</div>
  `
}

beforeEach(setupPanels)

describe('TabNav', () => {
  it('renders tabs with counts and pointer cursors, first tab active', () => {
    const wrapper = mount(TabNav, { props: { tabs } })

    const buttons = wrapper.findAll('[role="tab"]')
    expect(buttons).toHaveLength(2)
    expect(buttons[0].attributes('aria-selected')).toBe('true')
    expect(buttons[0].classes()).toContain('cursor-pointer')
    expect(buttons[1].text()).toContain('4')
  })

  it('switches the visible server-rendered panel on click', async () => {
    const wrapper = mount(TabNav, { props: { tabs }, attachTo: document.body })

    await wrapper.findAll('[role="tab"]')[1].trigger('click')

    expect(document.getElementById('one-panel')!.classList.contains('hidden')).toBe(true)
    expect(document.getElementById('two-panel')!.classList.contains('block')).toBe(true)
    expect(wrapper.findAll('[role="tab"]')[1].attributes('aria-selected')).toBe('true')
  })

  it('honours initialTab on mount', () => {
    mount(TabNav, { props: { tabs, initialTab: 'two' }, attachTo: document.body })

    expect(document.getElementById('one-panel')!.classList.contains('hidden')).toBe(true)
    expect(document.getElementById('two-panel')!.classList.contains('hidden')).toBe(false)
  })

  it('renders extra links (e.g. admin panel) as anchors', () => {
    const wrapper = mount(TabNav, {
      props: {
        tabs,
        links: [{ label: 'ADMIN', href: '/admin', external: true }],
      },
    })

    const link = wrapper.find('a')
    expect(link.text()).toBe('ADMIN')
    expect(link.attributes('target')).toBe('_blank')
    expect(link.attributes('rel')).toBe('noopener')
  })
})
