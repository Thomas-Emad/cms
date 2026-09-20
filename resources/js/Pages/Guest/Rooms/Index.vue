<script setup lang="ts">
import GuestLayout from '@/Layouts/GuestLayout.vue';
import RoomCard from '@/Components/Cards/RoomCard.vue';
import type { Room } from '@/types/room';

defineOptions({ layout: GuestLayout });

defineProps<{ rooms: Room[] }>();
</script>

<template>
    <div class="mx-auto max-w-7xl px-10 pt-28 pb-16">
        <h1 class="reveal text-5xl mb-10" style="font-family: var(--font-display); color: var(--color-primary, #1f4b5a)" v-reveal>Rooms &amp; Suites</h1>

        <div class="grid grid-cols-2 xl:grid-cols-3 gap-5">
            <div v-for="(room, i) in rooms" :key="room.id" class="reveal" v-reveal="{ delay: i * 70 }">
                <RoomCard :room="room" />
            </div>
        </div>

        <p v-if="!rooms.length" class="text-xl text-slate-400 mt-16 text-center">Rooms will appear here soon.</p>
    </div>
</template>
