<script setup lang="ts">
import GuestShell from '@/Layouts/GuestShell.vue';
import ServiceCard from '@/Components/Cards/ServiceCard.vue';
import type { Service } from '@/types/content';

defineOptions({ layout: GuestShell });

defineProps<{ services: Service[] }>();
</script>

<template>
    <div class="mx-auto max-w-4xl px-6 lg:px-10 pt-24 lg:pt-28 pb-16 lg:pb-24">
        <h1 class="reveal text-3xl lg:text-4xl mb-8" style="font-family: var(--font-display); color: var(--color-primary, #1f4b5a)" v-reveal>
            {{ $t('services.title') }}
        </h1>
        <div>
            <div v-for="(service, i) in services" :key="service.id" class="reveal" v-reveal="{ delay: i * 50 }">
                <ServiceCard :service="service" />
            </div>
        </div>
        <p v-if="!services.length" class="text-sm text-slate-400 mt-10 text-center">{{ $t('services.empty') }}</p>
    </div>
</template>
