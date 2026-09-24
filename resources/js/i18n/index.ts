import { computed } from 'vue';
import { router, usePage } from '@inertiajs/vue3';

export type Locale = 'en' | 'ar';
export type Direction = 'ltr' | 'rtl';

export interface I18nProps {
    locale?: Locale;
    direction?: Direction;
    translations?: Record<string, unknown>;
}

function resolveNestedKey(obj: Record<string, unknown>, path: string): unknown {
    if (!obj || typeof obj !== 'object') return undefined;
    if (path in obj) return obj[path];

    const parts = path.split('.');
    let current: unknown = obj;
    for (const part of parts) {
        if (current && typeof current === 'object' && part in (current as Record<string, unknown>)) {
            current = (current as Record<string, unknown>)[part];
        } else {
            return undefined;
        }
    }
    return current;
}

export function t(key: string, params?: Record<string, string | number>, fallback?: string): string {
    const page = usePage<I18nProps>();
    const dict = (page.props.translations as Record<string, unknown>) || {};

    const raw = resolveNestedKey(dict, key);
    let message = typeof raw === 'string' ? raw : (fallback ?? key);

    if (params) {
        for (const [k, v] of Object.entries(params)) {
            message = message.replace(new RegExp(`\\{${k}\\}`, 'g'), String(v));
            message = message.replace(new RegExp(`:${k}\\b`, 'g'), String(v));
        }
    }

    return message;
}

export function useI18n() {
    const page = usePage<I18nProps>();

    const locale = computed<Locale>(() => (page.props.locale as Locale) || 'en');
    const direction = computed<Direction>(() => (page.props.direction as Direction) || (locale.value === 'ar' ? 'rtl' : 'ltr'));
    const isRtl = computed(() => direction.value === 'rtl');

    function switchLocale(nextLocale: Locale) {
        if (nextLocale === locale.value) return;

        // Apply attribute immediately to minimize visual lag during visit
        document.documentElement.lang = nextLocale;
        document.documentElement.dir = nextLocale === 'ar' ? 'rtl' : 'ltr';

        router.post(
            window.route ? window.route('locale.update') : '/locale',
            { locale: nextLocale },
            {
                preserveScroll: true,
                preserveState: false,
                onSuccess: () => {
                    document.documentElement.lang = nextLocale;
                    document.documentElement.dir = nextLocale === 'ar' ? 'rtl' : 'ltr';
                },
            }
        );
    }

    return {
        locale,
        direction,
        isRtl,
        t,
        switchLocale,
    };
}

export function useLocale() {
    const { locale, direction, isRtl, switchLocale } = useI18n();
    return { locale, direction, isRtl, switchLocale };
}
