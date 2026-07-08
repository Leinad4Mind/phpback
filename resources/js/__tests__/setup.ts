import { vi } from 'vitest'

// The test DOM lacks a few browser APIs that @vueuse/core and reka-ui
// (floating-ui) rely on.
window.confirm = window.confirm ?? (() => true)
window.alert = window.alert ?? (() => {})
if (!window.matchMedia) {
  Object.defineProperty(window, 'matchMedia', {
    writable: true,
    value: vi.fn().mockImplementation((query: string) => ({
      matches: false,
      media: query,
      onchange: null,
      addListener: vi.fn(),
      removeListener: vi.fn(),
      addEventListener: vi.fn(),
      removeEventListener: vi.fn(),
      dispatchEvent: vi.fn(),
    })),
  })
}

if (!('ResizeObserver' in window)) {
  class ResizeObserverStub {
    observe() {}
    unobserve() {}
    disconnect() {}
  }
  // @ts-expect-error assigning stub
  window.ResizeObserver = ResizeObserverStub
}

if (!Element.prototype.scrollIntoView) {
  Element.prototype.scrollIntoView = () => {}
}

if (!('PointerEvent' in window)) {
  class PointerEventPolyfill extends MouseEvent {
    pointerId: number
    pointerType: string
    constructor(type: string, params: MouseEventInit & { pointerId?: number; pointerType?: string } = {}) {
      super(type, params)
      this.pointerId = params.pointerId ?? 0
      this.pointerType = params.pointerType ?? 'mouse'
    }
  }
  // @ts-expect-error assigning polyfill
  window.PointerEvent = PointerEventPolyfill
}
