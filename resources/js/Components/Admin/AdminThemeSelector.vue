<script setup lang="ts">
import { ref, onMounted, onUnmounted } from 'vue';
import { useAdminTheme, type AdminTheme } from '@/composables/useAdminTheme';
import { useI18n } from '@/i18n';

withDefaults(
    defineProps<{
        variant?: 'compact' | 'expanded';
    }>(),
    {
        variant: 'compact',
    }
);

const { currentTheme, setTheme, initTheme, themes } = useAdminTheme();
const { locale } = useI18n();

const isOpen = ref(false);
const containerRef = ref<HTMLElement | null>(null);

function selectTheme(themeId: AdminTheme) {
    setTheme(themeId);
    isOpen.value = false;
}

function handleClickOutside(event: MouseEvent) {
    if (containerRef.value && !containerRef.value.contains(event.target as Node)) {
        isOpen.value = false;
    }
}

onMounted(() => {
    initTheme();
    document.addEventListener('click', handleClickOutside);
});

onUnmounted(() => {
    document.removeEventListener('click', handleClickOutside);
});
</script>

<template>
    <!-- Compact Header Dropdown Variant -->
    <div
        v-if="variant === 'compact'"
        ref="containerRef"
        class="relative inline-block text-start"
    >
        <button
            type="button"
            class="flex items-center gap-1.5 rounded-md px-2.5 py-1.5 text-xs font-medium text-slate-700 hover:bg-slate-100 transition-colors border border-slate-200"
            :title="locale === 'ar' ? 'تغيير لون المظهر' : 'Change Admin Color Theme'"
            @click.stop="isOpen = !isOpen"
        >
            <span
                class="h-3 w-3 rounded-full border border-black/10 shrink-0 shadow-2xs"
                :style="{ backgroundColor: themes.find(t => t.id === currentTheme)?.primaryColor }"
            />
            <span class="hidden sm:inline">
                {{ locale === 'ar'
                    ? themes.find(t => t.id === currentTheme)?.nameAr
                    : themes.find(t => t.id === currentTheme)?.name }}
            </span>
            <svg
                class="h-3 w-3 text-slate-400 transition-transform"
                :class="{ 'rotate-180': isOpen }"
                viewBox="0 0 20 20"
                fill="currentColor"
            >
                <path
                    fill-rule="evenodd"
                    d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z"
                    clip-rule="evenodd"
                />
            </svg>
        </button>

        <Transition
            enter-active-class="transition duration-100 ease-out"
            enter-from-class="transform scale-95 opacity-0"
            enter-to-class="transform scale-100 opacity-100"
            leave-active-class="transition duration-75 ease-in"
            leave-from-class="transform scale-100 opacity-100"
            leave-to-class="transform scale-95 opacity-0"
        >
            <div
                v-if="isOpen"
                class="absolute end-0 mt-1.5 w-44 rounded-lg bg-white p-1.5 shadow-lg border border-slate-200 z-50 focus:outline-none"
            >
                <div class="px-2 py-1 text-[11px] font-semibold uppercase tracking-wider text-slate-400">
                    {{ locale === 'ar' ? 'ألوان المظهر' : 'Color Theme' }}
                </div>
                <div class="space-y-0.5">
                    <button
                        v-for="theme in themes"
                        :key="theme.id"
                        type="button"
                        class="w-full flex items-center justify-between rounded-md px-2 py-1.5 text-xs text-start transition-colors"
                        :class="currentTheme === theme.id ? 'bg-slate-100 text-slate-900 font-semibold' : 'text-slate-600 hover:bg-slate-50'"
                        @click="selectTheme(theme.id)"
                    >
                        <span class="flex items-center gap-2">
                            <span
                                class="h-3.5 w-3.5 rounded-full border border-black/10 shrink-0"
                                :style="{ backgroundColor: theme.primaryColor }"
                            />
                            <span>{{ locale === 'ar' ? theme.nameAr : theme.name }}</span>
                        </span>
                        <svg
                            v-if="currentTheme === theme.id"
                            class="h-3.5 w-3.5 text-emerald-600"
                            viewBox="0 0 20 20"
                            fill="currentColor"
                        >
                            <path
                                fill-rule="evenodd"
                                d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                clip-rule="evenodd"
                            />
                        </svg>
                    </button>
                </div>
            </div>
        </Transition>
    </div>

    <!-- Expanded Cards Variant (e.g. for Settings page) -->
    <div v-else class="grid grid-cols-2 sm:grid-cols-3 gap-3">
        <button
            v-for="theme in themes"
            :key="theme.id"
            type="button"
            class="flex items-center gap-3 p-3.5 rounded-lg border text-start transition-all"
            :class="[
                currentTheme === theme.id
                    ? 'border-slate-800 bg-slate-50/80 shadow-xs ring-2 ring-slate-800/10'
                    : 'border-slate-200 hover:border-slate-300 hover:bg-slate-50/50'
            ]"
            @click="selectTheme(theme.id)"
        >
            <span
                class="h-7 w-7 rounded-full border border-black/10 shrink-0 flex items-center justify-center text-white shadow-xs"
                :style="{ backgroundColor: theme.primaryColor }"
            >
                <svg
                    v-if="currentTheme === theme.id"
                    class="h-4 w-4"
                    viewBox="0 0 20 20"
                    fill="currentColor"
                >
                    <path
                        fill-rule="evenodd"
                        d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                        clip-rule="evenodd"
                    />
                </svg>
            </span>
            <div>
                <span class="block text-sm font-semibold text-slate-800">
                    {{ locale === 'ar' ? theme.nameAr : theme.name }}
                </span>
                <span class="block text-xs text-slate-400 font-mono">
                    {{ theme.primaryColor }}
                </span>
            </div>
        </button>
    </div>
</template>
