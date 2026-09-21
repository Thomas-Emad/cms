<script setup lang="ts">
import GuestShell from '@/Layouts/GuestShell.vue';

defineOptions({ layout: GuestShell });

defineProps<{
    kind: 'timing' | 'short_call';
    title: string;
    subtitle: string;
    groups: { name: string | null; items: { label: string; value: string }[] }[];
}>();
</script>

<template>
    <div class="mx-auto max-w-5xl px-10 pt-28 pb-16">
        <h1 class="reveal text-5xl" style="font-family: var(--font-display); color: var(--color-primary, #1f4b5a)" v-reveal>{{ title }}</h1>
        <p class="reveal mt-3 text-2xl text-slate-500" v-reveal="{ delay: 80 }">{{ subtitle }}</p>

        <div class="mt-10 space-y-12">
            <section v-for="(group, gi) in groups" :key="gi">
                <h2 v-if="group.name" class="mb-2 text-sm uppercase tracking-[0.2em]" style="color: var(--luxury-champagne)">{{ group.name }}</h2>

                <div v-for="(item, i) in group.items" :key="i" class="reveal flex items-center gap-4 py-5 border-b border-slate-100" v-reveal="{ delay: Math.min(i, 8) * 40 }">
                    <span class="text-3xl text-slate-800">{{ item.label }}</span>
                    <span class="flex-1 border-b border-dotted border-slate-300" aria-hidden="true" />

                    <!-- Short calls: what to dial, shown as a chip so the number is the first thing the eye lands on -->
                    <span v-if="kind === 'short_call'" class="rounded-full px-8 py-2 text-4xl font-medium tabular-nums text-slate-900" style="background: var(--luxury-champagne)">
                        {{ item.value }}
                    </span>
                    <span v-else class="text-3xl tabular-nums text-slate-600 text-right">{{ item.value }}</span>
                </div>
            </section>
        </div>

        <p v-if="!groups.length" class="text-xl text-slate-400 mt-16 text-center">Nothing to show yet.</p>
    </div>
</template>
