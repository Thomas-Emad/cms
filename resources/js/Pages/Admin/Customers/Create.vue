<script setup lang="ts">
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { useForm } from '@inertiajs/vue3';
import { AdminButton, AdminCard, AdminInput, AdminSelect, PageHeader } from '@/Components/Admin';

defineOptions({ layout: AdminLayout });

const props = defineProps<{
  timezones: string[];
  currencies: string[];
}>();

const form = useForm({
  name: '',
  slug: '',
  domain: '',
  status: 'active',
  contact_email: '',
  contact_phone: '',
  address: '',
  timezone: 'UTC',
  currency: 'USD',

  // Primary Administrator
  admin_name: '',
  admin_email: '',
  admin_password: '',

  // Hotel Settings
  default_locale: 'en',
  checkin_time: '14:00',
  checkout_time: '12:00',
});

function onNameInput() {
  if (!form.slug || form.slug === slugify(form.name.slice(0, -1))) {
    form.slug = slugify(form.name);
  }
}

function slugify(text: string): string {
  return text
    .toString()
    .toLowerCase()
    .trim()
    .replace(/\s+/g, '-')
    .replace(/[^\w\-]+/g, '')
    .replace(/\-\-+/g, '-');
}

function submit() {
  form.post('/admin/customers');
}
</script>

<template>
  <div class="max-w-4xl">
    <PageHeader
      title="Create New Hotel Customer"
      subtitle="Provision a new tenant hotel with dedicated settings, custom domain, and primary administrator."
    />

    <form class="space-y-6" @submit.prevent="submit">
      <!-- 1. Hotel Information -->
      <AdminCard title="Hotel Tenant Information" subtitle="Basic property identity and host details">
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <AdminInput
            v-model="form.name"
            label="Hotel Name"
            placeholder="e.g. Grand Horizon Hotel"
            required
            :error="form.errors.name"
            @input="onNameInput"
          />

          <AdminInput
            v-model="form.slug"
            label="Subdomain / Slug"
            placeholder="e.g. grand-horizon"
            hint="Used for subdomain resolution: {slug}.your-saas.com"
            required
            :error="form.errors.slug"
          />

          <AdminInput
            v-model="form.domain"
            label="Custom Domain (Optional)"
            placeholder="e.g. grandhorizon.com"
            hint="Exact custom domain pointing to this hotel"
            :error="form.errors.domain"
          />

          <AdminSelect
            v-model="form.status"
            label="Initial Status"
            :options="[
              { label: 'Active', value: 'active' },
              { label: 'Suspended', value: 'suspended' },
              { label: 'Draft', value: 'draft' }
            ]"
            required
            :error="form.errors.status"
          />

          <AdminInput
            v-model="form.contact_email"
            type="email"
            label="Contact Email"
            placeholder="info@hotel.com"
            :error="form.errors.contact_email"
          />

          <AdminInput
            v-model="form.contact_phone"
            label="Contact Phone"
            placeholder="+1 234 567 890"
            :error="form.errors.contact_phone"
          />

          <div class="sm:col-span-2">
            <AdminInput
              v-model="form.address"
              label="Address"
              placeholder="e.g. 123 Resort Boulevard, Cairo, Egypt"
              :error="form.errors.address"
            />
          </div>

          <AdminSelect
            v-model="form.timezone"
            label="Timezone"
            :options="props.timezones.map(tz => ({ label: tz, value: tz }))"
            :error="form.errors.timezone"
          />

          <AdminSelect
            v-model="form.currency"
            label="Currency"
            :options="props.currencies.map(c => ({ label: c, value: c }))"
            :error="form.errors.currency"
          />
        </div>
      </AdminCard>

      <!-- 2. Primary Administrator Account -->
      <AdminCard title="Primary Hotel Administrator" subtitle="Login credentials for the tenant's primary hotel_admin user">
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <AdminInput
            v-model="form.admin_name"
            label="Administrator Name"
            placeholder="e.g. Sarah Jenkins"
            required
            :error="form.errors.admin_name"
          />

          <AdminInput
            v-model="form.admin_email"
            type="email"
            label="Admin Login Email"
            placeholder="admin@hotel.com"
            required
            :error="form.errors.admin_email"
          />

          <div class="sm:col-span-2">
            <AdminInput
              v-model="form.admin_password"
              type="password"
              label="Admin Password"
              placeholder="At least 8 characters"
              required
              :error="form.errors.admin_password"
            />
          </div>
        </div>
      </AdminCard>

      <!-- 3. Default Configuration & Settings -->
      <AdminCard title="Hotel Operations & Configuration" subtitle="Initial hotel settings and schedule parameters">
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
          <AdminSelect
            v-model="form.default_locale"
            label="Default Language"
            :options="[
              { label: 'English (en)', value: 'en' },
              { label: 'Arabic (ar)', value: 'ar' }
            ]"
            :error="form.errors.default_locale"
          />

          <AdminInput
            v-model="form.checkin_time"
            label="Standard Check-in Time"
            placeholder="14:00"
            :error="form.errors.checkin_time"
          />

          <AdminInput
            v-model="form.checkout_time"
            label="Standard Check-out Time"
            placeholder="12:00"
            :error="form.errors.checkout_time"
          />
        </div>
      </AdminCard>

      <!-- Actions -->
      <div class="flex items-center justify-end gap-3 pt-2">
        <AdminButton variant="secondary" href="/admin/customers">
          Cancel
        </AdminButton>

        <AdminButton type="submit" variant="primary" :disabled="form.processing">
          {{ form.processing ? 'Creating Hotel...' : 'Create Hotel Customer' }}
        </AdminButton>
      </div>
    </form>
  </div>
</template>
