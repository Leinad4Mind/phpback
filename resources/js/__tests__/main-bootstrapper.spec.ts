import { beforeEach, describe, expect, it, vi } from 'vitest'
import { mountIslands } from '../main'

beforeEach(() => {
  document.body.innerHTML = ''
})

describe('island bootstrapper', () => {
  it('mounts a registered component with JSON props from data-props', () => {
    document.body.innerHTML = `
      <div data-vue-component="TabNav" data-props='${JSON.stringify({
        tabs: [
          { id: 'one', label: 'One' },
          { id: 'two', label: 'Two' },
        ],
      })}'></div>
      <div id="one-panel" class="block"></div>
      <div id="two-panel" class="hidden"></div>
    `

    mountIslands()

    expect(document.querySelectorAll('[role="tab"]')).toHaveLength(2)
    expect(document.body.textContent).toContain('One')
    expect(document.body.textContent).toContain('Two')
  })

  it('warns on unknown components instead of crashing', () => {
    const warn = vi.spyOn(console, 'warn').mockImplementation(() => {})
    document.body.innerHTML = '<div data-vue-component="Nope"></div>'

    mountIslands()

    expect(warn).toHaveBeenCalledWith(expect.stringContaining('Nope'))
    warn.mockRestore()
  })

  it('reports invalid data-props JSON without mounting', () => {
    const error = vi.spyOn(console, 'error').mockImplementation(() => {})
    document.body.innerHTML = '<div data-vue-component="TabNav" data-props="{not json"></div>'

    mountIslands()

    expect(error).toHaveBeenCalled()
    expect(document.querySelector('[role="tab"]')).toBeNull()
    error.mockRestore()
  })
})
