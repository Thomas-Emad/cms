import { defineComponent, h } from 'vue'
export const Link = defineComponent({ props: ['href'], setup(p, { slots }) { return () => h('a', { href: p.href, 'data-testid': 'link' }, slots.default?.()) } })
export const router = { calls: [] as any[], put(url: string, data: any, opts: any) { this.calls.push({ url, data, opts }) }, post() {}, on() { return () => {} } }
