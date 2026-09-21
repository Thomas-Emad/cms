import { it, vi } from 'vitest'
import { mount } from '@vue/test-utils'
import { writeFileSync } from 'node:fs'
import data from './Map/demo-hotel-map.json'
import MapCanvas from './Map/MapCanvas.vue'
import { useCamera } from './Map/camera'
import { useMapNavigation } from './Map/useMapNavigation'

const CSS = `<style>.map-marker--dim{opacity:.28} .map-areas--dim{opacity:.45} .map-ring{opacity:.35} .map-you-pulse{opacity:.3}</style>`
function shot(name: string, setup: (n: any, cam: any) => any, floor?: string) {
  vi.stubGlobal('ResizeObserver', class { observe() {} disconnect() {} })
  vi.stubGlobal('requestAnimationFrame', (f: any) => { f(0); return 1 })
  const n: any = useMapNavigation(data as any); const cam = useCamera()
  cam.width.value = 1500; cam.height.value = 800
  cam.fitHome({ x: 40, y: 20, w: 920, h: 560 }, 30, true)
  const extra = setup(n, cam)
  if (floor) n.setFloor(floor)
  const w = mount(MapCanvas, { props: {
    data: n.data, floorId: n.floorId.value, floorDirection: 'up', camera: cam, selectedId: n.selectedId.value, destinationId: n.destinationId.value,
    routeOriginId: n.routeOrigin.value?.locationId ?? null, here: n.here.value, you: extra?.you ?? null, route: n.route.value, routeActive: n.routeActive.value,
    stepIndex: extra?.stepIndex ?? null, highlightIds: n.highlightIds.value, pickingStart: false } })
  return new Promise<void>(res => setTimeout(() => {
    const svg = w.find('svg').element.outerHTML.replace(/<transition-stub[^>]*>/g, '<g>').replace(/<\/transition-stub>/g, '</g>').replace('<svg', `<svg xmlns="http://www.w3.org/2000/svg" width="1500" height="800" style="background:#e9e2d3"`).replace('</svg>', CSS + '</svg>')
    writeFileSync(`/tmp/${name}.svg`, svg); w.unmount(); res()
  }, 30))
}
it('render floors + route', async () => {
  await shot('map-ground', () => ({}))
  await shot('map-route-g', (n: any) => { n.select('garden'); n.requestDirections() })
  await shot('map-route-2', (n: any) => { n.select('garden'); n.requestDirections() }, 'f2')
  await shot('map-highlight', (n: any) => { n.group.value = 'wellness' }, 'fb1')
})
