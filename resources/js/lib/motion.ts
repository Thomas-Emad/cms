// resources/js/lib/motion.ts
//
// Cinematic-but-restrained motion primitives for the guest site.
//
// Deliberately NOT a new animation library/dependency: this is ~2 small
// utilities built on native IntersectionObserver + CSS transitions
// (see .reveal / .reveal-scale in resources/css/app.css). Both respect
// prefers-reduced-motion globally via CSS, so components using them
// don't need to special-case it themselves.

import { type Directive, onMounted, onUnmounted, ref, type Ref } from 'vue';

/**
 * v-reveal — add the `reveal` (or `reveal-scale`) class in your template,
 * then `v-reveal` (optionally `v-reveal="{ delay: 120 }"` for stagger).
 * Adds `.is-visible` once the element is ~15% into the viewport.
 *
 * Usage:
 *   <h2 class="reveal" v-reveal>Section title</h2>
 *   <div v-for="(item, i) in items" class="reveal" v-reveal="{ delay: i * 80 }">
 */
interface RevealOptions {
    delay?: number;
    threshold?: number;
}

const revealObserver =
    typeof IntersectionObserver !== 'undefined'
        ? new IntersectionObserver(
              (entries) => {
                  for (const entry of entries) {
                      if (entry.isIntersecting) {
                          entry.target.classList.add('is-visible');
                          revealObserver?.unobserve(entry.target);
                      }
                  }
              },
              { threshold: 0.15, rootMargin: '0px 0px -10% 0px' },
          )
        : null;

export const vReveal: Directive<HTMLElement, RevealOptions | undefined> = {
    mounted(el, binding) {
        const { delay = 0 } = binding.value ?? {};
        if (delay) {
            el.style.setProperty('--reveal-delay', `${delay}ms`);
        }
        if (!revealObserver) {
            // No IntersectionObserver support (very old browser) — just show it.
            el.classList.add('is-visible');
            return;
        }
        revealObserver.observe(el);
    },
    unmounted(el) {
        revealObserver?.unobserve(el);
    },
};

/**
 * useParallax — subtle, slower-than-scroll vertical drift for hero/gallery
 * imagery. Returns a ref to bind as `style` on the image wrapper. Disabled
 * automatically under prefers-reduced-motion, and uses requestAnimationFrame
 * + passive scroll listener to stay cheap.
 *
 * Usage:
 *   const { style } = useParallax(heroRef, { strength: 0.15 });
 *   <div ref="heroRef"><img :style="style" ... /></div>
 */
export function useParallax(targetRef: Ref<HTMLElement | null>, options: { strength?: number } = {}) {
    const strength = options.strength ?? 0.15;
    const offset = ref(0);
    const prefersReduced =
        typeof window !== 'undefined' && window.matchMedia?.('(prefers-reduced-motion: reduce)').matches;

    let frame = 0;
    const onScroll = () => {
        if (prefersReduced || !targetRef.value) return;
        cancelAnimationFrame(frame);
        frame = requestAnimationFrame(() => {
            const rect = targetRef.value!.getBoundingClientRect();
            // Only bother updating while the element is anywhere near the viewport.
            if (rect.bottom < -200 || rect.top > window.innerHeight + 200) return;
            offset.value = rect.top * strength;
        });
    };

    onMounted(() => {
        if (prefersReduced) return;
        window.addEventListener('scroll', onScroll, { passive: true });
        onScroll();
    });
    onUnmounted(() => {
        window.removeEventListener('scroll', onScroll);
        cancelAnimationFrame(frame);
    });

    return {
        style: () => (prefersReduced ? {} : { transform: `translate3d(0, ${offset.value}px, 0)` }),
    };
}
