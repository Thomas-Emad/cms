import { t } from '@/i18n';

declare module 'vue' {
    interface ComponentCustomProperties {
        $t: typeof t;
    }
}

export { };
