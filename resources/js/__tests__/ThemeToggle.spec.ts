import { beforeEach, describe, expect, it } from 'vitest'
import { mount } from '@vue/test-utils'
import { nextTick } from 'vue'
import ThemeToggle from '@/components/ThemeToggle.vue'

async function openMenu(wrapper: ReturnType<typeof mount>) {
  // Defaults already carry button=0 / ctrlKey=false; jsdom events reject
  // direct property assignment, so pass no overrides.
  const trigger = wrapper.find('button')
  await trigger.trigger('pointerdown')
  await trigger.trigger('click')
  await nextTick()
}

function menuItems(): HTMLElement[] {
  return Array.from(document.body.querySelectorAll('[data-slot="dropdown-menu-item"]'))
}

beforeEach(() => {
  document.body.innerHTML = ''
  document.documentElement.classList.remove('dark')
  localStorage.clear()
})

describe('ThemeToggle', () => {
  it('renders the trigger with pointer cursor and custom button class', () => {
    const wrapper = mount(ThemeToggle, {
      props: { buttonClass: 'text-slate-300' },
      attachTo: document.body,
    })

    const trigger = wrapper.find('button')
    expect(trigger.classes()).toContain('cursor-pointer')
    expect(trigger.classes()).toContain('text-slate-300')
  })

  it('shows translated menu entries', async () => {
    const wrapper = mount(ThemeToggle, {
      props: { labels: { light: 'Claro', dark: 'Escuro', system: 'Sistema', toggle: 'Alternar tema' } },
      attachTo: document.body,
    })

    expect(wrapper.text()).toContain('Alternar tema')
    await openMenu(wrapper)

    const labels = menuItems().map(item => item.textContent?.trim())
    expect(labels).toEqual(['Claro', 'Escuro', 'Sistema'])
  })

  it('applies the dark theme and persists it when Dark is selected', async () => {
    const wrapper = mount(ThemeToggle, { attachTo: document.body })
    await openMenu(wrapper)

    const dark = menuItems().find(item => item.textContent?.includes('Dark'))!
    dark.dispatchEvent(new Event('click', { bubbles: true }))
    await nextTick()

    expect(document.documentElement.classList.contains('dark')).toBe(true)
    expect(localStorage.getItem('theme')).toBe('dark')
  })

  it('menu items use the pointer cursor', async () => {
    const wrapper = mount(ThemeToggle, { attachTo: document.body })
    await openMenu(wrapper)

    for (const item of menuItems()) {
      expect(item.className).toContain('cursor-pointer')
    }
  })
})
