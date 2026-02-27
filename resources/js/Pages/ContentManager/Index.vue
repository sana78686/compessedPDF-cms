<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
</script>

<template>
  <Head title="Content manager" />

  <AuthenticatedLayout>
    <template #header>Content manager</template>

    <div class="admin-form-page">
      <div class="admin-form-page-header mb-3">
        <h1 class="admin-form-page-title">Content manager</h1>
        <p class="admin-form-page-desc text-muted small">
          Manage your content here. Add pages, posts, or other content types as you build out your CMS.
        </p>
      </div>
      <div class="admin-box admin-box-smooth">
        <p class="admin-text-muted small mb-0">
          This is your content manager area. You can add content types and list views here later.
        </p>
      </div>
    </div>
  </AuthenticatedLayout>
</template>
