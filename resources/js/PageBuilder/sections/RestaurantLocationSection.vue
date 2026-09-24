<script setup lang="ts">
import type { RenderMode, SectionSettings } from '@/types/pageBuilder';

interface Data {
    restaurant: { location?: string | null; floor?: string | null; phone?: string | null } | null;
}

defineProps<{
    props: Record<string, never>;
    settings: SectionSettings;
    data?: Data;
    mode: RenderMode;
}>();
</script>

<template>
    <section v-if="data?.restaurant" class="mx-auto max-w-4xl px-6 lg:px-10 py-12 lg:py-16 border-t border-slate-100">
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-6 text-sm">
            <div v-if="data.restaurant.location" class="reveal" v-reveal>
                <p class="text-slate-400 uppercase tracking-wide text-xs">{{ $t('common.location') }}</p>
                <p class="mt-1 text-slate-700">{{ data.restaurant.location }}</p>
            </div>
            <div v-if="data.restaurant.floor" class="reveal" v-reveal="{ delay: 60 }">
                <p class="text-slate-400 uppercase tracking-wide text-xs">{{ $t('facilities.fields.floor') || 'Floor' }}</p>
                <p class="mt-1 text-slate-700">{{ data.restaurant.floor }}</p>
            </div>
            <div v-if="data.restaurant.phone" class="reveal" v-reveal="{ delay: 120 }">
                <p class="text-slate-400 uppercase tracking-wide text-xs">{{ $t('common.phone') }}</p>
                <p class="mt-1 text-slate-700">{{ data.restaurant.phone }}</p>
            </div>
        </div>
    </section>
</template>
