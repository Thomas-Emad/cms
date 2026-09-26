<script setup lang="ts">
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { useForm } from '@inertiajs/vue3';
import { AdminButton, AdminCard, AdminInput, AdminSelect, PageHeader } from '@/Components/Admin';

defineOptions({ layout: AdminLayout });

interface CustomerEditData {
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
  settings: {
    checkin_time: string | null;
    checkout_time: string | null;
    default_locale: string;
  } | null;
}

const props = defineProps<{
  customer: CustomerEditData;
  timezones: string[];
  currencies: string[];
}>();

const form = useForm({
  name: props.customer.name,
  slug: props.customer.slug,
  domain: props.customer.domain ?? '',
  status: props.customer.status,
  contact_email: props.customer.contact_email ?? '',
  contact_phone: props.customer.contact_phone ?? '',
  address: props.customer.address ?? '',
  timezone: props.customer.timezone,
  currency: props.customer.currency,

  // Settings
  default_locale: props.customer.settings?.default_locale ?? 'en',
  checkin_time: props.customer.settings?.checkin_time ?? '14:00',
  checkout_time: props.customer.settings?.checkout_time ?? '12:00',
});

function submit() {
  form.put(`/admin/customers/${props.customer.id}`);
}
</script>

<template>
  <div class="max-w-4xl">
    <PageHeader
      :title="`Edit ${customer.name}`"
      subtitle="Update hotel customer details, domain routing, and property configuration."
    />

    <form class="space-y-6" @submit.prevent="submit">
      <!-- 1. Hotel Information -->
      <AdminCard title="Hotel Tenant Information" subtitle="Property identity and domain details">
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <AdminInput
            v-model="form.name"
            label="Hotel Name"
            required
            :error="form.errors.name"
          />

          <AdminInput
            v-model="form.slug"
            label="Subdomain / Slug"
            hint="Used for subdomain resolution: {slug}.your-saas.com"
            required
            :error="form.errors.slug"
          />

          <AdminInput
            v-model="form.domain"
            label="Custom Domain"
            placeholder="e.g. hotel-example.com"
            hint="Leave blank if using only subdomain"
            :error="form.errors.domain"
          />

          <AdminSelect
            v-model="form.status"
            label="Status"
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
            :error="form.errors.contact_email"
          />

          <AdminInput
            v-model="form.contact_phone"
            label="Contact Phone"
            :error="form.errors.contact_phone"
          />

          <div class="sm:col-span-2">
            <AdminInput
              v-model="form.address"
              label="Address"
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

      <!-- 2. Hotel Settings -->
      <AdminCard title="Operations & Schedule" subtitle="Check-in, check-out and default locale">
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
            label="Check-in Time"
            placeholder="14:00"
            :error="form.errors.checkin_time"
          />

          <AdminInput
            v-model="form.checkout_time"
            label="Check-out Time"
            placeholder="12:00"
            :error="form.errors.checkout_time"
          />
        </div>
      </AdminCard>

      <!-- Actions -->
      <div class="flex items-center justify-end gap-3 pt-2">
        <AdminButton variant="secondary" :href="`/admin/customers/${customer.id}`">
          Cancel
        </AdminButton>

        <AdminButton type="submit" variant="primary" :disabled="form.processing">
          {{ form.processing ? 'Saving...' : 'Save Changes' }}
        </AdminButton>
      </div>
    </form>
  </div>
</template>
