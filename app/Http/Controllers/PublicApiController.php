<?php

namespace App\Http\Controllers;

use App\Models\Blog;
use App\Models\Page;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PublicApiController extends Controller
{
    /**
     * List published pages for nav/sitemap (no auth).
     */
    public function pages(Request $request): JsonResponse
    {
        $pages = Page::where('is_published', true)
            ->where('visibility', Page::VISIBILITY_PUBLISHED)
            ->whereNull('parent_id')
            ->orderBy('sort_order')
            ->orderBy('title')
            ->get(['id', 'title', 'slug', 'meta_title', 'meta_description', 'placement', 'sort_order'])
            ->map(fn ($p) => [
                'id' => $p->id,
                'title' => $p->title,
                'slug' => $p->slug,
                'meta_title' => $p->meta_title,
                'meta_description' => $p->meta_description,
                'placement' => $p->placement,
                'sort_order' => $p->sort_order,
            ]);

        return response()->json(['pages' => $pages]);
    }

    /**
     * Get a single published page by slug with full content and SEO (no auth).
     */
    public function pageBySlug(string $slug): JsonResponse
    {
        $page = Page::where('slug', $slug)
            ->where('is_published', true)
            ->where('visibility', Page::VISIBILITY_PUBLISHED)
            ->first();

        if (! $page) {
            return response()->json(['message' => 'Page not found.'], 404);
        }

        return response()->json([
            'id' => $page->id,
            'title' => $page->title,
            'slug' => $page->slug,
            'content' => $page->content,
            'meta_title' => $page->meta_title ?? $page->title,
            'meta_description' => $page->meta_description,
            'canonical_url' => $page->canonical_url,
            'meta_robots' => $page->meta_robots ?? $page->metaRobotsForVisibility(),
            'og_title' => $page->og_title ?? $page->meta_title ?? $page->title,
            'og_description' => $page->og_description ?? $page->meta_description,
            'og_image' => $page->og_image,
            'schema_type' => $page->schema_type,
            'schema_data' => $page->schema_data,
        ]);
    }

    /**
     * List published blogs for nav/listing (no auth).
     */
    public function blogs(Request $request): JsonResponse
    {
        $blogs = Blog::where('is_published', true)
            ->where('visibility', Blog::VISIBILITY_PUBLISHED)
            ->orderBy('published_at', 'desc')
            ->orderBy('title')
            ->get(['id', 'title', 'slug', 'excerpt', 'published_at', 'og_title', 'og_description', 'og_image'])
            ->map(fn ($b) => [
                'id' => $b->id,
                'title' => $b->title,
                'slug' => $b->slug,
                'excerpt' => $b->excerpt,
                'published_at' => $b->published_at?->toIso8601String(),
                'og_title' => $b->og_title,
                'og_description' => $b->og_description,
                'og_image' => $b->og_image,
            ]);

        return response()->json(['blogs' => $blogs]);
    }

    /**
     * Get a single published blog by slug with full content and SEO (no auth).
     */
    public function blogBySlug(string $slug): JsonResponse
    {
        $blog = Blog::where('slug', $slug)
            ->where('is_published', true)
            ->where('visibility', Blog::VISIBILITY_PUBLISHED)
            ->first();

        if (! $blog) {
            return response()->json(['message' => 'Blog not found.'], 404);
        }

        return response()->json([
            'id' => $blog->id,
            'title' => $blog->title,
            'slug' => $blog->slug,
            'excerpt' => $blog->excerpt,
            'content' => $blog->content,
            'published_at' => $blog->published_at?->toIso8601String(),
            'meta_title' => $blog->og_title ?? $blog->title,
            'meta_description' => $blog->og_description ?? $blog->excerpt,
            'og_title' => $blog->og_title ?? $blog->title,
            'og_description' => $blog->og_description ?? $blog->excerpt,
            'og_image' => $blog->og_image,
            'schema_type' => $blog->schema_type,
            'schema_data' => $blog->schema_data,
        ]);
    }
}
