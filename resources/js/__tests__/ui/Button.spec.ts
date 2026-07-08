import { describe, expect, it } from 'vitest'
import { mount } from '@vue/test-utils'
import { Button, buttonVariants } from '@/components/ui/button'

describe('ui/Button', () => {
  it('always includes the pointer cursor in its base classes', () => {
    expect(buttonVariants()).toContain('cursor-pointer')
    expect(buttonVariants({ variant: 'ghost', size: 'icon' })).toContain('cursor-pointer')
  })

  it('renders slot content as a button element by default', () => {
    const wrapper = mount(Button, { slots: { default: 'Save' } })
    expect(wrapper.element.tagName).toBe('BUTTON')
    expect(wrapper.text()).toBe('Save')
    expect(wrapper.classes()).toContain('cursor-pointer')
  })

  // tailwind-merge drops conflicting base classes, so assert one stable
  // signature class per variant instead of the full list.
  it.each([
    ['default', 'bg-primary'],
    ['destructive', 'bg-destructive'],
    ['outline', 'bg-background'],
    ['secondary', 'bg-secondary'],
    ['ghost', 'hover:bg-accent'],
    ['link', 'text-primary'],
  ] as const)('applies the %s variant classes', (variant, signatureClass) => {
    const wrapper = mount(Button, { props: { variant }, slots: { default: 'x' } })
    expect(wrapper.classes()).toContain(signatureClass)
    expect(wrapper.classes()).toContain('cursor-pointer')
  })
})
