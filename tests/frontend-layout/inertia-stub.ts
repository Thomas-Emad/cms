import { defineComponent, h } from 'vue'
export const Link = defineComponent({ props: ['href'], setup(p, { slots, attrs }) { return () => h('a', { href: p.href, 'data-testid': 'link', ...attrs }, slots.default?.()) } })
export const router = { calls: [] as any[], visit(url: string) { this.calls.push({ url }) }, put(url: string, data: any, opts: any) { this.calls.push({ url, data, opts }) }, post() {}, on() { return () => {} } }
let _url = '/'
const _shared = { guestLayout: null as any }
export function usePage() { return { get url() { return _url }, props: _shared } }
export function __setUrl(u: string) { _url = u }
export function __setGuestLayout(v: any) { _shared.guestLayout = v }

/** Minimal Inertia useForm stub: reactive data + a `put` that records the call, and isDirty tracking. */
import { reactive, watch } from 'vue'
export function useForm<T extends object>(initial: T) {
  const snapshot = JSON.stringify(initial)
  const state: any = reactive({
    ...JSON.parse(JSON.stringify(initial)),
    isDirty: false,
    processing: false,
    put(url: string, opts: any = {}) {
      state.processing = true
      router.put(url, state.data(), opts)
      state.processing = false
      opts.onSuccess?.()
    },
    data() {
      const { isDirty, processing, put, data, ...rest } = state
      return JSON.parse(JSON.stringify(rest))
    },
  })
  watch(() => state.data(), (v) => { state.isDirty = JSON.stringify(v) !== snapshot }, { deep: true })
  return state as T & { isDirty: boolean; processing: boolean; put: (url: string, opts?: any) => void; data: () => T }
}
