import { describe, it, expect } from 'vitest'
import { execFileSync } from 'node:child_process'
import { resolveConfig, visibleItems, readableOn, DEFAULT_CONFIG, TEMPLATES, TILE_DIMENSIONS } from './Layouts/guest/shellConfig'

const phpValidate = (data: any) => JSON.parse(execFileSync('php', ['-r',
  `require '/home/claude/proj/app/Services/Layout/GuestLayoutConfig.php'; echo json_encode(App\\Services\\Layout\\GuestLayoutConfig::validate(json_decode('${JSON.stringify(data).replace(/\\/g, '\\\\').replace(/'/g, "\\'")}', true)));`
]).toString())

describe('shellConfig (frontend) matches GuestLayoutConfig (server)', () => {
  it('DEFAULT_CONFIG is exactly what the server considers valid, with no errors', () => {
    expect(phpValidate(DEFAULT_CONFIG)).toEqual([])
    expect(DEFAULT_CONFIG.template).toBe('classic'); expect(DEFAULT_CONFIG.items).toHaveLength(7)
  })
  it('resolveConfig(undefined / null / garbage) falls back to the full defaults, same as the server', () => {
    for (const bad of [undefined, null, 'x', 42, { template: 'hologram' }]) {
      expect(resolveConfig(bad)).toEqual(DEFAULT_CONFIG)
    }
  })
  it('resolveConfig keeps a partially-valid object and fills in the rest (never throws, never half-broken)', () => {
    const r = resolveConfig({ template: 'tv', tile_size: 'large', headline: 'Hi' })
    expect(r).toEqual({ ...DEFAULT_CONFIG, template: 'tv', tile_size: 'large', headline: 'Hi' })
  })
  it('resolveConfig drops individually-broken items but keeps the good ones', () => {
    const r = resolveConfig({ items: [{ href: '/ok', label: 'Ok', icon: '', color: null, visible: true }, { href: 'bad', label: 'x' }, { href: '/y', label: '' }] })
    expect(r.items).toEqual([{ href: '/ok', label: 'Ok', icon: '', color: null, visible: true }])
  })
  it('visibleItems filters out hidden items, in order', () => {
    const items = [{ href: '/a', label: 'A', icon: '', color: null, visible: true }, { href: '/b', label: 'B', icon: '', color: null, visible: false }, { href: '/c', label: 'C', icon: '', color: null, visible: true }]
    expect(visibleItems({ ...DEFAULT_CONFIG, items }).map((i) => i.href)).toEqual(['/a', '/c'])
  })
  it('every template id the frontend offers is one the server accepts, and vice versa', () => {
    const front = TEMPLATES.map((t) => t.id).sort()
    for (const id of front) expect(phpValidate({ ...DEFAULT_CONFIG, template: id })).toEqual([])
    expect(phpValidate({ ...DEFAULT_CONFIG, template: 'not-a-real-one' }).length).toBeGreaterThan(0)
  })
  it('every tile size is one the server accepts', () => {
    for (const s of Object.keys(TILE_DIMENSIONS)) expect(phpValidate({ ...DEFAULT_CONFIG, tile_size: s })).toEqual([])
  })
  it('readableOn picks white text on dark tiles and black text on light tiles', () => {
    expect(readableOn('#1f2937')).toBe('#ffffff'); expect(readableOn('#ffe08a')).toBe('#111111'); expect(readableOn(null)).toBe('#ffffff')
  })
  it('a config the admin editor could plausibly save also passes the server validator', () => {
    const edited = resolveConfig({ template: 'tv', show_clock: false, lock_home_scroll: false, tile_size: 'small', tagline: 'A Smarter Stay', headline: 'Welcome',
      items: [{ href: '/spa', label: 'Spa', icon: '🧖', color: '#4d8a7b', visible: true }, { href: '/pool', label: 'Pool', icon: '🏊', color: null, visible: false }] })
    expect(phpValidate(edited)).toEqual([])
  })
})
