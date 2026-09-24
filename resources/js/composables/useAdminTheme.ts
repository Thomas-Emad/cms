import { ref } from 'vue';

export type AdminTheme = 'green' | 'forest' | 'slate' | 'blue' | 'purple' | 'amber';

export interface ThemeOption {
    id: AdminTheme;
    name: string;
    nameAr: string;
    primaryColor: string;
    lightColor: string;
}

export const THEME_OPTIONS: ThemeOption[] = [
    { id: 'green', name: 'Emerald Green', nameAr: 'أخضر زمردي', primaryColor: '#059669', lightColor: '#ecfdf5' },
    { id: 'forest', name: 'Forest Green', nameAr: 'أخضر غابي', primaryColor: '#183c2d', lightColor: '#edf5f0' },
    { id: 'slate', name: 'Classic Slate', nameAr: 'رمادي كلاسيكي', primaryColor: '#1e293b', lightColor: '#f1f5f9' },
    { id: 'blue', name: 'Ocean Blue', nameAr: 'أزرق محيطي', primaryColor: '#2563eb', lightColor: '#eff6ff' },
    { id: 'purple', name: 'Royal Purple', nameAr: 'أرجواني ملكي', primaryColor: '#7c3aed', lightColor: '#f5f3ff' },
    { id: 'amber', name: 'Warm Amber', nameAr: 'كهرماني دافئ', primaryColor: '#d97706', lightColor: '#fffbeb' },
];

const currentTheme = ref<AdminTheme>('green');

export function useAdminTheme() {
    function applyTheme(theme: AdminTheme) {
        currentTheme.value = theme;
        if (typeof document !== 'undefined') {
            document.documentElement.setAttribute('data-admin-theme', theme);
            try {
                localStorage.setItem('admin_color_theme', theme);
            } catch {
                // Ignore localStorage errors
            }
        }
    }

    function initTheme() {
        if (typeof window === 'undefined') return;
        try {
            const saved = localStorage.getItem('admin_color_theme') as AdminTheme | null;
            if (saved && THEME_OPTIONS.some((t) => t.id === saved)) {
                applyTheme(saved);
            } else {
                applyTheme('green');
            }
        } catch {
            applyTheme('green');
        }
    }

    return {
        currentTheme,
        setTheme: applyTheme,
        initTheme,
        themes: THEME_OPTIONS,
    };
}
