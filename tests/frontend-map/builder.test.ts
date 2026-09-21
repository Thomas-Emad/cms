import { describe, it, expect } from 'vitest'
import { writeFileSync } from 'node:fs'
import { execFileSync } from 'node:child_process'
import demo from './Map/demo-hotel-map.json'
import { useMapBuilder, blankMap } from './Map/builder/useMapBuilder'
import type { HotelMapData } from './Map/types'

const mk = (m: any = demo) => useMapBuilder(m as HotelMapData)
const phpValidate = (data: any) => {
  writeFileSync('/tmp/b.json', JSON.stringify(data))
  const out = execFileSync('php', ['-r', `require '/home/claude/proj/app/Services/Map/MapDataValidator.php'; echo json_encode((new App\\Services\\Map\\MapDataValidator())->validate(json_decode(file_get_contents('/tmp/b.json'), true)));`]).toString()
  return JSON.parse(out) as { errors: string[]; warnings: string[] }
}

describe('map builder', () => {
  it('starts from a blank one-floor map when there is no map yet, and that saves as a valid (empty) map', () => {
    const b = mk(null); expect(b.data.value.floors).toHaveLength(1); expect(b.floorId.value).toBe('fg')
    expect(phpValidate(b.toJSON()).errors).toEqual([])
  })
  it('loading the demo map: no issues except none; untouched map is not dirty', () => {
    const b = mk(); expect(b.dirty.value).toBe(false); expect(b.issues.value).toEqual([]); expect(b.floors.value.map(f => f.label)).toEqual(['B1', 'G', '1', '2', '3'])
  })
  it('draw an area: too small is ignored, valid one is added, clamped to the floor, selected, undoable', () => {
    const b = mk(); b.floorId.value = 'f1'
    expect(b.addArea(10, 10, 5, 5)).toBeNull()
    const id = b.addArea(980, 20, 100, 50, 'public')!; const a = b.area(id)!
    expect(a.x).toBe(980); expect(b.selection.value).toEqual({ kind: 'area', id }); expect(b.dirty.value).toBe(true)
    b.resizeArea(id, 980, 20, 500, 50); expect(a.w).toBe(20)                                   // cannot grow past the floor edge
    b.undo(); expect(b.area(id)).toBeUndefined(); expect(b.selection.value).toBeNull(); b.redo(); expect(b.area(id)).toBeDefined()
  })
  it('a whole drag is ONE undo step', () => {
    const b = mk(); const l = b.location('reception')!; const x0 = l.x
    b.beginGesture(); for (let i = 1; i <= 20; i++) b.moveLocation('reception', x0 + i * 5, l.y); b.endGesture()
    expect(l.x).toBe(x0 + 100); b.undo(); expect(b.location('reception')!.x).toBe(x0)
  })
  it('placing a place picks the nearest walkway as its door; moving it re-picks; a pinned door stays', () => {
    const b = mk(); b.floorId.value = 'fg'
    const id = b.addLocation(660, 450, 'dining'); expect(b.location(id)!.node).toBe('fg-c5')
    b.moveLocation(id, 890, 450); expect(b.location(id)!.node).toBe('fg-c7')
    b.updateLocation(id, { node: 'fg-c1' }); b.moveLocation(id, 500, 450); expect(b.location(id)!.node).toBe('fg-c1')
  })
  it('walkway drawing: nodes chain-connect, connect() is idempotent and same-floor only', () => {
    const b = mk(null); const a = b.addNode(100, 300); const c = b.addNode(400, 300, 'walk', a)
    expect(b.node(a)!.connections).toEqual([c]); expect(b.node(c)!.connections).toEqual([a])
    expect(b.connect(a, c)).toBe(false); b.addFloor({ label: '1', name: '1st Floor', level: 1 }); const d = b.addNode(100, 100)
    expect(b.connect(a, d)).toBe(false)
  })
  it('ortho + snap helpers', () => {
    const b = mk(null); const a = b.addNode(100, 100)
    expect(b.orthoFrom(a, { x: 300, y: 130 })).toEqual({ x: 300, y: 100 }); expect(b.orthoFrom(a, { x: 120, y: 400 })).toEqual({ x: 100, y: 400 })
    b.ortho.value = false; expect(b.orthoFrom(a, { x: 300, y: 130 })).toEqual({ x: 300, y: 130 })
    expect(b.snapPoint({ x: 104, y: 96 })).toEqual({ x: 100, y: 100 }); b.snapOn.value = false; expect(b.snapPoint({ x: 104, y: 96 })).toEqual({ x: 104, y: 96 })
    expect(b.snapPoint({ x: 108, y: 104 }, { radius: 20, nodes: true })).toEqual({ x: 100, y: 100 })   // magnet to existing point
  })
  it('deleting a walkway point cleans connections and re-doors places that used it', () => {
    const b = mk(); b.floorId.value = 'fg'; b.remove({ kind: 'node', id: 'fg-c2' })
    expect(b.data.value.nodes.some(n => n.connections.includes('fg-c2'))).toBe(false)
    expect(b.location('concierge')!.node).not.toBe('fg-c2'); expect(b.location('concierge')!.node).toBeTruthy()
    expect(b.issues.value.some(i => /No walking route/.test(i.text))).toBe(true)            // c1 and c3 no longer joined -> flagged
  })
  it('deleting a place clears the kiosk if it was the kiosk', () => {
    const b = mk(); b.remove({ kind: 'location', id: 'reception' }); expect(b.data.value.default_start).toBeNull(); expect(b.issues.value.some(i => /kiosk/.test(i.text))).toBe(true)
  })
  it('new floor copied from another gets its own ids, walkways, and lifts/stairs joined to the floor it was copied from', () => {
    const b = mk(); const id = b.addFloor({ label: '4', name: '4th Floor', level: 4, copyFrom: 'f3' })
    const mine = b.data.value.nodes.filter(n => n.floor === id); expect(mine).toHaveLength(8)
    expect(new Set(b.data.value.nodes.map(n => n.id)).size).toBe(b.data.value.nodes.length)
    const lift = mine.find(n => n.type === 'elevator')!; expect(b.node(lift.connections.find(c => b.node(c)!.floor === 'f3')!)!.type).toBe('elevator')
    expect(b.data.value.locations.filter(l => l.floor === id)).toHaveLength(0)              // places are not copied
    expect(phpValidate(b.toJSON()).errors).toEqual([]); expect(b.issues.value.filter(i => i.level === 'error')).toEqual([])
  })
  it('linkShaft creates/joins twins on every floor; unlinkShaft cuts them; routing then works across floors', () => {
    const b = mk(null); const g = b.floorId.value
    const c0 = b.addNode(100, 300); const lift = b.addNode(400, 300, 'walk', c0); b.setNodeType(lift, 'elevator'); b.setLiftSide(lift, 's')
    b.addFloor({ label: '1', name: '1st Floor', level: 1 }); const f1 = b.floorId.value
    const room = (fl: string, x: number) => { b.floorId.value = fl; return b.addLocation(x, 200, 'room') }
    expect(b.linkShaft(lift)).toBe(1); const shaft = b.shaftOf(lift); expect(shaft).toHaveLength(2); expect(shaft.map(s => s.floor).sort()).toEqual([f1, g].sort())
    const a = room(g, 100); b.floorId.value = f1; const n1 = b.addNode(700, 300, 'walk', shaft.find(s => s.floor === f1)!.id); const d = room(f1, 700)
    b.updateLocation(a, { name: 'A' }); b.updateLocation(d, { name: 'D' })
    const r = b.testRoute(a, d)!; expect(r).not.toBeNull(); expect(r.transitions).toHaveLength(1); expect(r.transitions[0].kind).toBe('elevator')
    b.unlinkShaft(lift); expect(b.shaftOf(lift)).toHaveLength(1); expect(b.testRoute(a, d)).toBeNull()
    expect(b.issues.value.some(i => /isn't connected to other floors/.test(i.text))).toBe(true)
  })
  it('changing a lift to a plain walkway point cuts its cross-floor links (server rule)', () => {
    const b = mk(); b.setNodeType('f1-c3', 'walk'); expect(b.node('f1-c3')!.connections.every(c => b.node(c)!.floor === 'f1')).toBe(true)
    expect(phpValidate(b.toJSON()).errors).toEqual([])
  })
  it('lift side sets the cab position, which drives "turn left/right after exiting"', () => {
    const b = mk(); expect(b.liftSide(b.node('f2-c3')!)).toBe('n'); b.setLiftSide('f2-c3', 's'); expect(b.liftSide(b.node('f2-c3')!)).toBe('s')
    const r = b.testRoute('reception', 'garden')!; const exit = r.steps[r.steps.findIndex(s => s.kind === 'elevator') + 1]
    expect(exit.kind).toBe('left')                                                             // flipped from "right" because the cab is now south
  })
  it('deleting a floor removes its places/walkways/links; the last floor cannot be deleted', () => {
    const b = mk(); b.deleteFloor('f2'); expect(b.data.value.locations.some(l => l.floor === 'f2')).toBe(false)
    expect(b.data.value.nodes.every(n => n.connections.every(c => b.node(c)))).toBe(true); expect(phpValidate(b.toJSON()).errors).toEqual([])
    const one = mk(null); expect(one.deleteFloor('fg')).toBe(false)
  })
  it('edits to text fields: blank optional fields become null, area label removed when blank', () => {
    const b = mk(); b.updateLocation('spa', { opening_hours: '' }); expect(b.location('spa')!.opening_hours).toBeNull()
    b.updateArea('fb1-spa', { label: '' }); expect(b.area('fb1-spa')!.label).toBeUndefined()
  })
  it('a fully edited map still passes the SERVER validator (same document contract)', () => {
    const b = mk(); b.floorId.value = 'f1'
    const a = b.addArea(60, 340, 100, 200, 'room'); const id = b.addLocation(110, 440, 'room'); b.updateLocation(id, { name: 'Room 110' }); b.setDefaultStart('spa')
    b.updateLocation('spa', { ref: { type: 'facility', slug: 'serenity-spa' } })
    const r = phpValidate(b.toJSON()); expect(r.errors).toEqual([]); expect(r.warnings).toEqual([])
    expect(b.toJSON().locations.find(l => l.id === id)!.node).toBe('f1-c0')
  })
  it('issues: unnamed place and a floor with places but no walkway are hard errors', () => {
    const b = mk(null); const id = b.addLocation(100, 100); b.updateLocation(id, { name: ' ' })
    const errs = b.issues.value.filter(i => i.level === 'error').map(i => i.text); expect(errs.some(t => /no name/.test(t))).toBe(true); expect(errs.some(t => /no walkway/.test(t))).toBe(true)
    expect(phpValidate(b.toJSON()).errors.length).toBeGreaterThan(0)                            // the server would refuse it too
  })
  it('history is capped and redo is cleared by a new edit', () => {
    const b = mk(null); for (let i = 0; i < 130; i++) b.addNode(i, i); let n = 0; while (b.canUndo.value) { b.undo(); n++ } expect(n).toBe(100)
    b.redo(); b.addNode(1, 1); expect(b.canRedo.value).toBe(false)
  })
})

describe('door picking', () => {
  it('setDoor pins a walkway point on the same floor; other floors are refused; moving keeps the pinned door', () => {
    const b = mk(); expect(b.setDoor('reception', 'fg-c3')).toBe(true); expect(b.location('reception')!.node).toBe('fg-c3')
    b.moveLocation('reception', 100, 100); expect(b.location('reception')!.node).toBe('fg-c3')
    expect(b.setDoor('reception', 'f2-c3')).toBe(false); expect(b.location('reception')!.node).toBe('fg-c3'); expect(b.doorPick.value).toBeNull()
    const r = b.testRoute('reception', 'azure')!; expect(r).not.toBeNull()
  })
})
