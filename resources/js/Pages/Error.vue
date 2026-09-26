<script setup lang="ts">
import { computed, ref } from 'vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import LanguageSwitcher from '@/Components/LanguageSwitcher.vue';
import { useI18n } from '@/i18n';

const props = defineProps<{
    status: number;
    message?: string | null;
}>();

const page = usePage();
const { t, locale } = useI18n();
const isLoggingOut = ref(false);

const currentUser = computed(() => {
    return (page.props.auth as any)?.user ?? (page.props as any)?.user ?? null;
});

const currentHotel = computed(() => {
    return (page.props as any)?.hotel ?? null;
});

const currentBranch = computed(() => {
    return (page.props as any)?.branch ?? null;
});

const dashboardUrl = computed(() => {
    if (!currentUser.value) return '/';
    if (currentUser.value.role === 'super_admin') {
        return '/admin/platform';
    }
    return '/admin/dashboard';
});

const errorDetails = computed(() => {
    switch (props.status) {
        case 403:
            return {
                badge: '403 · ACCESS RESTRICTED',
                badgeAr: '٤٠٣ · غير مصرح بالدخول',
                titleEn: 'Access Restricted',
                titleAr: 'غير مصرح بالدخول',
                descEn: props.message || 'You do not have the necessary permissions or role to view this page, hotel, or branch. If you are signed in with the wrong account, you can sign out below to switch accounts.',
                descAr: 'ليس لديك الصلاحيات الكافية للوصول إلى هذه الصفحة أو الفرع المحدد. إذا كنت مسجلاً بحساب غير مخصص لهذا الفرع، يمكنك تسجيل الخروج بالأسفل للتبديل لحساب آخر.',
                color: 'amber',
                icon: 'lock',
            };
        case 404:
            return {
                badge: '404 · NOT FOUND',
                badgeAr: '٤٠٤ · الصفحة غير موجودة',
                titleEn: 'Page Not Found',
                titleAr: 'الصفحة غير موجودة',
                descEn: props.message || 'The destination or resource you requested could not be located. It may have been moved, renamed, or is temporarily unavailable.',
                descAr: 'الصفحة أو الوجهة التي تبحث عنها غير متوفرة حالياً. قد تكون نُقلت إلى عنوان آخر أو حُذفت.',
                color: 'slate',
                icon: 'compass',
            };
        case 500:
            return {
                badge: '500 · SERVER ERROR',
                badgeAr: '٥٠٠ · خطأ في الخادم',
                titleEn: 'Internal System Error',
                titleAr: 'حدث خطأ في النظام',
                descEn: 'An unexpected condition was encountered on our servers. Our technical team has been alerted. Please try again in a few moments.',
                descAr: 'واجه الخادم مشكلة غير متوقعة أثناء معالجة طلبك. تم تنبيه الفريق الفني. يُرجى إعادة المحاولة بعد قليل.',
                color: 'rose',
                icon: 'server',
            };
        case 503:
            return {
                badge: '503 · MAINTENANCE',
                badgeAr: '٥٠٣ · تحت الصيانة',
                titleEn: 'Service Under Maintenance',
                titleAr: 'الخدمة قيد الصيانة',
                descEn: 'We are currently performing scheduled maintenance and updates to improve your hospitality experience. We will be back shortly.',
                descAr: 'نقوم حالياً بإجراء تحديثات وتحسينات دورية على النظام. سنعود للعمل بكامل طاقتنا في أقرب وقت.',
                color: 'blue',
                icon: 'wrench',
            };
        case 419:
            return {
                badge: '419 · SESSION EXPIRED',
                badgeAr: '٤١٩ · انتهت الجلسة',
                titleEn: 'Session Expired',
                titleAr: 'انتهت صلاحية الجلسة',
                descEn: 'Your security token or active session has timed out due to inactivity. Please refresh or sign in again.',
                descAr: 'انتهت صلاحية رمز الأمان أو الجلسة الحالية بسبب عدم التفاعل. يُرجى تحديث الصفحة أو إعادة تسجيل الدخول.',
                color: 'purple',
                icon: 'clock',
            };
        default:
            return {
                badge: `${props.status} · ERROR`,
                badgeAr: `${props.status} · خطأ`,
                titleEn: 'Unexpected Error',
                titleAr: 'حدث خطأ غير متوقع',
                descEn: props.message || 'An error occurred while processing your request.',
                descAr: 'حدث خطأ أثناء معالجة طلبك. يُرجى إعادة المحاولة.',
                color: 'slate',
                icon: 'alert',
            };
    }
});

function handleLogout() {
    isLoggingOut.value = true;
    router.post('/logout', {}, {
        onFinish: () => {
            isLoggingOut.value = false;
        },
        onError: () => {
            isLoggingOut.value = false;
            // Native fallback if Inertia post encountered issues
            const form = document.getElementById('native-logout-form') as HTMLFormElement | null;
            if (form) {
                form.submit();
            }
        },
    });
}

function goBack() {
    if (window.history.length > 1) {
        window.history.back();
    } else {
        window.location.href = '/';
    }
}
</script>

<template>
    <Head :title="`${props.status} - ${errorDetails.titleEn}`" />

    <div
        class="min-h-screen bg-slate-950 text-slate-100 flex flex-col justify-between selection:bg-amber-500/30 selection:text-amber-200 relative overflow-hidden"
        :dir="locale === 'ar' ? 'rtl' : 'ltr'"
    >
        <!-- Ambient Luxury Radial Glows -->
        <div class="pointer-events-none absolute -top-40 -start-40 h-96 w-96 rounded-full bg-emerald-600/10 blur-3xl"></div>
        <div class="pointer-events-none absolute top-1/2 -end-40 h-96 w-96 rounded-full bg-amber-500/10 blur-3xl"></div>
        <div class="pointer-events-none absolute -bottom-40 start-1/3 h-96 w-96 rounded-full bg-slate-800/20 blur-3xl"></div>

        <!-- Top Header Navigation -->
        <header class="relative z-10 w-full max-w-7xl mx-auto px-6 py-6 flex items-center justify-between">
            <Link href="/" class="flex items-center gap-3 group">
                <div class="h-10 w-10 rounded-xl bg-gradient-to-br from-amber-500/20 to-emerald-500/20 border border-amber-500/30 flex items-center justify-center text-amber-400 font-bold shadow-lg shadow-amber-950/20 group-hover:border-amber-400/50 transition-all">
                    <span class="font-serif text-lg tracking-wider">H</span>
                </div>
                <div class="flex flex-col text-start">
                    <span class="text-sm font-semibold tracking-wide text-white group-hover:text-amber-300 transition-colors">
                        {{ currentHotel?.name ?? 'Smarttel Hospitality' }}
                    </span>
                    <span v-if="currentBranch?.name" class="text-xs text-slate-400">
                        {{ currentBranch.name }}
                    </span>
                    <span v-else class="text-xs text-slate-400">
                        Luxury Hotel Experience Platform
                    </span>
                </div>
            </Link>

            <div class="flex items-center gap-3">
                <LanguageSwitcher variant="guest" />
            </div>
        </header>

        <!-- Main Error Container -->
        <main class="relative z-10 flex-1 flex items-center justify-center px-4 py-12">
            <div class="w-full max-w-xl mx-auto">
                <div class="relative rounded-2xl border border-slate-800/80 bg-slate-900/70 p-8 sm:p-10 shadow-2xl backdrop-blur-xl">
                    
                    <!-- Top Status Badge -->
                    <div class="flex items-center justify-between mb-8 pb-6 border-b border-slate-800/60 flex-wrap gap-3">
                        <div class="inline-flex items-center gap-2 rounded-full border px-3 py-1 text-xs font-semibold tracking-wider uppercase backdrop-blur-md"
                            :class="{
                                'border-amber-500/40 bg-amber-500/10 text-amber-400 shadow-sm shadow-amber-500/10': errorDetails.color === 'amber',
                                'border-slate-700 bg-slate-800/40 text-slate-300': errorDetails.color === 'slate',
                                'border-rose-500/40 bg-rose-500/10 text-rose-400': errorDetails.color === 'rose',
                                'border-blue-500/40 bg-blue-500/10 text-blue-400': errorDetails.color === 'blue',
                                'border-purple-500/40 bg-purple-500/10 text-purple-400': errorDetails.color === 'purple',
                            }"
                        >
                            <span class="h-2 w-2 rounded-full animate-pulse"
                                :class="{
                                    'bg-amber-400': errorDetails.color === 'amber',
                                    'bg-slate-400': errorDetails.color === 'slate',
                                    'bg-rose-400': errorDetails.color === 'rose',
                                    'bg-blue-400': errorDetails.color === 'blue',
                                    'bg-purple-400': errorDetails.color === 'purple',
                                }"
                            ></span>
                            <span>{{ locale === 'ar' ? errorDetails.badgeAr : errorDetails.badgeEn }}</span>
                        </div>

                        <span class="font-mono text-3xl font-extrabold tracking-tight"
                            :class="{
                                'text-amber-400/90': errorDetails.color === 'amber',
                                'text-slate-400': errorDetails.color === 'slate',
                                'text-rose-400': errorDetails.color === 'rose',
                                'text-blue-400': errorDetails.color === 'blue',
                                'text-purple-400': errorDetails.color === 'purple',
                            }"
                        >
                            {{ props.status }}
                        </span>
                    </div>

                    <!-- Icon & Titles -->
                    <div class="text-center sm:text-start space-y-3 mb-8">
                        <div class="inline-flex items-center justify-center h-14 w-14 rounded-2xl border mb-2 shadow-inner"
                            :class="{
                                'border-amber-500/30 bg-amber-500/10 text-amber-400': errorDetails.color === 'amber',
                                'border-slate-700 bg-slate-800/50 text-slate-300': errorDetails.color === 'slate',
                                'border-rose-500/30 bg-rose-500/10 text-rose-400': errorDetails.color === 'rose',
                                'border-blue-500/30 bg-blue-500/10 text-blue-400': errorDetails.color === 'blue',
                                'border-purple-500/30 bg-purple-500/10 text-purple-400': errorDetails.color === 'purple',
                            }"
                        >
                            <!-- Lock Icon for 403 -->
                            <svg v-if="errorDetails.icon === 'lock'" class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                            </svg>
                            <!-- Compass Icon for 404 -->
                            <svg v-else-if="errorDetails.icon === 'compass'" class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M12 2a10 10 0 100 20 10 10 0 000-20zm0 18a8 8 0 110-16 8 8 0 010 16zm-1.5-6.5l4-1.5-1.5-4-4 1.5 1.5 4z" />
                            </svg>
                            <!-- Server Alert for 500 -->
                            <svg v-else-if="errorDetails.icon === 'server'" class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2m-2-4h.01M17 16h.01" />
                            </svg>
                            <!-- Generic Alert -->
                            <svg v-else class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                        </div>

                        <h1 class="text-2xl sm:text-3xl font-bold text-white tracking-tight">
                            {{ locale === 'ar' ? errorDetails.titleAr : errorDetails.titleEn }}
                        </h1>
                        <p class="text-sm text-slate-400 leading-relaxed max-w-lg">
                            {{ locale === 'ar' ? errorDetails.descAr : errorDetails.descEn }}
                        </p>
                    </div>

                    <!-- Authenticated User Profile Summary Box -->
                    <div v-if="currentUser" class="mb-8 rounded-xl border border-slate-800 bg-slate-950/60 p-4">
                        <div class="flex items-center justify-between flex-wrap gap-3">
                            <div class="flex items-center gap-3">
                                <div class="h-10 w-10 rounded-full bg-slate-800 border border-slate-700 flex items-center justify-center font-bold text-amber-400 text-sm">
                                    {{ currentUser.name?.charAt(0)?.toUpperCase() ?? 'U' }}
                                </div>
                                <div class="text-start">
                                    <div class="text-sm font-semibold text-slate-200 flex items-center gap-2">
                                        <span>{{ currentUser.name }}</span>
                                        <span class="rounded px-1.5 py-0.5 text-[10px] font-medium tracking-wide uppercase border"
                                            :class="currentUser.role === 'super_admin' ? 'border-purple-500/30 bg-purple-500/10 text-purple-300' : 'border-emerald-500/30 bg-emerald-500/10 text-emerald-300'"
                                        >
                                            {{ currentUser.role === 'super_admin' ? (locale === 'ar' ? 'مدير المنصة' : 'Super Admin') : (locale === 'ar' ? 'مدير الفندق' : 'Hotel Admin') }}
                                        </span>
                                    </div>
                                    <div class="text-xs text-slate-400 font-mono">
                                        {{ currentUser.email }}
                                    </div>
                                </div>
                            </div>

                            <!-- Logout button inside user card -->
                            <button
                                type="button"
                                @click="handleLogout"
                                :disabled="isLoggingOut"
                                class="inline-flex items-center gap-1.5 rounded-lg border border-rose-500/30 bg-rose-500/10 px-3 py-1.5 text-xs font-semibold text-rose-300 hover:bg-rose-500/20 hover:border-rose-500/50 transition-all active:scale-95 disabled:opacity-50"
                            >
                                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                                </svg>
                                <span>{{ isLoggingOut ? (locale === 'ar' ? 'جارٍ الخروج...' : 'Signing out...') : (locale === 'ar' ? 'تسجيل الخروج' : 'Log Out') }}</span>
                            </button>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="space-y-3">
                        <div class="flex flex-col sm:flex-row items-center gap-3">
                            <!-- Primary Button: For 403 when authenticated, prominent Log Out -->
                            <button
                                v-if="props.status === 403 && currentUser"
                                type="button"
                                @click="handleLogout"
                                :disabled="isLoggingOut"
                                class="w-full sm:flex-1 inline-flex items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-rose-600 to-rose-700 hover:from-rose-500 hover:to-rose-600 text-white px-5 py-3 text-sm font-semibold shadow-lg shadow-rose-950/40 transition-all active:scale-98 disabled:opacity-50"
                            >
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                                </svg>
                                <span>{{ isLoggingOut ? (locale === 'ar' ? 'جارٍ تسجيل الخروج...' : 'Signing Out...') : (locale === 'ar' ? 'تسجيل الخروج والتبديل لحساب آخر' : 'Sign Out & Switch Account') }}</span>
                            </button>

                            <!-- Dashboard Link if authenticated -->
                            <Link
                                v-if="currentUser"
                                :href="dashboardUrl"
                                class="w-full sm:flex-1 inline-flex items-center justify-center gap-2 rounded-xl border border-slate-700 bg-slate-800/80 hover:bg-slate-700 text-slate-100 px-5 py-3 text-sm font-semibold transition-all active:scale-98"
                            >
                                <svg class="h-4 w-4 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                                </svg>
                                <span>{{ locale === 'ar' ? 'الذهاب إلى لوحة التحكم' : 'Go to Dashboard' }}</span>
                            </Link>

                            <!-- Return Home (if not authenticated or general error) -->
                            <Link
                                v-if="!currentUser || props.status !== 403"
                                href="/"
                                class="w-full sm:flex-1 inline-flex items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-amber-600 to-amber-700 hover:from-amber-500 hover:to-amber-600 text-white px-5 py-3 text-sm font-semibold shadow-lg shadow-amber-950/40 transition-all active:scale-98"
                            >
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                                </svg>
                                <span>{{ locale === 'ar' ? 'العودة للصفحة الرئيسية' : 'Return to Home' }}</span>
                            </Link>
                        </div>

                        <!-- Secondary Actions: Go Back & Login if not authenticated -->
                        <div class="flex items-center justify-between pt-3 border-t border-slate-800/60 text-xs">
                            <button
                                type="button"
                                @click="goBack"
                                class="text-slate-400 hover:text-slate-200 transition-colors inline-flex items-center gap-1.5"
                            >
                                <svg class="h-3.5 w-3.5 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                                </svg>
                                <span>{{ locale === 'ar' ? 'الرجوع للصفحة السابقة' : 'Go back to previous page' }}</span>
                            </button>

                            <Link
                                v-if="!currentUser"
                                href="/login"
                                class="text-amber-400 hover:text-amber-300 font-medium underline underline-offset-4"
                            >
                                {{ locale === 'ar' ? 'تسجيل الدخول للموظفين' : 'Staff Sign In →' }}
                            </Link>
                            <span v-else class="text-slate-500">
                                {{ locale === 'ar' ? 'خدمة الفنادق والضيافة الذكية' : 'Hospitality Operations Suite' }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </main>

        <!-- Hidden Native Fallback Logout Form -->
        <form id="native-logout-form" method="POST" action="/logout" class="hidden">
            <input type="hidden" name="_token" :value="(page.props as any).csrf_token || ''" />
        </form>

        <!-- Footer -->
        <footer class="relative z-10 w-full max-w-7xl mx-auto px-6 py-6 text-center text-xs text-slate-500">
            <p>© {{ new Date().getFullYear() }} {{ currentHotel?.name ?? 'Smarttel Hospitality Systems' }}. All rights reserved.</p>
        </footer>
    </div>
</template>
