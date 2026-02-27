<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import LabelWithTooltip from '@/Components/LabelWithTooltip.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import RichTextEditor from '@/Components/RichTextEditor.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { reactive, ref } from 'vue';

const processing = ref(false);
const form = reactive({
  title: '',
  slug: '',
  excerpt: '',
  content: '',
  published_at: '',
  is_published: false,
});
const errors = reactive({});

function slugFromTitle() {
  if (!form.title) return;
  form.slug = form.title.toLowerCase().replace(/\s+/g, '-').replace(/[^a-z0-9_-]/g, '');
}

async function submit() {
  processing.value = true;
  Object.keys(errors).forEach((k) => delete errors[k]);
  try {
    await window.axios.post('/api/blogs', form);
    router.visit(route('blogs.index') + '?success=created');
  } catch (e) {
    if (e.response?.status === 422 && e.response?.data?.errors) {
      Object.assign(errors, e.response.data.errors);
    } else {
      errors.form = e.response?.data?.message || 'Something went wrong.';
    }
  } finally {
    processing.value = false;
  }
}
</script>

<template>
  <Head title="Add blog post" />

  <AuthenticatedLayout>
    <template #header>Add blog post</template>

    <div class="admin-form-page">
      <div class="admin-form-page-top">
        <div class="admin-form-page-header">
          <h1 class="admin-form-page-title">Add blog post</h1>
          <p class="admin-form-page-desc text-muted small">Create a new blog post or article.</p>
        </div>
        <div class="admin-form-page-top-actions">
          <PrimaryButton type="submit" form="create-blog-form" :loading="processing" :disabled="processing" class="btn btn-primary btn-sm admin-btn-smooth">Save</PrimaryButton>
        </div>
      </div>
      <div class="admin-box admin-box-smooth">
        <form id="create-blog-form" @submit.prevent="submit" class="admin-form-smooth">
          <div class="row g-3 mb-3">
            <div class="col-md-6">
              <LabelWithTooltip for="title" value="Title" required />
              <TextInput id="title" v-model="form.title" required class="form-control form-control-sm" @blur="slugFromTitle" />
              <InputError :message="errors.title?.[0]" />
            </div>
            <div class="col-md-6">
              <LabelWithTooltip for="slug" value="Slug" required />
              <TextInput id="slug" v-model="form.slug" required class="form-control form-control-sm" />
              <InputError :message="errors.slug?.[0]" />
            </div>
          </div>
          <div class="mb-3">
            <LabelWithTooltip for="excerpt" value="Excerpt" optional />
            <textarea id="excerpt" v-model="form.excerpt" class="form-control form-control-sm" rows="2"></textarea>
          </div>
          <div class="mb-3">
            <LabelWithTooltip for="content" value="Content" tip="Blog post body. Use the toolbar for headings, lists, links and formatting." optional />
            <RichTextEditor v-model="form.content" />
            <InputError :message="errors.content?.[0]" />
          </div>
          <div class="row g-3 mb-3">
            <div class="col-md-6">
              <LabelWithTooltip for="published_at" value="Published at" optional />
              <TextInput id="published_at" v-model="form.published_at" type="datetime-local" class="form-control form-control-sm" />
            </div>
            <div class="col-md-6 d-flex align-items-end">
              <div class="form-check">
                <input id="is_published" v-model="form.is_published" type="checkbox" class="form-check-input" />
                <label for="is_published" class="form-check-label small">Published</label>
              </div>
            </div>
          </div>
          <InputError v-if="errors.form" :message="errors.form" />
          <div class="d-flex gap-2">
            <Link :href="route('blogs.index')" class="btn btn-secondary btn-sm admin-btn-smooth">Cancel</Link>
            <PrimaryButton type="submit" :loading="processing" :disabled="processing" class="btn btn-primary btn-sm admin-btn-smooth">Create blog post</PrimaryButton>
          </div>
        </form>
      </div>
    </div>
  </AuthenticatedLayout>
</template>
