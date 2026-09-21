import { describe, it, expect, vi, beforeEach, afterEach } from 'vitest'
import { useCamera } from './Map/camera'

beforeEach(() => {
  vi.useFakeTimers({ toFake: ['setTimeout', 'clearTimeout', 'performance'] })
  vi.stubGlobal('requestAnimationFrame', (f: any) => setTimeout(() => f(performance.now()), 16))
  vi.stubGlobal('cancelAnimationFrame', (id: any) => clearTimeout(id))
})
afterEach(() => { vi.useRealTimers(); vi.unstubAllGlobals() })
const mk = () => { const c = useCamera(); c.width.value = 1000; c.height.value = 600; return c }
const run = (ms: number) => { for (let t = 0; t < ms; t += 16) vi.advanceTimersByTime(16) }

describe('camera', () => {
  it('fitHome fits the bounds, records fitZoom and derives zoom limits', () => {
    const c = mk(); c.fitHome({ x: 0, y: 0, w: 1000, h: 500 }, 0, true)
    expect(c.zoom.value).toBeCloseTo(1); expect(c.fitZoom.value).toBeCloseTo(1); expect(c.minZoom.value).toBeCloseTo(0.6); expect(c.maxZoom.value).toBeCloseTo(7)
    expect(c.cx.value).toBeCloseTo(500); expect(c.cy.value).toBeCloseTo(250)
  })
  it('zoomAt keeps the map point under the fingertip fixed', () => {
    const c = mk(); c.fitHome({ x: 0, y: 0, w: 1000, h: 600 }, 0, true)
    const before = c.toWorld(700, 200); c.zoomAt(2, 700, 200); const after = c.toWorld(700, 200)
    expect(after.x).toBeCloseTo(before.x, 5); expect(after.y).toBeCloseTo(before.y, 5); expect(c.zoom.value).toBeCloseTo(2)
  })
  it('zoom is clamped to min/max', () => {
    const c = mk(); c.fitHome({ x: 0, y: 0, w: 1000, h: 600 }, 0, true); c.zoomAt(100, 500, 300); expect(c.zoom.value).toBeCloseTo(c.maxZoom.value); c.zoomAt(0.0001, 500, 300); expect(c.zoom.value).toBeCloseTo(c.minZoom.value)
  })
  it('panByPx moves the map with the finger (1px drag = 1/zoom map units) and cancels animation', () => {
    const c = mk(); c.fitHome({ x: 0, y: 0, w: 1000, h: 600 }, 0, true); c.zoomAt(2, 500, 300)
    c.focus(800, 400, 2); c.panByPx(100, 0); const x = c.cx.value; run(500); expect(c.cx.value).toBeCloseTo(x, 5) // no leftover animation
  })
  it('focus glides (not jumps) to the target and settles exactly', () => {
    const c = mk(); c.fitHome({ x: 0, y: 0, w: 1000, h: 600 }, 0, true); c.focus(200, 100, 3)
    expect(c.cx.value).toBeCloseTo(500); run(64); const mid = c.cx.value; expect(mid).toBeLessThan(500); expect(mid).toBeGreaterThan(200)
    run(3000); expect(c.cx.value).toBeCloseTo(200, 1); expect(c.zoom.value).toBeCloseTo(3, 2)
  })
  it('padding: the focused point appears at the centre of the VISIBLE area, not the viewport', () => {
    const c = mk(); c.fitHome({ x: 0, y: 0, w: 1000, h: 600 }, 0, true); c.padding.value = { left: 400, right: 0, top: 0, bottom: 0 }
    c.focus(300, 300, 2, true); const p = { x: (300 - c.cx.value) * 2 + 500, y: (300 - c.cy.value) * 2 + 300 }  // screen position of the point
    expect(p.x).toBeCloseTo(400 + 300, 4); expect(p.y).toBeCloseTo(300, 4)                                  // 400 + (1000-400)/2
  })
  it('viewBox matches viewport aspect at 1:1 pixel scale', () => {
    const c = mk(); c.fitHome({ x: 0, y: 0, w: 1000, h: 600 }, 0, true); c.zoomAt(2, 500, 300)
    const [x, y, w, h] = c.viewBox.value.split(' ').map(Number); expect(w).toBeCloseTo(500); expect(h).toBeCloseTo(300); expect(x + w / 2).toBeCloseTo(c.cx.value)
  })
  it('reduced motion: focus jumps immediately', () => {
    vi.stubGlobal('matchMedia', () => ({ matches: true })); const c = mk(); c.fitHome({ x: 0, y: 0, w: 1000, h: 600 }, 0, true); c.focus(100, 100, 3); expect(c.cx.value).toBeCloseTo(100)
  })
})
