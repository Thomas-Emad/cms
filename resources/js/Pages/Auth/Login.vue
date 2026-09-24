<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import LanguageSwitcher from '@/Components/LanguageSwitcher.vue';

defineProps<{
    canResetPassword: boolean;
}>();

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>

    <Head :title="$t('common.sign_in')" />

    <div class="relative min-h-screen flex items-center justify-center bg-slate-50 px-4">
        <div class="absolute top-4 end-4">
            <LanguageSwitcher variant="admin" />
        </div>

        <div class="w-full max-w-sm rounded-xl border border-slate-200 bg-white p-8 shadow-sm">
            <h1 class="text-lg font-semibold text-slate-800 mb-1">{{ $t('auth.admin_sign_in') }}</h1>
            <p class="text-sm text-slate-500 mb-6">{{ $t('auth.platform_tagline') }}</p>

            <form @submit.prevent="submit" class="space-y-4">
                <div>
                    <label for="email" class="block text-sm font-medium text-slate-700 mb-1">{{ $t('auth.email_label')
                        }}</label>
                    <input id="email" v-model="form.email" type="email" required autofocus autocomplete="username"
                        class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:border-slate-500 focus:outline-none focus:ring-1 focus:ring-slate-500" />
                    <p v-if="form.errors.email" class="mt-1 text-xs text-red-600">{{ form.errors.email }}</p>
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium text-slate-700 mb-1">{{
                        $t('auth.password_label') }}</label>
                    <input id="password" v-model="form.password" type="password" required
                        autocomplete="current-password"
                        class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:border-slate-500 focus:outline-none focus:ring-1 focus:ring-slate-500" />
                    <p v-if="form.errors.password" class="mt-1 text-xs text-red-600">{{ form.errors.password }}</p>
                </div>

                <label class="flex items-center gap-2 text-sm text-slate-600">
                    <input v-model="form.remember" type="checkbox" class="rounded border-slate-300" />
                    {{ $t('auth.remember_me') }}
                </label>

                <!--
          Password reset intentionally omitted for Phase 1
          (see AuthenticatedSessionController docblock). Re-add here as:
          <Link v-if="canResetPassword" :href="route('password.request')">Forgot password?</Link>
        -->

                <button type="submit" :disabled="form.processing"
                    class="w-full rounded-md bg-slate-800 px-4 py-2 text-sm font-medium text-white hover:bg-slate-900 disabled:opacity-50">
                    {{ $t('common.sign_in') }}
                </button>
            </form>
        </div>
    </div>
</template>
