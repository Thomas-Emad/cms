<script setup lang="ts">
import type { RenderMode, SectionSettings } from '@/types/pageBuilder';

interface Data {
    restaurant: {
        name: string;
        description?: string | null;
        cuisine?: string | null;
        dress_code?: string | null;
        phone?: string | null;
        opening_hours?: Record<string, string> | null;
    } | null;
}

defineProps<{
    props: { show_opening_hours?: boolean };
    settings: SectionSettings;
    data?: Data;
    mode: RenderMode;
}>();
</script>

<template>
    <section v-if="data?.restaurant" class="mx-auto max-w-7xl px-6 lg:px-10 py-16 lg:py-24">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12">
            <div class="lg:col-span-7">
                <p v-if="data.restaurant.cuisine" class="reveal text-xs uppercase tracking-[0.2em] text-slate-400" v-reveal>
                    {{ data.restaurant.cuisine }}
                </p>
                <p
                    v-if="data.restaurant.description"
                    class="reveal mt-4 text-xl lg:text-2xl leading-relaxed text-slate-700"
                    style="font-family: var(--font-display)"
                    v-reveal="{ delay: 80 }"
                >
                    {{ data.restaurant.description }}
                </p>
            </div>

            <div class="reveal lg:col-span-4 lg:col-start-9 space-y-5 text-sm" v-reveal="{ delay: 160 }">
                <div v-if="data.restaurant.dress_code">
                    <p class="text-slate-400 uppercase tracking-wide text-xs">Dress code</p>
                    <p class="mt-1 text-slate-700">{{ data.restaurant.dress_code }}</p>
                </div>
                <div v-if="data.restaurant.phone">
                    <p class="text-slate-400 uppercase tracking-wide text-xs">Phone</p>
                    <p class="mt-1 text-slate-700">{{ data.restaurant.phone }}</p>
                </div>
                <div v-if="props.show_opening_hours !== false && data.restaurant.opening_hours">
                    <p class="text-slate-400 uppercase tracking-wide text-xs">Opening hours</p>
                    <p v-for="(hours, label) in data.restaurant.opening_hours" :key="label" class="mt-1 text-slate-700 flex justify-between gap-4 max-w-xs">
                        <span class="text-slate-500">{{ label }}</span> <span>{{ hours }}</span>
                    </p>
                </div>
            </div>
        </div>
    </section>
</template>
