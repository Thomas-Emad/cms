<script setup lang="ts">
import { ref, watch } from 'vue';
import type { RenderMode, SectionSettings } from '@/types/pageBuilder';

interface MenuItem {
    name: string;
    description?: string | null;
    price: string;
    dietary_info?: string[] | null;
}
interface MenuCategory {
    name: string;
    items: MenuItem[];
}
interface Data {
    menu: { categories: MenuCategory[] } | null;
}

const props = defineProps<{
    props: { title?: string };
    settings: SectionSettings;
    data?: Data;
    mode: RenderMode;
}>();

const activeIndex = ref(0);
watch(
    () => props.data?.menu,
    () => (activeIndex.value = 0),
);
</script>

<template>
    <section class="mx-auto max-w-4xl px-6 lg:px-10 py-16 lg:py-24">
        <h2
            v-if="props.title"
            class="reveal text-2xl lg:text-3xl mb-8 lg:mb-10 text-center"
            style="font-family: var(--font-display); color: var(--color-primary, #1f4b5a)"
            v-reveal
        >
            {{ props.title }}
        </h2>

        <div v-if="data?.menu">
            <!-- Category tabs — replaces a plain stacked "Category / Item Item Item"
           list with a switcher, so long menus don't read as a wall of text. -->
            <div class="flex flex-wrap justify-center gap-2 mb-10 border-b border-slate-100 pb-4">
                <button
                    v-for="(category, i) in data.menu.categories"
                    :key="category.name"
                    type="button"
                    class="px-4 py-1.5 text-xs uppercase tracking-wide rounded-full transition-colors"
                    :class="
                        i === activeIndex
                            ? 'text-white'
                            : 'text-slate-500 hover:text-slate-700'
                    "
                    :style="i === activeIndex ? { background: 'var(--color-primary, #1f4b5a)' } : {}"
                    @click="activeIndex = i"
                >
                    {{ category.name }}
                </button>
            </div>

            <Transition mode="out-in" enter-active-class="transition duration-300 ease-out" enter-from-class="opacity-0" enter-to-class="opacity-100">
                <div :key="activeIndex" class="space-y-5">
                    <div
                        v-for="item in data.menu.categories[activeIndex]?.items ?? []"
                        :key="item.name"
                        class="flex items-baseline gap-4"
                    >
                        <div class="min-w-0">
                            <p style="font-family: var(--font-display)" class="text-lg text-slate-800">{{ item.name }}</p>
                            <p v-if="item.description" class="text-sm text-slate-500 mt-0.5">{{ item.description }}</p>
                            <p v-if="item.dietary_info?.length" class="text-xs text-slate-400 mt-0.5 uppercase tracking-wide">
                                {{ item.dietary_info.join(' · ') }}
                            </p>
                        </div>
                        <div class="flex-1 border-b border-dotted border-slate-200 self-center translate-y-[-2px]" />
                        <span class="shrink-0" style="color: var(--color-primary, #1f4b5a)">${{ item.price }}</span>
                    </div>
                </div>
            </Transition>

            <!-- Edit-mode fallback: show every category stacked so an admin
           editing the Builder can scan the whole menu without clicking
           through tabs. -->
            <div v-if="mode === 'edit'" class="mt-10 space-y-8 border-t border-slate-100 pt-8">
                <p class="text-xs text-slate-400">Full menu (edit view — guests see the tabbed version above):</p>
                <div v-for="category in data.menu.categories" :key="category.name">
                    <h3 class="text-sm font-semibold uppercase tracking-wide text-slate-400 mb-2">{{ category.name }}</h3>
                    <ul class="divide-y divide-slate-100">
                        <li v-for="item in category.items" :key="item.name" class="py-2 flex justify-between gap-4">
                            <p class="text-slate-700">{{ item.name }}</p>
                            <span class="shrink-0 text-slate-700">${{ item.price }}</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
        <p v-else class="text-xs text-slate-400 text-center">No active menu for this restaurant yet.</p>
    </section>
</template>
