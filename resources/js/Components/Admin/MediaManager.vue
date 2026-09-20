<script setup lang="ts">
import { computed, ref } from 'vue';
import axios from 'axios';
import type { MediaItem } from '@/types/room';

/**
 * Upload / remove / reorder images for one owner + collection.
 * Talks to /admin/media directly (JSON), so it works inside a normal Inertia
 * form page without triggering a page visit for each photo.
 *
 * 'cover' behaves as a single image: uploading replaces the current one.
 */
const props = defineProps<{
    mediableType: 'facility' | 'room' | 'hotel';
    mediableId: number;
    collection: 'cover' | 'gallery';
    items: MediaItem[];
    label: string;
    hint?: string;
}>();

const list = ref<MediaItem[]>([...props.items]);
const busy = ref(false);
const percent = ref(0);
const error = ref<string | null>(null);
const isCover = computed(() => props.collection === 'cover');

function messageFrom(e: unknown): string {
    const err = e as { response?: { data?: { message?: string; errors?: Record<string, string[]> } } };
    const first = Object.values(err.response?.data?.errors ?? {})[0]?.[0];
    return first ?? err.response?.data?.message ?? 'Something went wrong. Please try again.';
}

async function onPick(event: Event) {
    const input = event.target as HTMLInputElement;
    const files = Array.from(input.files ?? []);
    if (!files.length) return;

    const body = new FormData();
    body.append('mediable_type', props.mediableType);
    body.append('mediable_id', String(props.mediableId));
    body.append('collection', props.collection);
    files.forEach((f) => body.append('files[]', f));

    busy.value = true;
    error.value = null;
    try {
        percent.value = 0;
        const { data } = await axios.post<{ items: MediaItem[] }>('/admin/media', body, {
            // Videos can take a while; show progress instead of a frozen button.
            onUploadProgress: (e) => (percent.value = e.total ? Math.round((e.loaded / e.total) * 100) : 0),
        });
        list.value = isCover.value ? data.items : [...list.value, ...data.items];
    } catch (e) {
        error.value = messageFrom(e);
    } finally {
        busy.value = false;
        input.value = ''; // allow picking the same file again
    }
}

async function remove(item: MediaItem) {
    if (!confirm('Remove this photo?')) return;
    error.value = null;
    try {
        await axios.delete(`/admin/media/${item.id}`);
        list.value = list.value.filter((m) => m.id !== item.id);
    } catch (e) {
        error.value = messageFrom(e);
    }
}

async function move(index: number, direction: -1 | 1) {
    const target = index + direction;
    if (target < 0 || target >= list.value.length) return;

    const previous = [...list.value];
    const next = [...list.value];
    [next[index], next[target]] = [next[target], next[index]];
    list.value = next; // optimistic

    try {
        await axios.put('/admin/media/reorder', { ids: next.map((m) => m.id) });
    } catch (e) {
        list.value = previous;
        error.value = messageFrom(e);
    }
}
</script>

<template>
    <div>
        <div class="flex items-center justify-between mb-2">
            <div>
                <p class="text-sm font-medium text-slate-700">{{ label }}</p>
                <p v-if="hint" class="text-xs text-slate-400">{{ hint }}</p>
            </div>
            <label
                class="cursor-pointer rounded-md border border-slate-300 bg-white px-3 py-1.5 text-sm text-slate-700 hover:bg-slate-50"
                :class="{ 'opacity-50 pointer-events-none': busy }"
            >
                {{ busy ? `Uploading... ${percent}%` : isCover ? (list.length ? 'Replace photo' : 'Choose photo') : 'Add photos or videos' }}
                <input
                    type="file"
                    :accept="isCover ? 'image/jpeg,image/png,image/webp' : 'image/jpeg,image/png,image/webp,video/mp4,video/webm'"
                    class="hidden"
                    :multiple="!isCover"
                    data-testid="file-input"
                    @change="onPick"
                />
            </label>
        </div>

        <p v-if="error" class="mb-2 text-xs text-red-600" data-testid="error">{{ error }}</p>

        <ul v-if="list.length" class="grid grid-cols-3 sm:grid-cols-4 gap-3">
            <li v-for="(item, i) in list" :key="item.id" class="relative group" data-testid="item">
                <video v-if="item.type === 'video'" :src="item.url" muted preload="metadata" class="aspect-square w-full rounded-md object-cover border border-slate-200" />
                <img v-else :src="item.url" :alt="item.alt_text ?? ''" class="aspect-square w-full rounded-md object-cover border border-slate-200" />
                <span v-if="item.type === 'video'" class="absolute left-1 top-1 rounded bg-black/60 px-1.5 text-xs text-white">▶ video</span>
                <button
                    type="button"
                    class="absolute top-1 right-1 h-6 w-6 rounded-full bg-black/60 text-white text-xs leading-6 text-center hover:bg-red-600"
                    aria-label="Remove photo"
                    data-testid="remove"
                    @click="remove(item)"
                >
                    ×
                </button>
                <div v-if="!isCover && list.length > 1" class="absolute bottom-1 left-1 flex gap-1">
                    <button type="button" class="h-6 w-6 rounded bg-black/60 text-white text-xs" aria-label="Move earlier" :disabled="i === 0" data-testid="up" @click="move(i, -1)">←</button>
                    <button type="button" class="h-6 w-6 rounded bg-black/60 text-white text-xs" aria-label="Move later" :disabled="i === list.length - 1" data-testid="down" @click="move(i, 1)">→</button>
                </div>
            </li>
        </ul>
        <p v-else class="rounded-md border border-dashed border-slate-300 p-4 text-center text-xs text-slate-400">No photos yet.</p>
    </div>
</template>
