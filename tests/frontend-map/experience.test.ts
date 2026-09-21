import { describe, it, expect, vi, beforeEach, afterEach } from 'vitest'
import { mount, flushPromises } from '@vue/test-utils'
import data from './Map/demo-hotel-map.json'
import MapExperience from './Map/MapExperience.vue'

beforeEach(() => {
  vi.useFakeTimers({ toFake: ['setTimeout', 'setInterval', 'clearInterval', 'clearTimeout'] })
  vi.stubGlobal('ResizeObserver', class { cb: any; constructor(cb: any) { this.cb = cb } observe() { this.cb([{ contentRect: { width: 1400, height: 800 } }]) } disconnect() {} })
  vi.stubGlobal('requestAnimationFrame', (f: any) => setTimeout(() => f(performance.now()), 16))
  vi.stubGlobal('cancelAnimationFrame', (id: any) => clearTimeout(id))
  vi.stubGlobal('IntersectionObserver', class { observe() {} disconnect() {} })
})
afterEach(() => { vi.useRealTimers(); vi.unstubAllGlobals() })

const mk = () => mount(MapExperience, { props: { data: data as any }, attachTo: document.body })
const t = (w: any, id: string) => w.find(`[data-testid=${id}]`)
const marker = (w: any, id: string) => w.find(`[data-loc="${id}"]`)
const label = (w: any) => w.find('svg').attributes('aria-label')

describe('MapExperience (full guest flow)', () => {
  it('starts in explore: ground floor, "you are here" visible, no panel', async () => {
    const w = mk(); await flushPromises()
    expect(label(w)).toBe('Map of Ground Floor'); expect(t(w, 'you-are-here').exists()).toBe(true)
    expect(t(w, 'location-sheet').exists()).toBe(false); expect(marker(w, 'azure').exists()).toBe(true); w.unmount()
  })

  it('tap a marker -> card with name/floor/buttons; view details expands; close returns to explore', async () => {
    const w = mk(); await flushPromises()
    await marker(w, 'azure').trigger('click'); await flushPromises()
    const sheet = t(w, 'location-sheet'); expect(sheet.text()).toContain('Azure Restaurant'); expect(sheet.text()).toContain('Ground Floor')
    expect(t(w, 'get-directions').exists()).toBe(true); expect(sheet.find('.expander--open').exists()).toBe(false)
    await t(w, 'view-details').trigger('click'); expect(sheet.find('.expander--open').exists()).toBe(true); expect(sheet.text()).toContain('Show Less')
    await t(w, 'sheet-close').trigger('click'); await flushPromises(); vi.advanceTimersByTime(600); await flushPromises()
    expect(t(w, 'location-sheet').exists()).toBe(false); w.unmount()
  })

  it('directions -> preview -> start -> steps -> arrived -> done (spec example, across floors)', async () => {
    const w = mk(); await flushPromises()
    // pick the Garden Restaurant via search (it is on the 2nd floor)
    await w.find('input[type=search]').setValue('garden'); await flushPromises()
    await w.find('[role=option] button').trigger('click'); await flushPromises()
    expect(label(w)).toBe('Map of 2nd Floor'); expect(t(w, 'location-sheet').text()).toContain('The Garden Restaurant')
    await t(w, 'get-directions').trigger('click'); await flushPromises(); vi.advanceTimersByTime(600); await flushPromises()

    const panel = t(w, 'directions-panel'); expect(panel.attributes('data-mode')).toBe('preview')
    expect(t(w, 'summary').text()).toMatch(/55 m/); expect(t(w, 'summary').text()).toContain('2 min walk')
    expect(panel.text()).toContain('Reception'); expect(panel.text()).toContain('The Garden Restaurant'); expect(panel.text()).toContain('Elevator up to the 2nd Floor')
    expect(label(w)).toBe('Map of Ground Floor')                 // preview starts where the route starts
    expect(t(w, 'route-line').exists()).toBe(true)

    await t(w, 'start').trigger('click'); await flushPromises(); vi.advanceTimersByTime(600); await flushPromises()
    expect(t(w, 'directions-panel').attributes('data-mode')).toBe('navigating'); expect(t(w, 'step-text').text()).toContain('Turn left')
    expect(t(w, 'next-preview').text()).toContain('Take the elevator'); expect(t(w, 'route-leg').exists()).toBe(true)

    await t(w, 'next').trigger('click'); await flushPromises(); vi.advanceTimersByTime(600); await flushPromises()
    expect(t(w, 'step-text').text()).toContain('Take the elevator')
    expect(t(w, 'transition').exists()).toBe(true)                // "Lift ↑ 2" chip on this floor
    await t(w, 'next').trigger('click'); await flushPromises(); vi.advanceTimersByTime(600); await flushPromises()
    expect(label(w)).toBe('Map of 2nd Floor'); expect(t(w, 'step-text').text()).toContain('Turn right')
    await t(w, 'next').trigger('click'); await flushPromises(); vi.advanceTimersByTime(600); await flushPromises()
    expect(t(w, 'arrived').text()).toContain("You've arrived"); expect(t(w, 'arrived').text()).toContain('on your right')

    await t(w, 'finish').trigger('click'); await flushPromises(); vi.advanceTimersByTime(600); await flushPromises()
    expect(t(w, 'location-sheet').text()).toContain('The Garden Restaurant'); expect(t(w, 'location-sheet').text()).toContain('You are here')
    w.unmount()
  })

  it('simulate walk auto-advances to arrival, then stops', async () => {
    const w = mk(); await flushPromises()
    await marker(w, 'azure').trigger('click'); await t(w, 'get-directions').trigger('click'); await flushPromises()
    await t(w, 'start').trigger('click'); await flushPromises()
    await t(w, 'simulate').trigger('click'); expect(t(w, 'simulate').text()).toContain('Pause')
    for (let i = 0; i < 6; i++) { vi.advanceTimersByTime(3900); await flushPromises() }
    expect(t(w, 'arrived').exists()).toBe(true); w.unmount()
  })

  it('floor selector switches floors; the chosen floor is highlighted; markers belong to that floor', async () => {
    const w = mk(); await flushPromises()
    await w.find('[aria-label="3rd Floor"]').trigger('click'); await flushPromises()
    expect(label(w)).toBe('Map of 3rd Floor'); expect(marker(w, 'presidential').exists()).toBe(true)
    expect(w.find('[aria-label="3rd Floor"]').attributes('aria-pressed')).toBe('true'); w.unmount()
  })

  it('category chip highlights matching places and jumps to the floor that has them', async () => {
    const w = mk(); await flushPromises()
    const chip = w.findAll('[role=tab]').find(c => c.text() === 'Meeting Rooms')!; await chip.trigger('click'); await flushPromises()
    expect(label(w)).toBe('Map of 1st Floor'); expect(chip.attributes('aria-selected')).toBe('true')
    expect(marker(w, 'boardroom').classes()).not.toContain('map-marker--dim'); expect(marker(w, 'room-101').classes()).toContain('map-marker--dim'); w.unmount()
  })

  it('search: no-match message; selecting a result jumps floor and opens its card', async () => {
    const w = mk(); await flushPromises()
    const input = w.find('input[type=search]'); await input.setValue('zzzz'); await flushPromises(); expect(w.text()).toContain('No places match')
    await input.setValue('spa'); await flushPromises(); await w.find('[role=option] button').trigger('click'); await flushPromises()
    expect(label(w)).toBe('Map of Basement'); expect(t(w, 'location-sheet').text()).toContain('Serenity Spa'); w.unmount()
  })

  it('"I\'m here" moves the You-Are-Here marker; markers are keyboard-operable; Escape steps back', async () => {
    const w = mk(); await flushPromises()
    await marker(w, 'cafe-aroma').trigger('keydown', { key: 'Enter' }); await flushPromises()
    expect(t(w, 'location-sheet').text()).toContain('Cafe Aroma'); await t(w, 'set-here').trigger('click'); await flushPromises()
    expect(t(w, 'location-sheet').text()).toContain('You are here')
    window.dispatchEvent(new KeyboardEvent('keydown', { key: 'Escape' })); await flushPromises(); vi.advanceTimersByTime(600); await flushPromises()
    expect(t(w, 'location-sheet').exists()).toBe(false); w.unmount()
  })

  it('set starting point: banner shows, tapping the map sets it; markers are inert-free', async () => {
    const w = mk(); await flushPromises(); await t(w, 'set-start').trigger('click'); expect(t(w, 'pick-hint').exists()).toBe(true)
    await t(w, 'set-start').trigger('click'); await flushPromises(); vi.advanceTimersByTime(600); expect(t(w, 'pick-hint').exists()).toBe(false); w.unmount()
  })

  it('markers are labelled for assistive tech and focusable', async () => {
    const w = mk(); await flushPromises(); const m = marker(w, 'reception')
    expect(m.attributes('role')).toBe('button'); expect(m.attributes('tabindex')).toBe('0'); expect(m.attributes('aria-label')).toContain('Reception'); w.unmount()
  })
})
