<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import HomePageEditor from '@/Components/HomePageEditor.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

const props = defineProps({
  homePageContent: { type: String, default: '' },
  contactEmail: { type: String, default: '' },
  contactPhone: { type: String, default: '' },
  contactAddress: { type: String, default: '' },
  flash: { type: Object, default: () => ({}) },
});

const activeTab = ref('home');

const form = useForm({
  home_page_content: props.homePageContent,
  contact_email: props.contactEmail,
  contact_phone: props.contactPhone,
  contact_address: props.contactAddress,
});

watch(
  () => [props.homePageContent, props.contactEmail, props.contactPhone, props.contactAddress],
  ([home, contact, phone, address]) => {
    form.home_page_content = home ?? '';
    form.contact_email = contact ?? '';
    form.contact_phone = phone ?? '';
    form.contact_address = address ?? '';
  }
);

function submitHome() {
  form.clearErrors();
  form.put(route('content-manager.update'), {
    preserveScroll: true,
    onSuccess: () => {},
  });
}

function submitContact() {
  form.clearErrors();
  form.put(route('content-manager.update'), {
    preserveScroll: true,
    onSuccess: () => {},
  });
}
</script>

<template>
  <Head title="Content manager" />

  <AuthenticatedLayout>
    <template #header>Content manager</template>

    <div class="admin-form-page">
      <div class="admin-form-page-header mb-3">
        <h1 class="admin-form-page-title">Content manager</h1>
        <p class="admin-form-page-desc text-muted small">
          Manage your content here. Use the tabs below to edit the home page or contact mail settings.
        </p>
      </div>

      <div v-if="flash?.success" class="alert alert-success alert-dismissible fade show mb-3" role="alert">
        {{ flash.success }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>

      <!-- Tabs -->
      <ul class="nav nav-tabs admin-tabs mb-3" role="tablist">
        <li class="nav-item" role="presentation">
          <button
            type="button"
            class="nav-link"
            :class="{ active: activeTab === 'home' }"
            role="tab"
            aria-selected="activeTab === 'home'"
            @click="activeTab = 'home'"
          >
            Home page
          </button>
        </li>
        <li class="nav-item" role="presentation">
          <button
            type="button"
            class="nav-link"
            :class="{ active: activeTab === 'contact' }"
            role="tab"
            aria-selected="activeTab === 'contact'"
            @click="activeTab = 'contact'"
          >
            Contact us mail information
          </button>
        </li>
      </ul>

      <div class="admin-box admin-box-smooth">
        <!-- Tab: Home page -->
        <div v-show="activeTab === 'home'" role="tabpanel" class="content-manager-tab-panel">
          <p class="text-muted small mb-3">
            Design your home page content. Use the <strong>Card</strong> button in the toolbar to add card blocks. Cards are displayed as separate blocks on the frontend.
          </p>
          <div class="mb-3">
            <label class="form-label small fw-semibold">Home page content</label>
            <HomePageEditor v-model="form.home_page_content" />
            <InputError :message="form.errors.home_page_content" />
          </div>
          <PrimaryButton
            type="button"
            class="btn btn-primary btn-sm admin-btn-smooth"
            :disabled="form.processing"
            @click="submitHome"
          >
            Save home page
          </PrimaryButton>
        </div>

        <!-- Tab: Contact us mail information -->
        <div v-show="activeTab === 'contact'" role="tabpanel" class="content-manager-tab-panel">
          <p class="text-muted small mb-3">
            <strong>On this email you will get requests from the contact request page.</strong> These details are shown on the frontend Contact page.
          </p>
          <div class="mb-3">
            <label for="contact_email" class="form-label small fw-semibold">Contact email</label>
            <TextInput
              id="contact_email"
              v-model="form.contact_email"
              type="email"
              class="form-control form-control-sm"
              placeholder="e.g. contact@example.com"
            />
            <InputError :message="form.errors.contact_email" />
          </div>
          <div class="mb-3">
            <label for="contact_phone" class="form-label small fw-semibold">Contact phone / number</label>
            <TextInput
              id="contact_phone"
              v-model="form.contact_phone"
              type="text"
              class="form-control form-control-sm"
              placeholder="e.g. +1 234 567 8900"
            />
            <InputError :message="form.errors.contact_phone" />
          </div>
          <div class="mb-3">
            <label for="contact_address" class="form-label small fw-semibold">Contact address</label>
            <textarea
              id="contact_address"
              v-model="form.contact_address"
              class="form-control form-control-sm"
              rows="3"
              placeholder="e.g. 123 Main St, City, Country"
            />
            <InputError :message="form.errors.contact_address" />
          </div>
          <PrimaryButton
            type="button"
            class="btn btn-primary btn-sm admin-btn-smooth"
            :disabled="form.processing"
            @click="submitContact"
          >
            Save contact details
          </PrimaryButton>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<style scoped>
.admin-tabs {
  border-bottom: 1px solid var(--admin-card-border, #eaeaef);
}
.admin-tabs .nav-link {
  border: none;
  border-bottom: 2px solid transparent;
  background: none;
  color: var(--admin-text-muted, #666687);
  font-weight: 500;
  padding: 0.5rem 1rem;
  cursor: pointer;
  margin-bottom: -1px;
  border-radius: 0;
}
.admin-tabs .nav-link:hover {
  color: var(--admin-text, #32324d);
}
.admin-tabs .nav-link.active {
  color: var(--admin-primary, #181826);
  border-bottom-color: var(--admin-primary, #181826);
}
.content-manager-tab-panel {
  padding: 0.25rem 0;
}
</style>
