import { onMounted } from 'vue';
import type { Theme } from '@/types/hotel';

/**
 * Applies a hotel's theme as CSS custom properties on :root, so that
 * Tailwind utility classes / component CSS can reference var(--color-primary)
 * etc. instead of hard-coding colors per hotel.
 *
 * Phase 4 will extend this to also toggle Tailwind-plugin-driven radius/
 * button/card style classes; Phase 1 only wires the color/font variables.
 */
export function useTheme(theme?: Partial<Theme>) {
  const apply = (t?: Partial<Theme>) => {
    if (!t) return;
    const root = document.documentElement;

    if (t.primaryColor) root.style.setProperty('--color-primary', t.primaryColor);
    if (t.secondaryColor) root.style.setProperty('--color-secondary', t.secondaryColor);
    if (t.fontFamily) root.style.setProperty('--font-family', t.fontFamily);

    const radiusMap: Record<string, string> = {
      none: '0px',
      small: '4px',
      medium: '8px',
      large: '16px',
      full: '9999px',
    };
    if (t.borderRadius) {
      root.style.setProperty('--radius', radiusMap[t.borderRadius] ?? '8px');
    }
  };

  onMounted(() => apply(theme));

  return { applyTheme: apply };
}
