<script setup lang="ts">
import { Link } from '@inertiajs/vue3';

withDefaults(
    defineProps<{
        title: string;
        subtitle?: string;
        backUrl?: string;
        backLabel?: string;
    }>(),
    {
        subtitle: undefined,
        backUrl: undefined,
        backLabel: undefined,
    }
);
</script>

<template>
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
        <div>
            <Link
                v-if="backUrl"
                :href="backUrl"
                class="inline-flex items-center gap-1 text-xs font-medium text-slate-500 hover:text-slate-800 transition-colors mb-1.5"
            >
                <span class="inline-block transition-transform rtl:rotate-180">←</span>
                <span>{{ backLabel ?? 'Back' }}</span>
            </Link>

            <div class="flex items-center gap-3">
                <h1 class="text-xl sm:text-2xl font-bold tracking-tight text-slate-800">
                    {{ title }}
                </h1>
                <slot name="title-extra" />
            </div>

            <p v-if="subtitle" class="mt-1 text-xs sm:text-sm text-slate-500">
                {{ subtitle }}
            </p>
        </div>

        <div v-if="$slots.actions || $slots.default" class="flex items-center gap-2 sm:gap-3 shrink-0">
            <slot name="actions" />
            <slot />
        </div>
    </div>
</template>
