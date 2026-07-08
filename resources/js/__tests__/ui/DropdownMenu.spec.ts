import { beforeEach, describe, expect, it } from 'vitest'
import { defineComponent, nextTick } from 'vue'
import { mount } from '@vue/test-utils'
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuLabel,
  DropdownMenuSeparator,
  DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu'

const Harness = defineComponent({
  components: {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
  },
  template: `
    <DropdownMenu :default-open="true">
      <DropdownMenuTrigger as-child>
        <button type="button">Open</button>
      </DropdownMenuTrigger>
      <DropdownMenuContent>
        <DropdownMenuLabel>Theme</DropdownMenuLabel>
        <DropdownMenuSeparator />
        <DropdownMenuItem>Light</DropdownMenuItem>
        <DropdownMenuItem>Dark</DropdownMenuItem>
      </DropdownMenuContent>
    </DropdownMenu>
  `,
})

beforeEach(() => {
  document.body.innerHTML = ''
})

describe('ui/DropdownMenu', () => {
  it('renders the teleported content when open', async () => {
    mount(Harness, { attachTo: document.body })
    await nextTick()

    const items = document.body.querySelectorAll('[data-slot="dropdown-menu-item"]')
    expect(items).toHaveLength(2)
    expect(document.body.textContent).toContain('Theme')
  })

  it('gives every menu item a pointer cursor', async () => {
    mount(Harness, { attachTo: document.body })
    await nextTick()

    const items = Array.from(document.body.querySelectorAll('[data-slot="dropdown-menu-item"]'))
    expect(items.length).toBeGreaterThan(0)
    for (const item of items) {
      expect(item.className).toContain('cursor-pointer')
      expect(item.className).not.toContain('cursor-default')
    }
  })
})
