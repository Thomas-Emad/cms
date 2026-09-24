import { onMounted } from 'vue';

export interface ThemeInput {
  id?: number;
  name?: string;
  primaryColor?: string;
  primary_color?: string;
  secondaryColor?: string;
  secondary_color?: string;
  headerBg?: string;
  header_bg?: string;
  footerBg?: string;
  footer_bg?: string;
  fontFamily?: string;
  font_family?: string;
  borderRadius?: string;
  border_radius?: string;
  buttonStyle?: string;
  button_style?: string;
  cardStyle?: string;
  card_style?: string;
  css_variables?: Record<string, string>;
}

function hexToRgba(hex: string, alpha: number): string {
  if (hex.startsWith('rgba') || hex.startsWith('rgb')) return hex;
  let clean = hex.replace('#', '');
  if (clean.length === 3) {
    clean = clean.split('').map(c => c + c).join('');
  }
  if (clean.length !== 6) return `rgba(10, 12, 16, ${alpha})`;
  const r = parseInt(clean.substring(0, 2), 16);
  const g = parseInt(clean.substring(2, 4), 16);
  const b = parseInt(clean.substring(4, 6), 16);
  return `rgba(${r}, ${g}, ${b}, ${alpha})`;
}

function deriveDarkenedRgba(hex: string, alpha: number, factor = 0.35): string {
  let clean = hex.replace('#', '');
  if (clean.length === 3) {
    clean = clean.split('').map(c => c + c).join('');
  }
  if (clean.length !== 6) return `rgba(10, 12, 16, ${alpha})`;
  const r = Math.round(parseInt(clean.substring(0, 2), 16) * factor);
  const g = Math.round(parseInt(clean.substring(2, 4), 16) * factor);
  const b = Math.round(parseInt(clean.substring(4, 6), 16) * factor);
  return `rgba(${r}, ${g}, ${b}, ${alpha})`;
}

function deriveDarkenedHex(hex: string, factor = 0.22): string {
  let clean = hex.replace('#', '');
  if (clean.length === 3) {
    clean = clean.split('').map(c => c + c).join('');
  }
  if (clean.length !== 6) return '#0b0e13';
  const r = Math.max(0, Math.min(255, Math.round(parseInt(clean.substring(0, 2), 16) * factor)));
  const g = Math.max(0, Math.min(255, Math.round(parseInt(clean.substring(2, 4), 16) * factor)));
  const b = Math.max(0, Math.min(255, Math.round(parseInt(clean.substring(4, 6), 16) * factor)));
  return `#${r.toString(16).padStart(2, '0')}${g.toString(16).padStart(2, '0')}${b.toString(16).padStart(2, '0')}`;
}

/**
 * Applies a hotel's guest theme as CSS custom properties on :root, so that
 * Tailwind utility classes, layouts, header, footer, dock, buttons, and cards
 * dynamically reflect the active theme colors.
 */
export function useTheme(theme?: ThemeInput) {
  const apply = (t?: ThemeInput) => {
    if (!t || typeof document === 'undefined') return;
    const root = document.documentElement;

    const primary = t.primaryColor ?? t.primary_color;
    if (primary) {
      root.style.setProperty('--color-primary', primary);
      root.style.setProperty('--luxury-forest', primary);
    }

    const secondary = t.secondaryColor ?? t.secondary_color;
    if (secondary) {
      root.style.setProperty('--color-secondary', secondary);
      root.style.setProperty('--luxury-champagne', secondary);
    }

    // Header & Footer colors: use explicit values or derive from primary
    const headerBg = t.headerBg ?? t.header_bg;
    if (headerBg) {
      root.style.setProperty('--header-bg', hexToRgba(headerBg, 0.88));
      root.style.setProperty('--header-bg-solid', headerBg);
    } else if (primary) {
      root.style.setProperty('--header-bg', deriveDarkenedRgba(primary, 0.88, 0.35));
      root.style.setProperty('--header-bg-solid', deriveDarkenedHex(primary, 0.35));
    }

    const footerBg = t.footerBg ?? t.footer_bg;
    if (footerBg) {
      root.style.setProperty('--footer-bg', footerBg);
      root.style.setProperty('--dock-bg', hexToRgba(footerBg, 0.92));
      root.style.setProperty('--footer-bg-solid', footerBg);
    } else if (primary) {
      const derivedDarkFooter = deriveDarkenedHex(primary, 0.22);
      root.style.setProperty('--footer-bg', derivedDarkFooter);
      root.style.setProperty('--dock-bg', deriveDarkenedRgba(primary, 0.92, 0.25));
      root.style.setProperty('--footer-bg-solid', derivedDarkFooter);
    }

    const font = t.fontFamily ?? t.font_family;
    if (font) {
      root.style.setProperty('--font-sans', font);
    }

    const radius = t.borderRadius ?? t.border_radius;
    const radiusMap: Record<string, string> = {
      none: '0px',
      small: '4px',
      medium: '8px',
      large: '16px',
      full: '9999px',
    };
    if (radius) {
      root.style.setProperty('--radius', radiusMap[radius] ?? '8px');
    }

    if (t.css_variables) {
      for (const [key, val] of Object.entries(t.css_variables)) {
        if (typeof val === 'string') {
          root.style.setProperty(key, val);
        }
      }
    }
  };

  onMounted(() => apply(theme));

  return { applyTheme: apply };
}
