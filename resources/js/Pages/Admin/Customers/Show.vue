<script setup lang="ts">
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { useForm, router, Link } from '@inertiajs/vue3';
import { ref } from 'vue';
import { AdminBadge, AdminButton, AdminCard, AdminInput, AdminSelect, EditButton, PageHeader } from '@/Components/Admin';

defineOptions({ layout: AdminLayout });

interface CustomerDetails {
  id: number;
  name: string;
  slug: string;
  domain: string | null;
  status: 'active' | 'suspended' | 'draft';
  contact_email: string | null;
  contact_phone: string | null;
  address: string | null;
  timezone: string;
  currency: string;
  created_at: string | null;
  updated_at: string | null;
  settings: {
    checkin_time: string | null;
    checkout_time: string | null;
    default_locale: string;
    guest_view: string | null;
  } | null;
  primary_admin: {
    id: number;
    name: string;
    email: string;
    status: string;
    created_at: string | null;
  } | null;
  users: {
    id: number;
    name: string;
    email: string;
    role: string;
    status: string;
    created_at: string | null;
  }[];
  branches?: {
    id: number;
    name: string;
    slug: string;
    domain: string | null;
    city: string | null;
    phone: string | null;
    status: string;
    is_main: boolean;
  }[];
  content_counts: {
    facilities: number;
    rooms: number;
    restaurants: number;
    pages: number;
  };
}

const props = defineProps<{
  customer: CustomerDetails;
}>();

// Domain modal & form
const showDomainModal = ref(false);
const domainForm = useForm({
  domain: props.customer.domain ?? '',
});

function submitDomain() {
  domainForm.patch(`/admin/customers/${props.customer.id}/domain`, {
    onSuccess: () => {
      showDomainModal.value = false;
    },
  });
}

// User modal & form
const showUserModal = ref(false);
const userForm = useForm({
  name: '',
  email: '',
  password: '',
  role: 'hotel_admin',
});

function submitUser() {
  userForm.post(`/admin/customers/${props.customer.id}/users`, {
    onSuccess: () => {
      showUserModal.value = false;
      userForm.reset();
    },
  });
}

function toggleStatus() {
  const newStatus = props.customer.status === 'suspended' ? 'active' : 'suspended';
  const confirmMsg =
    newStatus === 'suspended'
      ? `Are you sure you want to suspend "${props.customer.name}"? Tenant access will be blocked immediately.`
      : `Reactivate "${props.customer.name}"?`;

  if (confirm(confirmMsg)) {
    router.patch(`/admin/customers/${props.customer.id}/status`, { status: newStatus }, { preserveScroll: true });
  }
}
</script>

<template>
  <div class="space-y-6">
    <PageHeader
      :title="customer.name"
      :subtitle="`Hotel Customer details, domain configuration, and administration.`"
    >
      <template #actions>
        <div class="flex items-center gap-2">
          <button
            type="button"
            class="px-3 py-1.5 rounded-lg text-xs font-semibold shadow-2xs transition-colors"
            :class="customer.status === 'suspended'
              ? 'bg-emerald-600 text-white hover:bg-emerald-700'
              : 'bg-rose-600 text-white hover:bg-rose-700'"
            @click="toggleStatus"
          >
            {{ customer.status === 'suspended' ? 'Activate Hotel' : 'Suspend Hotel' }}
          </button>

          <AdminButton variant="secondary" size="sm" @click="showDomainModal = true">
            Change Domain
          </AdminButton>

          <EditButton :href="`/admin/customers/${customer.id}/edit`" />

          <AdminButton variant="secondary" size="sm" href="/admin/customers">
            Back to List
          </AdminButton>
        </div>
      </template>
    </PageHeader>

    <!-- Top Overview Cards -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
      <div class="bg-white p-4 rounded-xl border border-slate-200/80 shadow-2xs">
        <div class="text-xs font-medium text-slate-500 uppercase tracking-wider">Tenant Status</div>
        <div class="mt-2 flex items-center gap-2">
          <AdminBadge
            :variant="customer.status === 'active' ? 'published' : customer.status === 'suspended' ? 'archived' : 'draft'"
          >
            {{ customer.status }}
          </AdminBadge>
        </div>
      </div>

      <div class="bg-white p-4 rounded-xl border border-slate-200/80 shadow-2xs">
        <div class="text-xs font-medium text-slate-500 uppercase tracking-wider">Total Users</div>
        <div class="mt-1 text-2xl font-bold text-slate-800">{{ customer.users.length }}</div>
      </div>

      <div class="bg-white p-4 rounded-xl border border-slate-200/80 shadow-2xs">
        <div class="text-xs font-medium text-slate-500 uppercase tracking-wider">Facilities & Rooms</div>
        <div class="mt-1 text-2xl font-bold text-emerald-600">
          {{ customer.content_counts.facilities + customer.content_counts.rooms }}
        </div>
      </div>

      <div class="bg-white p-4 rounded-xl border border-slate-200/80 shadow-2xs">
        <div class="text-xs font-medium text-slate-500 uppercase tracking-wider">Published Pages</div>
        <div class="mt-1 text-2xl font-bold text-slate-800">
          {{ customer.content_counts.pages }}
        </div>
      </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <!-- Left 2 Cols: Hotel Information & Users -->
      <div class="lg:col-span-2 space-y-6">
        <!-- Hotel Information -->
        <AdminCard title="Hotel Information" subtitle="Tenant identity, domain resolution, and address">
          <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-4 gap-y-3 text-xs">
            <div>
              <dt class="font-medium text-slate-400">Hotel Name</dt>
              <dd class="mt-1 text-slate-800 font-semibold">{{ customer.name }}</dd>
            </div>

            <div>
              <dt class="font-medium text-slate-400">Slug (Subdomain)</dt>
              <dd class="mt-1 font-mono text-slate-700 bg-slate-50 px-2 py-1 rounded inline-block">
                {{ customer.slug }}
              </dd>
            </div>

            <div>
              <dt class="font-medium text-slate-400">Custom Domain</dt>
              <dd class="mt-1 text-slate-700">
                <span v-if="customer.domain" class="font-mono text-emerald-600 font-medium">
                  🌐 {{ customer.domain }}
                </span>
                <span v-else class="text-slate-400 italic">None configured (uses slug subdomain)</span>
              </dd>
            </div>

            <div>
              <dt class="font-medium text-slate-400">Contact Email</dt>
              <dd class="mt-1 text-slate-700">{{ customer.contact_email ?? '—' }}</dd>
            </div>

            <div>
              <dt class="font-medium text-slate-400">Contact Phone</dt>
              <dd class="mt-1 text-slate-700">{{ customer.contact_phone ?? '—' }}</dd>
            </div>

            <div>
              <dt class="font-medium text-slate-400">Created Date</dt>
              <dd class="mt-1 text-slate-700">{{ customer.created_at ?? '—' }}</dd>
            </div>

            <div class="sm:col-span-2">
              <dt class="font-medium text-slate-400">Physical Address</dt>
              <dd class="mt-1 text-slate-700">{{ customer.address ?? '—' }}</dd>
            </div>
          </dl>
        </AdminCard>

        <!-- Hotel Administration & Users -->
        <AdminCard title="Hotel Administrators & Staff" subtitle="User accounts attached to this tenant">
          <template #actions>
            <AdminButton size="sm" variant="secondary" @click="showUserModal = true">
              + Add User
            </AdminButton>
          </template>

          <div class="overflow-x-auto">
            <table class="w-full text-start text-xs text-slate-600">
              <thead class="bg-slate-50 text-slate-500 border-b border-slate-200 uppercase text-[10px] font-semibold">
                <tr>
                  <th class="px-3 py-2 text-start">Name</th>
                  <th class="px-3 py-2 text-start">Email</th>
                  <th class="px-3 py-2 text-start">Role</th>
                  <th class="px-3 py-2 text-start">Status</th>
                  <th class="px-3 py-2 text-end">Joined</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-100">
                <tr v-for="user in customer.users" :key="user.id" class="hover:bg-slate-50/50">
                  <td class="px-3 py-2.5 font-medium text-slate-800">
                    {{ user.name }}
                    <span
                      v-if="customer.primary_admin?.id === user.id"
                      class="ms-1.5 px-1.5 py-0.5 rounded text-[10px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200"
                    >
                      Primary
                    </span>
                  </td>
                  <td class="px-3 py-2.5 text-slate-600 font-mono text-[11px]">{{ user.email }}</td>
                  <td class="px-3 py-2.5">
                    <span
                      class="px-2 py-0.5 rounded text-[10px] font-medium"
                      :class="user.role === 'hotel_admin' ? 'bg-indigo-50 text-indigo-700' : 'bg-slate-100 text-slate-600'"
                    >
                      {{ user.role }}
                    </span>
                  </td>
                  <td class="px-3 py-2.5">
                    <span class="text-emerald-600 font-medium">{{ user.status }}</span>
                  </td>
                  <td class="px-3 py-2.5 text-end text-slate-400 text-[11px]">{{ user.created_at ?? '—' }}</td>
                </tr>

                <tr v-if="customer.users.length === 0">
                  <td colspan="5" class="px-3 py-4 text-center text-slate-400 italic">
                    No users registered for this hotel.
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </AdminCard>

        <!-- Hotel Branches & Branch Domains -->
        <AdminCard
          title="Hotel Branches & Domains"
          subtitle="Regional locations and custom branch domain assignments"
        >
          <div class="overflow-x-auto">
            <table class="w-full text-start text-xs">
              <thead class="bg-slate-50 border-b border-slate-200 font-semibold text-slate-500 uppercase tracking-wider">
                <tr>
                  <th class="px-3 py-2 text-start">Branch</th>
                  <th class="px-3 py-2 text-start">City</th>
                  <th class="px-3 py-2 text-start">Branch Domain</th>
                  <th class="px-3 py-2 text-start">Status</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-100">
                <tr v-for="branch in customer.branches" :key="branch.id" class="hover:bg-slate-50/60">
                  <td class="px-3 py-2.5 font-medium text-slate-800">
                    <div class="flex items-center gap-1.5 flex-wrap">
                      <span>{{ branch.name }}</span>
                      <span v-if="branch.is_main" class="px-1.5 py-0.5 rounded text-[10px] font-semibold bg-emerald-100 text-emerald-800">
                        Main
                      </span>
                    </div>
                    <div class="text-[11px] text-slate-400 font-mono">slug: {{ branch.slug }}</div>
                  </td>
                  <td class="px-3 py-2.5 text-slate-600">{{ branch.city || '—' }}</td>
                  <td class="px-3 py-2.5">
                    <a
                      v-if="branch.domain"
                      :href="`http://${branch.domain}`"
                      target="_blank"
                      rel="noopener noreferrer"
                      class="inline-flex items-center gap-1 font-mono text-emerald-600 hover:underline"
                    >
                      <span>{{ branch.domain }}</span>
                      <span class="text-slate-400 text-[10px]">↗</span>
                    </a>
                    <span v-else class="text-slate-400 italic">None</span>
                  </td>
                  <td class="px-3 py-2.5">
                    <span
                      class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-medium"
                      :class="branch.status === 'published' ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-600'"
                    >
                      {{ branch.status }}
                    </span>
                  </td>
                </tr>

                <tr v-if="!customer.branches || customer.branches.length === 0">
                  <td colspan="4" class="px-3 py-4 text-center text-slate-400 italic">
                    No branches configured for this hotel yet.
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </AdminCard>
      </div>

      <!-- Right 1 Col: Operations, Settings & Content Counts -->
      <div class="space-y-6">
        <!-- Configuration & Settings -->
        <AdminCard title="Hotel Configuration" subtitle="Operational settings and defaults">
          <dl class="space-y-3 text-xs">
            <div>
              <dt class="font-medium text-slate-400">Default Locale</dt>
              <dd class="mt-1 font-semibold text-slate-800 uppercase">
                {{ customer.settings?.default_locale ?? 'en' }}
              </dd>
            </div>

            <div>
              <dt class="font-medium text-slate-400">Timezone / Currency</dt>
              <dd class="mt-1 text-slate-700">
                {{ customer.timezone }} / {{ customer.currency }}
              </dd>
            </div>

            <div>
              <dt class="font-medium text-slate-400">Check-in / Check-out Time</dt>
              <dd class="mt-1 text-slate-700">
                {{ customer.settings?.checkin_time ?? '14:00' }} / {{ customer.settings?.checkout_time ?? '12:00' }}
              </dd>
            </div>

            <div>
              <dt class="font-medium text-slate-400">Guest View Mode</dt>
              <dd class="mt-1 text-slate-700 font-medium capitalize">
                {{ customer.settings?.guest_view ?? 'classic' }}
              </dd>
            </div>
          </dl>
        </AdminCard>

        <!-- Content Inventory Summary -->
        <AdminCard title="Tenant Content Summary" subtitle="Total records owned by this hotel">
          <div class="space-y-2 text-xs">
            <div class="flex items-center justify-between p-2 rounded-lg bg-slate-50">
              <span class="text-slate-600 font-medium">Facilities</span>
              <span class="font-bold text-slate-800">{{ customer.content_counts.facilities }}</span>
            </div>
            <div class="flex items-center justify-between p-2 rounded-lg bg-slate-50">
              <span class="text-slate-600 font-medium">Rooms & Suites</span>
              <span class="font-bold text-slate-800">{{ customer.content_counts.rooms }}</span>
            </div>
            <div class="flex items-center justify-between p-2 rounded-lg bg-slate-50">
              <span class="text-slate-600 font-medium">Restaurants</span>
              <span class="font-bold text-slate-800">{{ customer.content_counts.restaurants }}</span>
            </div>
            <div class="flex items-center justify-between p-2 rounded-lg bg-slate-50">
              <span class="text-slate-600 font-medium">Custom Pages</span>
              <span class="font-bold text-slate-800">{{ customer.content_counts.pages }}</span>
            </div>
          </div>
        </AdminCard>
      </div>
    </div>

    <!-- Modal: Change Domain -->
    <div
      v-if="showDomainModal"
      class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-xs"
    >
      <div class="bg-white rounded-xl shadow-xl max-w-md w-full p-6">
        <h3 class="text-base font-semibold text-slate-800 mb-1">Configure Custom Domain</h3>
        <p class="text-xs text-slate-500 mb-4">
          Point a custom domain directly to this hotel. Leave blank to resolve exclusively via subdomain slug (<code>{{ customer.slug }}</code>).
        </p>

        <form @submit.prevent="submitDomain">
          <AdminInput
            v-model="domainForm.domain"
            label="Domain Name"
            placeholder="e.g. hotel-example.com"
            :error="domainForm.errors.domain"
          />

          <div class="mt-5 flex items-center justify-end gap-2">
            <AdminButton variant="secondary" size="sm" @click="showDomainModal = false">
              Cancel
            </AdminButton>
            <AdminButton type="submit" variant="primary" size="sm" :disabled="domainForm.processing">
              Save Domain
            </AdminButton>
          </div>
        </form>
      </div>
    </div>

    <!-- Modal: Add User -->
    <div
      v-if="showUserModal"
      class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-xs"
    >
      <div class="bg-white rounded-xl shadow-xl max-w-md w-full p-6">
        <h3 class="text-base font-semibold text-slate-800 mb-1">Add Hotel User</h3>
        <p class="text-xs text-slate-500 mb-4">
          Create an administrator or staff member scoped strictly to <strong>{{ customer.name }}</strong>.
        </p>

        <form class="space-y-4" @submit.prevent="submitUser">
          <AdminInput
            v-model="userForm.name"
            label="Full Name"
            placeholder="e.g. Michael Smith"
            required
            :error="userForm.errors.name"
          />

          <AdminInput
            v-model="userForm.email"
            type="email"
            label="Email Address"
            placeholder="michael@hotel.com"
            required
            :error="userForm.errors.email"
          />

          <AdminInput
            v-model="userForm.password"
            type="password"
            label="Password"
            placeholder="At least 8 characters"
            required
            :error="userForm.errors.password"
          />

          <AdminSelect
            v-model="userForm.role"
            label="Tenant Role"
            :options="[
              { label: 'Hotel Administrator', value: 'hotel_admin' },
              { label: 'Hotel Staff', value: 'hotel_staff' }
            ]"
            required
            :error="userForm.errors.role"
          />

          <div class="mt-5 flex items-center justify-end gap-2 pt-2">
            <AdminButton variant="secondary" size="sm" @click="showUserModal = false">
              Cancel
            </AdminButton>
            <AdminButton type="submit" variant="primary" size="sm" :disabled="userForm.processing">
              Add User
            </AdminButton>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>
