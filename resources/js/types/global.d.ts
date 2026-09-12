import type { route as routeFn } from 'ziggy-js';

declare global {
    interface Window {
        route: typeof routeFn;
    }
}
