import { describe, it, expect, vi, beforeEach, afterEach } from 'vitest'
import { mount, flushPromises } from '@vue/test-utils'
import demo from './Map/demo-hotel-map.json'
import Builder from './Pages/Admin/Map/Builder.vue'
import { router } from './inertia-stub'

const W = 1400, H = 800
beforeEach(() => {
  vi.useFakeTimers({ toFake: ['setTimeout', 'clearTimeout', 'setInterval', 'clearInterval', 'performance'] })
  vi.stubGlobal('ResizeObserver', class { cb: any; constructor(cb: any) { this.cb = cb } observe() { this.cb([{ contentRect: { width: W, height: H } }]) } disconnect() {} })
  vi.stubGlobal('requestAnimationFrame', (f: any) => setTimeout(() => f(performance.now()), 16))
  vi.stubGlobal('cancelAnimationFrame', (id: any) => clearTimeout(id))
  vi.stubGlobal('confirm', () => true)
  ;(router as any).calls.length = 0
})
afterEach(() => { vi.useRealTimers(); vi.unstubAllGlobals() })

const props = (over: any = {}) => ({ map: structuredClone(demo), content: [['facility','infinity-pool','Infinity Pool'],['facility','serenity-spa','Serenity Spa'],['facility','fitness-center','Fitness Center'],['facility','business-center','Business Center'],['restaurant','azure-restaurant','Azure Restaurant'],['restaurant','sky-lounge','Sky Lounge'],['page','spa-menu','Spa Menu'],['page','draft-page','Draft Page']].map(([type,slug,name]) => ({ type, slug, name, published: slug !== 'draft-page' })), errors_list: [], warnings: [], flash_ok: null, ...over })
const mk = (over?: any) => mount(Builder, { props: props(over), attachTo: document.body })
const t = (w: any, id: string) => w.find(`[data-testid=${id}]`)
const el = (w: any, kind: string, id: string) => w.find(`[data-el=${kind}][data-id="${id}"]`)

/** world -> screen using the live viewBox (the canvas is W x H in the tests). */
function toScreen(w: any, x: number, y: number) {
  const [vx, vy, vw, vh] = w.find('svg').attributes('viewBox').split(' ').map(Number)
  return { clientX: ((x - vx) / vw) * W, clientY: ((y - vy) / vh) * H }
}
function fire(target: any, type: string, pt: { clientX: number; clientY: number }, id = 1) {
  const e = new MouseEvent(type, { bubbles: true, cancelable: true, clientX: pt.clientX, clientY: pt.clientY, button: 0 })
  Object.defineProperty(e, 'pointerId', { value: id })
  ;(target.element ?? target).dispatchEvent(e)
}
async function tap(w: any, target: any, x: number, y: number) {
  const p = toScreen(w, x, y)
  fire(target, 'pointerdown', p); fire(target, 'pointerup', p); await flushPromises()
}
async function dragOn(w: any, target: any, from: [number, number], to: [number, number]) {
  const a = toScreen(w, ...from), b = toScreen(w, ...to), canvas = t(w, 'builder-canvas')
  fire(target, 'pointerdown', a)
  for (let i = 1; i <= 6; i++) fire(canvas, 'pointermove', { clientX: a.clientX + ((b.clientX - a.clientX) * i) / 6, clientY: a.clientY + ((b.clientY - a.clientY) * i) / 6 })
  fire(canvas, 'pointerup', b); await flushPromises()
}
/** Click Save and let the (stubbed) server finish, like Inertia does. */
async function save(w: any) { await t(w, 'save').trigger('click'); (router as any).calls.at(-1)?.opts.onFinish?.(); await flushPromises() }
const floorData = (w: any) => (router as any).calls.at(-1)?.data?.data

describe('map builder page', () => {
  it('opens on the ground floor with tools, floors, checks; nothing unsaved; save disabled', async () => {
    const w = mk(); await flushPromises()
    expect(w.text()).toContain('Ground Floor'); expect(t(w, 'floor-list').findAll('li')).toHaveLength(5); expect(t(w, 'all-good').exists()).toBe(true)
    expect(t(w, 'dirty').exists()).toBe(false); expect(t(w, 'save').attributes('disabled')).toBeDefined(); expect(el(w, 'location', 'reception').exists()).toBe(true); w.unmount()
  })

  it('tap a place -> inspector shows it; rename -> unsaved + save enabled -> Save sends the whole map to /admin/map/save', async () => {
    const w = mk(); await flushPromises()
    const rec = el(w, 'location', 'reception'); await tap(w, rec, 170, 155)
    const name = t(w, 'loc-name'); expect((name.element as HTMLInputElement).value).toBe('Reception')
    ;(name.element as HTMLInputElement).value = 'Front Desk'; await name.trigger('change'); await flushPromises()
    expect(t(w, 'dirty').exists()).toBe(true); expect(t(w, 'save').attributes('disabled')).toBeUndefined()
    await t(w, 'save').trigger('click')
    const call = (router as any).calls.at(-1); expect(call.url).toBe('/admin/map/save'); expect(call.data.data.locations.find((l: any) => l.id === 'reception').name).toBe('Front Desk')
    expect(call.data.data.floors).toHaveLength(5); expect(call.opts.preserveState).toBe(true)
    call.opts.onSuccess({ props: { flash_ok: 'Map saved.' } }); await flushPromises(); expect(t(w, 'dirty').exists()).toBe(false); w.unmount()
  })

  it('dragging a place moves it (one undo step); undo button puts it back', async () => {
    const w = mk(); await flushPromises()
    const before = structuredClone(demo.locations.find(l => l.id === 'concierge')!)
    await dragOn(w, el(w, 'location', 'concierge'), [before.x, before.y], [before.x, before.y + 100])
    await save(w); const moved = floorData(w).locations.find((l: any) => l.id === 'concierge'); expect(moved.y).toBeGreaterThan(before.y + 40)
    await t(w, 'undo').trigger('click'); await save(w); expect(floorData(w).locations.find((l: any) => l.id === 'concierge').y).toBe(before.y); w.unmount()
  })

  it('Area tool: drag on the plan draws a room, selects it, and the inspector offers its kind', async () => {
    const w = mk(); await flushPromises(); await t(w, 'tool-area').trigger('click')
    const n0 = demo.floors.find(f => f.id === 'fg')!.areas.length
    await dragOn(w, t(w, 'builder-canvas'), [100, 580], [300, 590])                                  // too small in height -> ignored
    await dragOn(w, t(w, 'builder-canvas'), [600, 100], [760, 220])
    await save(w); const areas = floorData(w).floors.find((f: any) => f.id === 'fg').areas
    expect(areas).toHaveLength(n0 + 1); const a = areas.at(-1); expect([a.x, a.y, a.w, a.h]).toEqual([600, 100, 160, 120]); expect(t(w, 'area-kind').exists()).toBe(true); w.unmount()
  })

  it('Walkway tool: clicks lay connected points; Esc ends the chain; a click on an existing point joins to it', async () => {
    const w = mk(); await flushPromises(); await t(w, 'tool-path').trigger('click'); const canvas = t(w, 'builder-canvas')
    const n0 = demo.nodes.filter(n => n.floor === 'fg').length
    await tap(w, canvas, 200, 500); await tap(w, canvas, 260, 505); await tap(w, canvas, 260, 560)     // ortho: 2nd point lines up with 1st, 3rd with 2nd
    expect(w.findAll('[data-el=node]')).toHaveLength(n0 + 3)
    await tap(w, el(w, 'node', 'fg-c1'), 225, 300)                                                       // join back to the corridor
    await save(w); const nodes = floorData(w).nodes; const created = nodes.filter((n: any) => n.floor === 'fg' && !demo.nodes.some(d => d.id === n.id))
    expect(created.map((n: any) => [n.x, n.y])).toEqual([[200, 500], [260, 500], [260, 560]])
    expect(nodes.find((n: any) => n.id === 'fg-c1').connections.length).toBe(3)                          // c0, c2 and the new branch
    window.dispatchEvent(new KeyboardEvent('keydown', { key: 'Escape' })); await flushPromises(); expect(t(w, 'path-done').exists()).toBe(false); w.unmount()
  })

  it('Place tool: click adds a new place at the nearest door, selected and ready to name; Delete removes it', async () => {
    const w = mk(); await flushPromises(); await t(w, 'tool-location').trigger('click')
    await tap(w, t(w, 'builder-canvas'), 500, 450)
    expect((t(w, 'loc-name').element as HTMLInputElement).value).toBe('New place')
    await save(w); const added = floorData(w).locations.filter((l: any) => l.name === 'New place'); expect(added).toHaveLength(1); expect(added[0].node).toBe('fg-c3')
    window.dispatchEvent(new KeyboardEvent('keydown', { key: 'Delete' })); await flushPromises()
    await save(w); expect(floorData(w).locations.some((l: any) => l.name === 'New place')).toBe(false); w.unmount()
  })

  it('Delete key removes a selected place; typing in a field does NOT delete (shortcuts ignore inputs)', async () => {
    const w = mk(); await flushPromises(); await tap(w, el(w, 'location', 'gift-shop'), 885, 155)
    const input = t(w, 'loc-name').element as HTMLInputElement; input.focus(); input.dispatchEvent(new KeyboardEvent('keydown', { key: 'Delete', bubbles: true })); await flushPromises()
    expect(el(w, 'location', 'gift-shop').exists()).toBe(true)
    window.dispatchEvent(new KeyboardEvent('keydown', { key: 'Delete' })); await flushPromises(); expect(el(w, 'location', 'gift-shop').exists()).toBe(false); w.unmount()
  })

  it('add a floor by copying: new floor is listed and selected, with the lift/stairs already joined to the floor it came from', async () => {
    const w = mk(); await flushPromises(); await t(w, 'add-floor').trigger('click')
    await t(w, 'nf-label').setValue('4'); await w.find('input[placeholder="Full name"]').setValue('Sky Floor'); await t(w, 'nf-copy').setValue('f3'); await t(w, 'nf-submit').trigger('click'); await flushPromises()
    expect(t(w, 'floor-list').findAll('li')).toHaveLength(6); expect(t(w, 'floor-list').text()).toContain('Sky Floor')
    await save(w); const d = floorData(w); const nf = d.floors.find((f: any) => f.label === '4'); expect(nf.level).toBe(4)
    expect(d.nodes.filter((n: any) => n.floor === nf.id)).toHaveLength(8); expect(d.locations.filter((l: any) => l.floor === nf.id)).toHaveLength(0); w.unmount()
  })

  it('a lift can be connected to all floors from the inspector (creates twins that are still unconnected to a walkway -> flagged)', async () => {
    const w = mk({ map: (() => { const m = structuredClone(demo); m.nodes.filter(n => n.floor === 'f3').forEach(n => (n.connections = n.connections.filter(c => c.startsWith('f3-')))); return m })() }); await flushPromises()
    expect(t(w, 'issues').text()).toContain("isn't connected to other floors")
    await w.find('[data-testid=floor-3]').trigger('click'); await tap(w, el(w, 'node', 'f3-c3'), 445, 300); await t(w, 'link-shaft').trigger('click'); await flushPromises()
    const left = t(w, 'issues').text(); expect(left).not.toContain('A lift'); expect(left).toContain('A staircase'); w.unmount()   // the lift is joined now; the staircase (also cut in this setup) still needs linking
  })

  it('Checks: deleting a corridor point lists the places nobody can reach; clicking an issue jumps to it', async () => {
    const w = mk(); await flushPromises(); await tap(w, el(w, 'node', 'fg-c3'), 445, 300); window.dispatchEvent(new KeyboardEvent('keydown', { key: 'Delete' })); await flushPromises()
    const list = t(w, 'issues'); expect(list.text()).toContain('No walking route to')
    await w.find('[data-testid=floor-B1]').trigger('click'); await list.find('button').trigger('click'); await flushPromises()
    expect(t(w, 'loc-name').exists()).toBe(true); w.unmount()
  })

  it('unfixable problems block saving: an unnamed place disables Save and is listed as an error', async () => {
    const w = mk(); await flushPromises(); await tap(w, el(w, 'location', 'reception'), 170, 155)
    const name = t(w, 'loc-name').element as HTMLInputElement; name.value = '  '; await t(w, 'loc-name').trigger('change'); await flushPromises()
    expect(t(w, 'save').attributes('disabled')).toBeDefined(); expect(t(w, 'issues').text()).toContain('has no name'); w.unmount()
  })

  it('Test a route: choose two places, see distance and the same wording guests get; dotted route drawn on the map', async () => {
    const w = mk(); await flushPromises(); await t(w, 'tab-route').trigger('click')
    await t(w, 'route-from').setValue('reception'); await t(w, 'route-to').setValue('garden'); await t(w, 'route-go').trigger('click'); await flushPromises()
    const r = t(w, 'route-result'); expect(r.text()).toContain('55 m'); expect(r.text()).toContain('After exiting the elevator'); expect(t(w, 'test-route').exists()).toBe(true)
    await t(w, 'route-to').setValue('reception'); expect(t(w, 'route-go').attributes('disabled')).toBeDefined(); w.unmount()
  })

  it('server errors from a refused save are shown; unsaved edits stay', async () => {
    const w = mk({ errors_list: ['Location "x" uses unknown node "y".'] }); await flushPromises(); expect(t(w, 'server-errors').text()).toContain('unknown node'); w.unmount()
  })

  it('keyboard: V/A/W/P switch tools; Ctrl+Z undoes; Esc returns to Select', async () => {
    const w = mk(); await flushPromises()
    window.dispatchEvent(new KeyboardEvent('keydown', { key: 'w' })); await flushPromises(); expect(t(w, 'tool-path').classes().join(' ')).toContain('bg-slate-800')
    window.dispatchEvent(new KeyboardEvent('keydown', { key: 'Escape' })); await flushPromises(); expect(t(w, 'tool-select').classes().join(' ')).toContain('bg-slate-800')
    await tap(w, el(w, 'location', 'concierge'), 335, 155); const n = t(w, 'loc-name'); (n.element as HTMLInputElement).value = 'X'; await n.trigger('change'); expect(t(w, 'dirty').exists()).toBe(true)
    window.dispatchEvent(new KeyboardEvent('keydown', { key: 'z', ctrlKey: true })); await flushPromises(); await t(w, 'undo'); expect(t(w, 'undo').attributes('disabled')).toBeDefined(); w.unmount()
  })

  it('links: the dropdown groups Facilities / Restaurants / Pages; picking a Page saves ref type "page"', async () => {
    const w = mk(); await flushPromises(); await tap(w, el(w, 'location', 'concierge'), 335, 155)
    const groups = t(w, 'loc-ref').findAll('optgroup').map((g: any) => g.attributes('label')); expect(groups).toEqual(['Facilities', 'Restaurants', 'Pages'])
    await t(w, 'loc-ref').setValue('page:spa-menu'); await save(w); expect(floorData(w).locations.find((l: any) => l.id === 'concierge').ref).toEqual({ type: 'page', slug: 'spa-menu' }); w.unmount()
  })

  it('links: a draft / deleted target warns in the inspector AND in Checks (guests would silently get no link)', async () => {
    const w = mk(); await flushPromises(); await tap(w, el(w, 'location', 'concierge'), 335, 155)
    await t(w, 'loc-ref').setValue('page:draft-page'); await flushPromises()
    expect(t(w, 'ref-unpublished').text()).toContain("isn't published"); expect(t(w, 'issues').text()).toContain('which isn\'t published')
    const m = structuredClone(demo); m.locations.find((l: any) => l.id === 'spa')!.ref = { type: 'facility', slug: 'deleted-thing' } as any
    const w2 = mk({ map: m }); await flushPromises(); expect(t(w2, 'issues').text()).toContain('no longer exists'); w.unmount(); w2.unmount()
  })

  it('links: a place named like an existing page gets a one-click suggestion', async () => {
    const w = mk(); await flushPromises(); await tap(w, el(w, 'location', 'concierge'), 335, 155)
    const name = t(w, 'loc-name'); (name.element as HTMLInputElement).value = 'spa menu'; await name.trigger('change'); await flushPromises()
    await t(w, 'ref-suggest').trigger('click'); await save(w); expect(floorData(w).locations.find((l: any) => l.id === 'concierge').ref).toEqual({ type: 'page', slug: 'spa-menu' })
    expect(t(w, 'ref-suggest').exists()).toBe(false); w.unmount()
  })

  it('links: a typed address is saved (and trimmed); one that does not start with / or http(s) blocks Save', async () => {
    const w = mk(); await flushPromises(); await tap(w, el(w, 'location', 'concierge'), 335, 155)
    const link = t(w, 'loc-link'); (link.element as HTMLInputElement).value = '  /pages/tours  '; await link.trigger('change'); await save(w)
    expect(floorData(w).locations.find((l: any) => l.id === 'concierge').link).toBe('/pages/tours')
    ;(link.element as HTMLInputElement).value = 'pages/tours'; await link.trigger('change'); await flushPromises()
    expect(t(w, 'save').attributes('disabled')).toBeDefined(); expect(t(w, 'issues').text()).toContain('must start with'); w.unmount()
  })
})
