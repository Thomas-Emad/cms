<script setup lang="ts">
import type { Service } from '@/types/content';

defineProps<{
    service: Service;
}>();
</script>

<template>
    <div class="flex items-center justify-between gap-4 border-b border-slate-100 py-5">
        <div class="min-w-0">
            <h3 class="text-lg" style="font-family: var(--font-display); color: var(--color-primary, #1f4b5a)">
                {{ service.name }}
            </h3>
            <p v-if="service.description" class="mt-0.5 text-sm text-slate-500">{{ service.description }}</p>
        </div>
        <button
            v-if="service.request_enabled"
            type="button"
            class="shrink-0 rounded-full px-4 py-2 text-xs uppercase tracking-wide text-white transition-opacity hover:opacity-90"
            style="background: var(--color-primary, #1f4b5a)"
        >
            {{ $t('services.request') }}
        </button>
        <span v-else-if="service.price" class="shrink-0 text-sm text-slate-600">${{ service.price }}</span>
    </div>
</template>
