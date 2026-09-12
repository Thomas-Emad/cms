<script setup lang="ts">
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Link, useForm } from '@inertiajs/vue3';
import { watch } from 'vue';

defineOptions({ layout: AdminLayout });

const form = useForm({
  name: '',
  slug: '',
  is_home: false,
});

// Light auto-slugify convenience, not a hard requirement - the admin can
// still edit the slug manually afterward. Only auto-fills while the slug
// field hasn't been touched by hand (tracked via slugTouched below), so
// it never fights the admin's own edits.
let slugTouched = false;
function onSlugInput() {
  slugTouched = true;
}
watch(() => form.name, (name) => {
  if (slugTouched || form.is_home) return;
  form.slug = name
    .toLowerCase()
    .trim()
    .replace(/[^a-z0-9]+/g, '-')
    .replace(/(^-|-$)/g, '');
});

function submit() {
  form.post('/admin/pages');
  // On success, the server redirects straight to the Builder - see
  // AdminPageController::store(). No client-side redirect needed here.
}
</script>

<template>
  <div class="max-w-lg">
    <h1 class="text-xl font-semibold text-slate-800 mb-1">Create Page</h1>
    <p class="text-sm text-slate-500 mb-6">
      Give your page a name and a URL slug. You'll add content in the Builder next.
    </p>

    <form @submit.prevent="submit" class="space-y-4 rounded-lg border border-slate-200 bg-white p-6">
      <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">Page Name</label>
        <input
          v-model="form.name"
          type="text"
          placeholder="e.g. About Us"
          autofocus
          class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm"
        />
        <p v-if="form.errors.name" class="mt-1 text-xs text-red-600">{{ form.errors.name }}</p>
      </div>

      <div v-if="!form.is_home">
        <label class="block text-sm font-medium text-slate-700 mb-1">Slug</label>
        <div class="flex items-center rounded-md border border-slate-300 overflow-hidden">
          <span class="px-3 py-2 text-sm text-slate-400 bg-slate-50 border-r border-slate-300">/pages/</span>
          <input
            v-model="form.slug"
            type="text"
            placeholder="about-us"
            class="flex-1 px-3 py-2 text-sm outline-none"
            @input="onSlugInput"
          />
        </div>
        <p v-if="form.errors.slug" class="mt-1 text-xs text-red-600">{{ form.errors.slug }}</p>
      </div>

      <label class="flex items-center gap-2 text-sm text-slate-600">
        <input v-model="form.is_home" type="checkbox" class="rounded border-slate-300" />
        Set as home page
        <span class="text-xs text-slate-400">(replaces the current homepage)</span>
      </label>

      <div class="flex justify-end gap-2 pt-2">
        <Link
          href="/admin/pages"
          class="rounded-md border border-slate-300 px-4 py-2 text-sm text-slate-600 hover:bg-slate-50"
        >
          Cancel
        </Link>
        <button
          type="submit"
          :disabled="form.processing"
          class="rounded-md bg-slate-800 px-4 py-2 text-sm font-medium text-white hover:bg-slate-900 disabled:opacity-50"
        >
          {{ form.processing ? 'Creating…' : 'Create Page' }}
        </button>
      </div>
    </form>
  </div>
</template>
