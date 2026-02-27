<?php

namespace App\Http\Controllers;

use App\Models\Blog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class BlogController extends Controller
{
    private function blogToArray(Blog $blog): array
    {
        $blog->loadMissing('author:id,name');
        return [
            'id' => $blog->id,
            'title' => $blog->title,
            'slug' => $blog->slug,
            'excerpt' => $blog->excerpt,
            'content' => $blog->content,
            'published_at' => $blog->published_at?->toIso8601String(),
            'user_id' => $blog->user_id,
            'author' => $blog->author ? ['id' => $blog->author->id, 'name' => $blog->author->name] : null,
            'is_published' => $blog->is_published,
            'created_at' => $blog->created_at->toIso8601String(),
        ];
    }

    public function index(): Response|JsonResponse
    {
        $blogs = Blog::with('author:id,name')
            ->orderByDesc('created_at')
            ->get()
            ->map(fn ($b) => $this->blogToArray($b));

        if (request()->is('api/*')) {
            return response()->json(['blogs' => $blogs]);
        }
        return Inertia::render('Blogs/Index');
    }

    public function create(): Response|JsonResponse
    {
        if (request()->is('api/*')) {
            return response()->json([]);
        }
        return Inertia::render('Blogs/Create');
    }

    public function store(Request $request): RedirectResponse|JsonResponse
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:blogs,slug',
            'excerpt' => 'nullable|string|max:1000',
            'content' => 'nullable|string',
            'published_at' => 'nullable|date',
            'is_published' => 'boolean',
        ]);

        $blog = Blog::create([
            'title' => $request->title,
            'slug' => $request->slug ?: Str::slug($request->title),
            'excerpt' => $request->excerpt,
            'content' => $request->content,
            'published_at' => $request->published_at,
            'user_id' => $request->user()?->id,
            'is_published' => $request->boolean('is_published', false),
        ]);

        if (request()->is('api/*')) {
            return response()->json(['message' => 'Blog created.', 'blog' => $this->blogToArray($blog)], 201);
        }
        return redirect()->route('blogs.index')->with('success', 'created');
    }

    public function edit(Blog $blog): Response|JsonResponse
    {
        $payload = ['blogId' => $blog->id, 'blog' => $this->blogToArray($blog)];
        if (request()->is('api/*')) {
            return response()->json($payload);
        }
        return Inertia::render('Blogs/Edit', $payload);
    }

    public function update(Request $request, Blog $blog): RedirectResponse|JsonResponse
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:blogs,slug,' . $blog->id,
            'excerpt' => 'nullable|string|max:1000',
            'content' => 'nullable|string',
            'published_at' => 'nullable|date',
            'is_published' => 'boolean',
        ]);

        $blog->update([
            'title' => $request->title,
            'slug' => $request->slug,
            'excerpt' => $request->excerpt,
            'content' => $request->content,
            'published_at' => $request->published_at,
            'is_published' => $request->boolean('is_published', false),
        ]);

        if (request()->is('api/*')) {
            return response()->json(['message' => 'Blog updated.', 'blog' => $this->blogToArray($blog)]);
        }
        return redirect()->route('blogs.index')->with('success', 'updated');
    }

    public function destroy(Blog $blog): RedirectResponse|JsonResponse
    {
        $blog->delete();
        if (request()->is('api/*')) {
            return response()->json(['message' => 'Blog deleted.']);
        }
        return redirect()->route('blogs.index')->with('success', 'deleted');
    }

    public function togglePublish(Blog $blog): JsonResponse
    {
        $blog->is_published = !$blog->is_published;
        $blog->visibility = $blog->is_published ? Blog::VISIBILITY_PUBLISHED : Blog::VISIBILITY_DRAFT;
        $blog->save();
        return response()->json([
            'is_published' => $blog->is_published,
            'message' => $blog->is_published ? 'Blog published.' : 'Blog unpublished.',
        ]);
    }
}
