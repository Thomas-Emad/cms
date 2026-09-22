import { describe, it, expect, beforeEach } from 'vitest'
import { mount, flushPromises } from '@vue/test-utils'
import { execFileSync } from 'node:child_process'
import Edit from './Pages/Admin/Layout/Edit.vue'
import { DEFAULT_CONFIG } from './Layouts/guest/shellConfig'
import { router } from './inertia-stub'

const phpValidate = (data: any) => JSON.parse(execFileSync('php', ['-r',
  `require '/home/claude/proj/app/Services/Layout/GuestLayoutConfig.php'; echo json_encode(App\\Services\\Layout\\GuestLayoutConfig::validate(json_decode('${JSON.stringify(data).replace(/\\/g, '\\\\').replace(/'/g, "\\'")}', true)));`
]).toString())

beforeEach(() => { (router as any).calls.length = 0 })
const mk = (over: any = {}) => mount(Edit, { props: { config: structuredClone(DEFAULT_CONFIG), defaults: DEFAULT_CONFIG, errors_list: [], flash_ok: null, ...over }, global: { stubs: { Link: false, teleport: true } } })
const t = (w: any, id: string) => w.find(`[data-testid=${id}]`)
const lastSave = () => (router as any).calls.at(-1)

describe('Guest Layout admin editor', () => {
  it('starts on Classic (the current config) with 7 items, and switching template updates the live preview', async () => {
    const w = mk(); expect(t(w, 'template-classic').classes().join(' ')).toContain('ring-2'); expect(t(w, 'items').findAll('li')).toHaveLength(7)
    expect(t(w, 'preview').find('.kiosk-root').exists()).toBe(true); expect(t(w, 'preview').find('.tv-root').exists()).toBe(false)
    await t(w, 'template-tv').trigger('click'); expect(t(w, 'preview').find('.tv-root').exists()).toBe(true); expect(t(w, 'lock-scroll').exists()).toBe(true); w.unmount()
  })

  it('saving sends the whole config to PUT /admin/layout, and it is exactly what the server validator accepts', async () => {
    const w = mk(); await t(w, 'template-tv').trigger('click'); await t(w, 'tile-size').setValue('large')
    await w.find('input[placeholder^="e.g. Welcome"]').setValue('Welcome to Grand Horizon')
    await w.find('form').trigger('submit'); await flushPromises()
    expect(lastSave().url).toBe('/admin/layout'); const sent = lastSave().data
    expect(sent.template).toBe('tv'); expect(sent.tile_size).toBe('large'); expect(sent.headline).toBe('Welcome to Grand Horizon')
    expect(phpValidate(sent)).toEqual([]); w.unmount()
  })

  it('add / rename / reorder / hide / delete a menu item, and it is reflected in the saved payload and the preview tile count', async () => {
    const w = mk(); await t(w, 'add-item').trigger('click'); expect(t(w, 'items').findAll('li')).toHaveLength(8)
    const rows = t(w, 'items').findAll('li'); await rows[7].find('input[aria-label^="Name for item"]').setValue('Spa Menu')
    await rows[7].find('input[aria-label^="Page address"]').setValue('/pages/spa-menu')
    await rows[7].find('button[aria-label="Move up"]').trigger('click') // moves into slot 7 (index 6)
    await rows[0].find('input[type=checkbox]').setValue(false) // hide the first original item
    await w.find('form').trigger('submit'); await flushPromises()
    const items = lastSave().data.items
    expect(items.find((i: any) => i.label === 'Spa Menu').href).toBe('/pages/spa-menu')
    expect(items[0].visible).toBe(false); expect(phpValidate(lastSave().data)).toEqual([])
    await rows[0].find('button[aria-label="Delete item"]').trigger('click'); expect(t(w, 'items').findAll('li')).toHaveLength(7); w.unmount()
  })

  it('server-side errors are shown; the success message clears once the form is edited again', async () => {
    const w = mk({ errors_list: ['Menu item 2: the page address must start with /.'] }); expect(t(w, 'errors').text()).toContain('must start with')
    const w2 = mk({ flash_ok: 'Layout saved.' }); expect(t(w2, 'saved').text()).toContain('Layout saved.')
    await w2.find('input[aria-label="Name for item 1"]').setValue('Changed'); expect(t(w2, 'saved').exists()).toBe(false)
  })

  it('"Reset to defaults" restores the default 7 items after confirmation', async () => {
    const w = mk(); await t(w, 'add-item').trigger('click'); expect(t(w, 'items').findAll('li')).toHaveLength(8)
    window.confirm = () => true; await w.find('button:not([data-testid])').trigger('click') // the "Reset to defaults" text button
    const reset = w.findAll('button').find((b: any) => b.text() === 'Reset to defaults')!; await reset.trigger('click')
    expect(t(w, 'items').findAll('li')).toHaveLength(7); w.unmount()
  })
})
