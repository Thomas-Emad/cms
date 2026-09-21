import { describe, it, expect } from 'vitest'
import data from './Map/demo-hotel-map.json'
import { buildGraph, planRoute, shortestPath, originFromLocation, originFromPoint, nodeForLocation, turnAngle, formatDistance } from './Map/routing'
import type { HotelMapData } from './Map/types'

const map = data as unknown as HotelMapData
const graph = buildGraph(map)
const L = (id: string) => map.locations.find(l => l.id === id)!
const route = (from: string, to: string) => planRoute(graph, map, originFromLocation(map, L(from))!, L(to))!
const texts = (r: any) => r.steps.map((s: any) => s.text)

describe('demo data integrity', () => {
  it('every location has a node on its own floor and is reachable from reception', () => {
    for (const l of map.locations) {
      const n = nodeForLocation(map, l)!; expect(n, l.id).toBeTruthy(); expect(n.floor, l.id).toBe(l.floor)
      expect(shortestPath(graph, 'fg-c0', n.id), `path to ${l.id}`).not.toBeNull()
    }
  })
  it('all coordinates are inside their floor', () => {
    for (const l of map.locations) { const f = map.floors.find(f => f.id === l.floor)!; expect(l.x).toBeGreaterThanOrEqual(0); expect(l.x).toBeLessThanOrEqual(f.width); expect(l.y).toBeLessThanOrEqual(f.height) }
  })
})

describe('turnAngle', () => {
  it('east then south is a right turn; east then north is left; straight is 0', () => {
    expect(turnAngle({ x: 1, y: 0 }, { x: 0, y: 1 })).toBeCloseTo(90); expect(turnAngle({ x: 1, y: 0 }, { x: 0, y: -1 })).toBeCloseTo(-90); expect(turnAngle({ x: 1, y: 0 }, { x: 1, y: 0 })).toBeCloseTo(0)
  })
})

describe('routing', () => {
  it('same-floor route: no elevator, arrives, distance is corridor length', () => {
    const r = route('reception', 'azure'); expect(r.floors).toEqual(['fg'])
    expect(r.steps.at(-1)!.kind).toBe('arrive'); expect(r.transitions).toHaveLength(0)
    expect(r.meters).toBeCloseTo((cx(6) - cx(0)) * 0.1, 0)
  })
  it('spec example: Reception -> The Garden Restaurant goes via ONE elevator, up to the 2nd floor', () => {
    const r = route('reception', 'garden')
    expect(r.floors).toEqual(['fg', 'f2']); expect(r.transitions).toHaveLength(1)
    expect(r.transitions[0]).toMatchObject({ kind: 'elevator', direction: 'up', floor: 'fg', toFloor: 'f2' })
    const t = texts(r); expect(t).toContain('Take the elevator'); expect(t.at(-1)).toBe('You have arrived')
    const lift = r.steps.findIndex(s => s.kind === 'elevator'); expect(r.steps[lift].detail).toContain('2nd Floor')
  })
  it('after exiting the elevator, the turn is computed from the cab door (west of the lift = turn RIGHT when facing south)', () => {
    const r = route('reception', 'garden'); const exit = r.steps[r.steps.findIndex(s => s.kind === 'elevator') + 1]
    expect(exit.kind).toBe('right'); expect(exit.detail).toContain('After exiting the elevator, turn right')
  })
  it('east of the lift the exit turn flips to LEFT', () => {
    const r = route('reception', 'room-201'); const exit = r.steps[r.steps.findIndex(s => s.kind === 'elevator') + 1]; expect(exit.kind).toBe('left')
  })
  it('leaving a room adds a turn onto the corridor (reception is north of the corridor, going east = turn right when facing south)', () => {
    const r = route('reception', 'azure'); expect(r.steps[0].kind).toBe('left'); expect(r.steps[0].detail).toMatch(/^Leave Reception/)
  })
  it('arrival side: destination south of an eastbound corridor is on the RIGHT, north is on the LEFT', () => {
    expect(route('reception', 'azure').steps.at(-1)!.detail).toContain('on your right')
    expect(route('reception', 'cafe-aroma').steps.at(-1)!.detail).toContain('on your left')
  })
  it('basement route goes DOWN and prefers the elevator over stairs', () => {
    const r = route('reception', 'spa'); expect(r.transitions[0]).toMatchObject({ kind: 'elevator', direction: 'down' })
  })
  it('multi-floor: 3rd floor suite from basement pool collapses to one elevator step spanning floors', () => {
    const r = route('pool', 'presidential'); expect(r.steps.filter(s => s.kind === 'elevator')).toHaveLength(1)
    expect(r.floors).toEqual(['fb1', 'f3']); expect(r.segments.map(s => s.floor)).toEqual(['fb1', 'f3'])
  })
  it('totals equal the sum of steps; time includes elevator wait', () => {
    const r = route('reception', 'garden'); expect(r.meters).toBeCloseTo(r.steps.reduce((a, s) => a + s.meters, 0))
    expect(r.seconds).toBeGreaterThan(r.meters / 1.25 + 30)
  })
  it('routes are symmetric in distance', () => {
    const a = route('reception', 'garden'), b = route('garden', 'reception'); expect(a.meters).toBeCloseTo(b.meters, 3)
  })
  it('same node: single arrival step', () => {
    const r = route('spa', 'juice-bar'); expect(r.steps.at(-1)!.kind).toBe('arrive') // both use c6
    expect(r.meters).toBe(0); expect(r.steps.length).toBeGreaterThanOrEqual(1)
  })
  it('origin from a tapped point snaps to the nearest walkway node on that floor', () => {
    const o = originFromPoint(map, 'f2', { x: 430, y: 200 })!; expect(o.nodeId).toBe('f2-c3')
    const r = planRoute(graph, map, o, L('garden'))!; expect(r.floors).toEqual(['f2'])
  })
  it('stairs are used when the elevator link is removed (graph really drives routing)', () => {
    const cut = structuredClone(map) as HotelMapData
    for (const n of cut.nodes) n.connections = n.connections.filter(c => !(n.type === 'elevator' && cut.nodes.find(x => x.id === c)!.floor !== n.floor))
    const r = planRoute(buildGraph(cut), cut, originFromLocation(cut, cut.locations.find(l => l.id === 'reception')!)!, cut.locations.find(l => l.id === 'garden')!)!
    expect(r.transitions[0].kind).toBe('stairs'); expect(r.steps.find(s => s.kind === 'stairs')!.text).toBe('Take the stairs up')
  })
  it('unreachable destination returns null', () => {
    const cut = structuredClone(map) as HotelMapData; for (const n of cut.nodes) n.connections = n.connections.filter(c => !c.startsWith('f2-') || n.floor === 'f2')
    for (const n of cut.nodes.filter(n => n.floor === 'f2')) n.connections = n.connections.filter(c => c.startsWith('f2-'))
    expect(planRoute(buildGraph(cut), cut, originFromLocation(cut, cut.locations.find(l => l.id === 'reception')!)!, cut.locations.find(l => l.id === 'garden')!)).toBeNull()
  })
  it('formatting', () => { expect(formatDistance(12.4)).toBe('12 m'); expect(formatDistance(0.2)).toBe('') })
})
const cx = (i: number) => 60 + i * 110 + 55
