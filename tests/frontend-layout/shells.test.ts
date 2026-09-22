import { describe, it, expect, vi, beforeEach, afterEach } from 'vitest'
import { mount, flushPromises } from '@vue/test-utils'
import ClassicShell from './Layouts/guest/ClassicShell.vue'
import TvShell from './Layouts/guest/TvShell.vue'
import GuestLayout from './Layouts/GuestLayout.vue'
import { DEFAULT_CONFIG, resolveConfig } from './Layouts/guest/shellConfig'
import { router, __setUrl, __setGuestLayout } from './inertia-stub'

beforeEach(() => { vi.useFakeTimers(); __setUrl('/'); __setGuestLayout(null); (router as any).calls.length = 0 })
afterEach(() => { vi.useRealTimers(); document.documentElement.style.overflow = ''; document.body.style.overflow = ''; document.documentElement.style.fontSize = '' })

const hotel = { name: 'Grand Horizon', slug: 'gh' }

describe('ClassicShell (unchanged behaviour)', () => {
  it('shows the hotel name, clock (when enabled), and only VISIBLE items in the dock, in order', () => {
    const cfg = { ...DEFAULT_CONFIG, items: [...DEFAULT_CONFIG.items.slice(0, 2), { ...DEFAULT_CONFIG.items[2], visible: false }] }
    const w = mount(ClassicShell, { props: { hotel, config: cfg }, global: { stubs: { Link: false } } })
    expect(w.text()).toContain('Grand Horizon'); expect(w.findAll('nav a')).toHaveLength(2); expect(w.find('[aria-label="Current time"]').exists()).toBe(true)
  })
  it('hides the clock when show_clock is off, and hides "Home" while already on home', () => {
    const w = mount(ClassicShell, { props: { hotel, config: { ...DEFAULT_CONFIG, show_clock: false } } })
    expect(w.find('[aria-label="Current time"]').exists()).toBe(false); expect(w.text()).not.toContain('← Home')
  })
  it('sets the root font-size on mount and restores it on unmount (screen scaling, unchanged)', () => {
    const w = mount(ClassicShell, { props: { hotel, config: DEFAULT_CONFIG } })
    expect(document.documentElement.style.fontSize).toContain('clamp'); w.unmount(); expect(document.documentElement.style.fontSize).toBe('')
  })
  it('idle timeout sends the guest back home', () => {
    __setUrl('/facilities'); mount(ClassicShell, { props: { hotel, config: DEFAULT_CONFIG } })
    vi.advanceTimersByTime(90_000); expect((router as any).calls.at(-1)).toEqual({ url: '/' })
  })
})

describe('TvShell', () => {
  it('home: brand + tagline + headline show, tiles include a Home tile then the visible items with icons/colours', () => {
    const cfg = { ...DEFAULT_CONFIG, template: 'tv' as const, tagline: 'A Smarter Stay', headline: 'Welcome' }
    const w = mount(TvShell, { props: { hotel, config: cfg } })
    expect(w.find('[data-testid=tv-tagline]').text()).toBe('A Smarter Stay'); expect(w.find('[data-testid=tv-headline]').text()).toBe('Welcome')
    expect(w.find('[data-testid=tile-home]').attributes('aria-current')).toBe('page')
    expect(w.findAll('[data-testid=tv-tile]')).toHaveLength(cfg.items.length)
  })
  it('home is scroll-LOCKED by default: html/body overflow hidden while mounted, restored after unmount', () => {
    const w = mount(TvShell, { props: { hotel, config: { ...DEFAULT_CONFIG, template: 'tv' as const } }, attachTo: document.body })
    expect(document.documentElement.style.overflow).toBe('hidden'); expect(document.body.style.overflow).toBe('hidden')
    w.unmount(); expect(document.documentElement.style.overflow).toBe('')
  })
  it('turning the lock off leaves scrolling alone', () => {
    mount(TvShell, { props: { hotel, config: { ...DEFAULT_CONFIG, template: 'tv' as const, lock_home_scroll: false } } })
    expect(document.documentElement.style.overflow).toBe('')
  })
  it('on any OTHER page, home elements are gone, the slim bar is shown, and it is not locked even with the setting on', () => {
    __setUrl('/facilities')
    const w = mount(TvShell, { props: { hotel, config: { ...DEFAULT_CONFIG, template: 'tv' as const, headline: 'Welcome' } } })
    expect(w.find('[data-testid=tv-brand]').exists()).toBe(false); expect(w.find('[data-testid=tv-headline]').exists()).toBe(false)
    expect(document.documentElement.style.overflow).toBe('')
  })
  it('tile size changes the CSS variable driving tile width', () => {
    const small = mount(TvShell, { props: { hotel, config: { ...DEFAULT_CONFIG, template: 'tv' as const, tile_size: 'small' as const } } })
    const large = mount(TvShell, { props: { hotel, config: { ...DEFAULT_CONFIG, template: 'tv' as const, tile_size: 'large' as const } } })
    expect((small.find('.tv-root').element as HTMLElement).style.getPropertyValue('--tv-tile-w')).toBe('9.5rem')
    expect((large.find('.tv-root').element as HTMLElement).style.getPropertyValue('--tv-tile-w')).toBe('14rem')
  })
  it('ArrowRight/ArrowLeft move focus along the tile rail (remote-control navigation)', async () => {
    const w = mount(TvShell, { props: { hotel, config: { ...DEFAULT_CONFIG, template: 'tv' as const } }, attachTo: document.body })
    const tiles = w.findAll('[data-testid=tv-rail] a')
    ;(tiles[0].element as HTMLElement).focus()
    window.dispatchEvent(new KeyboardEvent('keydown', { key: 'ArrowRight' })); await flushPromises()
    expect(document.activeElement).toBe(tiles[1].element)
    window.dispatchEvent(new KeyboardEvent('keydown', { key: 'ArrowRight' })); await flushPromises()
    expect(document.activeElement).toBe(tiles[2].element)
    window.dispatchEvent(new KeyboardEvent('keydown', { key: 'ArrowLeft' })); await flushPromises()
    expect(document.activeElement).toBe(tiles[1].element); w.unmount()
  })
  it('idle timeout still applies on the TV template', () => {
    __setUrl('/facilities'); mount(TvShell, { props: { hotel, config: { ...DEFAULT_CONFIG, template: 'tv' as const } } })
    vi.advanceTimersByTime(90_000); expect((router as any).calls.at(-1)).toEqual({ url: '/' })
  })
})

describe('GuestLayout switches template based on the shared prop', () => {
  it('defaults to Classic when nothing is configured (an old hotel with no saved layout)', () => {
    const w = mount(GuestLayout, { props: { hotel } })
    expect(w.find('.tv-root').exists()).toBe(false); expect(w.find('.kiosk-root').exists()).toBe(true)
  })
  it('renders TvShell when the shared prop says template: tv', async () => {
    __setGuestLayout({ ...DEFAULT_CONFIG, template: 'tv' })
    const w = mount(GuestLayout, { props: { hotel } }); expect(w.find('.tv-root').exists()).toBe(true)
  })
})
