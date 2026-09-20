import { ref, watchEffect, onBeforeUnmount, toValue, type MaybeRefOrGetter } from 'vue'

/**
 * Whether the current page starts with a full-bleed dark hero, so the fixed
 * header can sit transparent over it.
 *
 * Why default false: a solid header is the safe state. A transparent header
 * with white text is invisible on any page that has no hero image, so
 * transparency must be opt-in, never assumed.
 *
 * Module-level ref so GuestLayout and the active page share one flag.
 */
export const headerOverHero = ref(false)

/**
 * Call from any page that begins with a hero.
 * Accepts a boolean or a getter so Builder pages can decide from their
 * first section, and Show pages from whether a cover image exists.
 */
export function useHeroHeader(enabled: MaybeRefOrGetter<boolean> = true) {
    watchEffect(() => {
        headerOverHero.value = toValue(enabled)
    })

    // Reset on leave so the next page starts from the safe solid state.
    onBeforeUnmount(() => {
        headerOverHero.value = false
    })
}
