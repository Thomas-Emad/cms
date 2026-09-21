<script setup lang="ts">
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { useForm } from '@inertiajs/vue3';

defineOptions({ layout: AdminLayout });

const props = defineProps<{
    guestView: 'classic' | 'tv';
}>();

const form = useForm({
    guest_view: props.guestView,
});

function save() {
    form.patch('/admin/settings/guest-view');
}
</script>

<template>
    <div class="max-w-2xl">
        <h1 class="text-xl font-semibold text-slate-800 mb-1">Settings</h1>
        <p class="text-sm text-slate-500 mb-6">Site-wide guest view.</p>

        <div class="rounded-lg border border-slate-200 bg-white p-6">
            <h2 class="text-sm font-semibold text-slate-800 mb-1">Guest View</h2>
            <p class="text-sm text-slate-500 mb-4">
                Choose the overall layout guests see across the whole site. This applies to every guest page, not
                just one - it can be combined with per-page "Full screen" layout in the Page Builder for
                TV-home-screen-style pages.
            </p>

            <div class="grid grid-cols-2 gap-3">
                <label
                    class="cursor-pointer rounded-lg border p-4"
                    :class="form.guest_view === 'classic' ? 'border-slate-800 bg-slate-50' : 'border-slate-300'"
                >
                    <input v-model="form.guest_view" type="radio" value="classic" class="sr-only" />
                    <span class="block font-medium text-slate-800 mb-1">Old Layout</span>
                    <span class="block text-xs text-slate-500">
                        The current hospitality-screen shell: a top bar with the hotel name/clock and a bottom dock
                        of navigation buttons.
                    </span>
                </label>

                <label
                    class="cursor-pointer rounded-lg border p-4"
                    :class="form.guest_view === 'tv' ? 'border-slate-800 bg-slate-50' : 'border-slate-300'"
                >
                    <input v-model="form.guest_view" type="radio" value="tv" class="sr-only" />
                    <span class="block font-medium text-slate-800 mb-1">TV View</span>
                    <span class="block text-xs text-slate-500">
                        Samsung-Smart-TV-style shell: no persistent top bar or dock - pages (like an App Launcher
                        home screen) drive their own navigation.
                    </span>
                </label>
            </div>

            <div class="mt-5 flex items-center gap-3">
                <button
                    type="button"
                    :disabled="form.processing"
                    class="rounded-md bg-slate-800 px-4 py-2 text-sm font-medium text-white hover:bg-slate-900 disabled:opacity-50"
                    @click="save"
                >
                    {{ form.processing ? 'Saving…' : 'Save' }}
                </button>
                <span v-if="form.recentlySuccessful" class="text-sm text-emerald-600">Saved.</span>
            </div>
        </div>
    </div>
</template>
