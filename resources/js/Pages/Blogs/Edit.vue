<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import LabelWithTooltip from '@/Components/LabelWithTooltip.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { computed, onMounted, reactive, ref } from 'vue';

const props = defineProps({
  blogId: { type: [Number, String], required: true },
  blog: { type: Object, default: null },
});

const loading = ref(true);
const processing = ref(false);
const loadError = ref('');
const form = reactive({
  title: '',
  slug: '',
  excerpt: '',
  content: '',
  published_at: '',
  is_published: false,
});
const errors = reactive({});
const blogIdNum = computed(() => Number(props.blogId) || 0);

function toDatetimeLocal(iso) {
  if (!iso) return '';
  const d = new Date(iso);
  const y = d.getFullYear();
  const m = String(d.getMonth() + 1).padStart(2, '0');
  const day = String(d.getDate()).padStart(2, '0');
  const h = String(d.getHours()).padStart(2, '0');
  const min = String(d.getMinutes()).padStart(2, '0');
  return `${y}-${m}-${day}T${h}:${min}`;
}

onMounted(async () => {
  if (props.blog) {
    form.title = props.blog.title ?? '';
    form.slug = props.blog.slug ?? '';
    form.excerpt = props.blog.excerpt ?? '';
    form.content = props.blog.content ?? '';
    form.published_at = toDatetimeLocal(props.blog.published_at);
    form.is_published = !!props.blog.is_published;
    loading.value = false;
    return;
  }
  try {
    const { data } = await window.axios.get(`/api/blogs/${blogIdNum.value}/edit`);
    const b = data.blog ?? {};
    form.title = b.title ?? '';
    form.slug = b.slug ?? '';
    form.excerpt = b.excerpt ?? '';
    form.content = b.content ?? '';
    form.published_at = toDatetimeLocal(b.published_at);
    form.is_published = !!b.is_published;
  } catch (e) {
    loadError.value = e.response?.data?.message || 'Failed to load blog.';
  } finally {
    loading.value = false;
  }
});

function slugFromTitle() {
  if (!form.title) return;
  form.slug = form.title.toLowerCase().replace(/\s+/g, '-').replace(/[^a-z0-9_-]/g, '');
}

async function submit() {
  processing.value = true;
  Object.keys(errors).forEach((k) => delete errors[k]);
  try {
    await window.axios.put(`/api/blogs/${blogIdNum.value}`, form);
    router.visit(route('blogs.index') + '?success=updated');
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
  <Head title="Edit blog post" />

  <AuthenticatedLayout>
    <template #header>Edit blog post</template>

    <div class="admin-form-page">
      <div class="admin-form-page-top">
        <div class="admin-form-page-header">
          <h1 class="admin-form-page-title">Edit blog post</h1>
          <p class="admin-form-page-desc text-muted small">Update the blog post.</p>
        </div>
        <div v-if="!loading && !loadError" class="admin-form-page-top-actions">
          <PrimaryButton type="submit" form="edit-blog-form" :loading="processing" :disabled="processing" class="btn btn-primary btn-sm admin-btn-smooth">Save</PrimaryButton>
        </div>
      </div>
      <div class="admin-box admin-box-smooth">
        <Transition name="admin-fade" mode="out-in">
          <div v-if="loading" key="loading" class="admin-loading-placeholder">
            <span class="spinner-border spinner-border-sm text-secondary" role="status" aria-hidden="true"></span>
            <span class="ms-2 text-muted small">Loading…</span>
          </div>
          <div v-else-if="loadError" key="error" class="text-danger small mb-0">
            {{ loadError }}
            <Link :href="route('blogs.index')" class="ms-2">Back to blogs</Link>
          </div>
          <form id="edit-blog-form" v-else key="form" @submit.prevent="submit" class="admin-form-smooth">
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
              <PrimaryButton type="submit" :loading="processing" :disabled="processing" class="btn btn-primary btn-sm admin-btn-smooth">Update blog post</PrimaryButton>
            </div>
          </form>
        </Transition>
      </div>
    </div>
  </AuthenticatedLayout>
</template>
