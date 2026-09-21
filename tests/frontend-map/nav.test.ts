import { describe, it, expect } from 'vitest'
import data from './Map/demo-hotel-map.json'
import { useMapNavigation } from './Map/useMapNavigation'
import type { HotelMapData } from './Map/types'
const map = data as unknown as HotelMapData
const mk = (m = map) => useMapNavigation(m)

describe('map state machine', () => {
  it('starts in explore on the start floor with "you are here" at reception', () => {
    const n = mk(); expect(n.state.value).toBe('explore'); expect(n.floorId.value).toBe('fg'); expect(n.here.value!.locationId).toBe('reception')
  })
  it('explore -> selected switches floor and collapses the card; deselect returns', () => {
    const n = mk(); n.select('garden'); expect(n.state.value).toBe('selected'); expect(n.floorId.value).toBe('f2'); expect(n.floorDirection.value).toBe('up'); expect(n.expanded.value).toBe(false)
    n.deselect(); expect(n.state.value).toBe('explore'); expect(n.selectedId.value).toBeNull()
  })
  it('directions -> preview (route computed from "here"), start -> navigating, steps walk to arrived', () => {
    const n = mk(); n.select('garden'); n.requestDirections(); expect(n.state.value).toBe('preview')
    expect(n.route.value!.floors).toEqual(['fg', 'f2']); expect(n.floorId.value).toBe('fg')       // preview starts on the origin floor
    n.startNavigation(); expect(n.state.value).toBe('navigating'); expect(n.stepIndex.value).toBe(0)
    const last = n.route.value!.steps.length - 1
    for (let i = 0; i < last - 1; i++) n.nextStep()
    expect(n.state.value).toBe('navigating'); n.nextStep(); expect(n.state.value).toBe('arrived'); expect(n.currentStep.value!.kind).toBe('arrive'); expect(n.floorId.value).toBe('f2')
  })
  it('the floor follows the step: after the elevator step we are on the destination floor', () => {
    const n = mk(); n.requestDirections('garden'); n.startNavigation()
    const lift = n.route.value!.steps.findIndex(s => s.kind === 'elevator'); n.goToStep(lift); expect(n.floorId.value).toBe('fg'); n.goToStep(lift + 1); expect(n.floorId.value).toBe('f2')
  })
  it('prev never goes below 0; goToStep clamps', () => {
    const n = mk(); n.requestDirections('garden'); n.startNavigation(); n.prevStep(); expect(n.stepIndex.value).toBe(0); n.goToStep(99); expect(n.state.value).toBe('arrived')
  })
  it('finish: you are now at the destination, its card is shown, route cleared', () => {
    const n = mk(); n.requestDirections('garden'); n.startNavigation(); n.goToStep(99); n.finish()
    expect(n.state.value).toBe('selected'); expect(n.selectedId.value).toBe('garden'); expect(n.here.value!.locationId).toBe('garden'); expect(n.route.value).toBeNull()
  })
  it('cancel from preview returns to the selected card', () => {
    const n = mk(); n.select('spa'); n.requestDirections(); n.cancelRoute(); expect(n.state.value).toBe('selected'); expect(n.route.value).toBeNull()
  })
  it('no known start -> choosing; setting an origin computes the route and previews', () => {
    const n = mk({ ...map, default_start: null }); expect(n.here.value).toBeNull()
    n.select('spa'); n.requestDirections(); expect(n.state.value).toBe('choosing')
    n.setStartFromLocation(n.byId.get('gym')!); expect(n.state.value).toBe('preview'); expect(n.route.value!.floors).toEqual(['fb1'])
  })
  it('"set starting point" by tapping the map snaps to the nearest walkway node; markers are inert while picking is off in preview', () => {
    const n = mk({ ...map, default_start: null }); n.setFloor('f2'); n.pickingStart.value = true
    expect(n.tapMap({ x: 700, y: 200 })).toBe(true); expect(n.here.value!.nodeId).toBe('f2-c5'); expect(n.pickingStart.value).toBe(false)
    expect(n.tapMap({ x: 1, y: 1 })).toBe(false) // not picking any more
    n.select('garden'); n.requestDirections(); n.select('spa'); expect(n.selectedId.value).toBe('garden') // markers ignored during preview
  })
  it('picking a marker while picking start uses it as the start', () => {
    const n = mk(); n.pickingStart.value = true; n.select('cafe-aroma'); expect(n.here.value!.locationId).toBe('cafe-aroma'); expect(n.state.value).toBe('explore')
  })
  it('swap reverses the route; same start and destination is a friendly error', () => {
    const n = mk(); n.requestDirections('garden'); const meters = n.route.value!.meters; n.swap()
    expect(n.destinationId.value).toBe('reception'); expect(n.routeOrigin.value!.locationId).toBe('garden'); expect(n.route.value!.meters).toBeCloseTo(meters, 3)
    const m = mk(); m.requestDirections('reception'); expect(m.routeError.value).toContain('already here'); expect(m.route.value).toBeNull()
  })
  it('category chips highlight the right locations; explore = no filter', () => {
    const n = mk(); expect(n.highlightIds.value).toBeNull(); n.group.value = 'meeting'
    expect([...n.highlightIds.value!].sort()).toEqual(['boardroom', 'meeting-a', 'meeting-b']); n.group.value = 'restaurants'; expect(n.highlightIds.value!.has('azure')).toBe(true); expect(n.highlightIds.value!.has('gym')).toBe(false)
  })
  it('search: by name, prefix, floor, category; ranked; empty query = no results', () => {
    const n = mk(); expect(n.results.value).toEqual([])
    n.query.value = 'spa'; expect(n.results.value[0].id).toBe('spa')
    n.query.value = 'room 30'; expect(n.results.value.every(l => l.name.includes('Room 30') || l.id.startsWith('room-30'))).toBe(true)
    n.query.value = 'meeting'; expect(n.results.value.map(l => l.id)).toEqual(expect.arrayContaining(['meeting-a', 'boardroom']))
    n.query.value = 'basement'; expect(n.results.value.some(l => l.id === 'pool')).toBe(true)
    n.query.value = 'zzzz'; expect(n.results.value).toEqual([])
  })
  it('unknown ids are ignored', () => { const n = mk(); n.select('nope'); expect(n.state.value).toBe('explore'); n.requestDirections('nope'); expect(n.state.value).toBe('explore') })
})
