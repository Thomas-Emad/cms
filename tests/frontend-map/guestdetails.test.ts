import { describe, it, expect, vi, beforeEach, afterEach } from 'vitest'
import { mount, flushPromises } from '@vue/test-utils'
import demo from './Map/demo-hotel-map.json'
import LocationSheet from './Map/LocationSheet.vue'
import MapExperience from './Map/MapExperience.vue'

beforeEach(() => {
  vi.useFakeTimers({ toFake: ['setTimeout', 'clearTimeout', 'setInterval', 'clearInterval', 'performance'] })
  vi.stubGlobal('ResizeObserver', class { cb: any; constructor(cb: any) { this.cb = cb } observe() { this.cb([{ contentRect: { width: 1400, height: 800 } }]) } disconnect() {} })
  vi.stubGlobal('requestAnimationFrame', (f: any) => setTimeout(() => f(performance.now()), 16))
  vi.stubGlobal('cancelAnimationFrame', (id: any) => clearTimeout(id))
})
afterEach(() => { vi.useRealTimers(); vi.unstubAllGlobals() })
const loc = (over: any = {}) => ({ ...(demo.locations.find(l => l.id === 'azure') as any), ...over })
const sheet = (l: any, expanded = false) => mount(LocationSheet, { props: { loc: l, floorName: 'Ground Floor', expanded, isHere: false } })

describe('guest card: View Details', () => {
  it('a place linked to a page on this site opens it inside the app (Inertia link)', () => {
    const b = sheet(loc({ details_url: '/restaurants/azure-restaurant' })).find('[data-testid=view-details]')
    expect(b.attributes('href')).toBe('/restaurants/azure-restaurant'); expect(b.attributes('data-kind')).toBe('page'); expect(b.text()).toBe('View Details')
  })
  it('a Page Builder page or facility works the same way', () => {
    expect(sheet(loc({ details_url: '/pages/spa-menu' })).find('[data-testid=view-details]').attributes('href')).toBe('/pages/spa-menu')
    expect(sheet(loc({ details_url: '/facilities/serenity-spa' })).find('[data-testid=view-details]').attributes('data-kind')).toBe('page')
  })
  it('a full web address opens in a new tab', () => {
    const b = sheet(loc({ details_url: 'https://example.com/menu' })).find('[data-testid=view-details]')
    expect(b.element.tagName).toBe('A'); expect(b.attributes('target')).toBe('_blank'); expect(b.attributes('rel')).toContain('noopener'); expect(b.attributes('data-kind')).toBe('external')
  })
  it('with NO page to open, the button is honest: "More info" expands the card in place (no fake "View Details")', async () => {
    const w = sheet(loc({ details_url: null })); const b = w.find('[data-testid=view-details]')
    expect(b.text()).toBe('More info'); expect(b.attributes('data-kind')).toBe('expand'); await b.trigger('click'); expect(w.emitted('toggle')).toHaveLength(1)
    expect(sheet(loc({ details_url: null }), true).find('[data-testid=view-details]').text()).toBe('Show Less')
  })
  it('the action buttons live OUTSIDE the scrolling body, so a short screen can never push them out of view', () => {
    const w = sheet(loc({ description: 'x '.repeat(500), details_url: '/pages/a' }), true); const body = w.find('[data-testid=sheet-body]')
    expect(body.classes()).toContain('overflow-y-auto'); expect(body.find('[data-testid=get-directions]').exists()).toBe(false); expect(w.find('[data-testid=get-directions]').exists()).toBe(true)
    expect(w.find('article').classes()).toEqual(expect.arrayContaining(['flex', 'flex-col', 'max-h-full']))
  })
})

describe('guest map: open at a place, responsive measurement', () => {
  const mk = (props: any = {}) => mount(MapExperience, { props: { data: demo as any, ...props }, attachTo: document.body })
  it('/map?place=spa opens with that place selected on its own floor', async () => {
    const w = mk({ place: 'spa' }); await flushPromises()
    expect(w.find('[data-testid=location-sheet]').text()).toContain('Serenity Spa'); expect(w.find('svg').attributes('aria-label')).toBe('Map of Basement'); w.unmount()
  })
  it('an unknown ?place= is ignored', async () => {
    const w = mk({ place: 'nope' }); await flushPromises(); expect(w.find('[data-testid=location-sheet]').exists()).toBe(false); w.unmount()
  })
  it('the camera keeps its focus clear of the REAL panel width (measured, not guessed) on wide screens', async () => {
    vi.stubGlobal('matchMedia', (q: string) => ({ matches: q.includes('1024'), media: q, addEventListener() {}, removeEventListener() {} }))
    // pretend the side panel is 352px wide at left 24px; everything else (floor selector...) is 60px
    Object.defineProperty(HTMLElement.prototype, 'offsetWidth', { configurable: true, get() { return String((this as HTMLElement).className).includes('panel-w') ? 352 : 60 } })
    Object.defineProperty(HTMLElement.prototype, 'offsetLeft', { configurable: true, get() { return 24 } })
    const w = mk(); await flushPromises(); for (let t = 0; t < 4000; t += 16) vi.advanceTimersByTime(16); await flushPromises()
    const vb = w.find('svg').attributes('viewBox').split(' ').map(Number); const centreX = vb[0] + vb[2] / 2
    const reception = (demo.locations as any[]).find(l => l.id === 'reception')!
    expect(centreX).toBeLessThan(reception.x - 20)      // "you are here" is drawn to the RIGHT of the panel, not hidden under it
    w.unmount(); delete (HTMLElement.prototype as any).offsetWidth; delete (HTMLElement.prototype as any).offsetLeft
  })
  it('below 1024px the floor selector and zoom buttons are still there (touch kiosks may not pinch)', async () => {
    vi.stubGlobal('matchMedia', () => ({ matches: false, addEventListener() {}, removeEventListener() {} }))
    const w = mk(); await flushPromises(); expect(w.find('[data-testid=zoom-in]').exists()).toBe(true); expect(w.find('[data-testid=floor-selector]').exists()).toBe(true)
    expect(w.find('[data-testid=zoom-in]').element.closest('div.absolute')!.className).not.toContain('max-md:hidden'); w.unmount()
  })
})
