<script setup lang="ts">
import ServiceCard from '@/Components/Cards/ServiceCard.vue';
import type { RenderMode, SectionSettings } from '@/types/pageBuilder';
import type { Service } from '@/types/content';

interface ServiceGridProps {
    title?: string;
    limit?: number;
}
interface ServiceGridData {
    services: Service[];
}

defineProps<{
    props: ServiceGridProps;
    settings: SectionSettings;
    data?: ServiceGridData;
    mode: RenderMode;
}>();
</script>

<template>
    <section class="mx-auto max-w-4xl px-6 lg:px-10 py-12 lg:py-16">
        <h2
            v-if="props.title"
            class="reveal text-2xl lg:text-3xl mb-6"
            style="font-family: var(--font-display); color: var(--color-primary, #1f4b5a)"
            v-reveal
        >
            {{ props.title }}
        </h2>

        <div>
            <div v-for="(service, i) in data?.services ?? []" :key="service.id" class="reveal" v-reveal="{ delay: i * 50 }">
                <ServiceCard :service="service" />
            </div>
        </div>

        <p v-if="mode === 'edit' && !data?.services?.length" class="text-xs text-slate-400 mt-2">
            No services published yet.
        </p>
    </section>
</template>
