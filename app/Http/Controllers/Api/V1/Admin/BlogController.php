<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\BlogCategory;
use App\Models\BlogPost;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class BlogController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $status = $request->query('status');
        $categoryId = $request->query('category_id');
        $search = $request->query('search');

        $query = BlogPost::with(['category', 'author']);

        if ($status) {
            $query->where('status', $status);
        }

        if ($categoryId) {
            $query->where('category_id', $categoryId);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('excerpt', 'like', "%{$search}%");
            });
        }

        $posts = $query->latest()->paginate(15);
        $categories = BlogCategory::where('is_active', true)->get();

        return response()->json([
            'success' => true,
            'data' => [
                'posts' => $posts,
                'categories' => $categories,
            ],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:blog_posts,slug',
            'category_id' => 'nullable|exists:blog_categories,id',
            'featured_image' => 'nullable|string',
            'excerpt' => 'nullable|string',
            'content' => 'required|string',
            'status' => 'required|in:draft,published,scheduled',
            'published_at' => 'nullable|date',
            'seo_title' => 'nullable|string|max:255',
            'seo_description' => 'nullable|string',
        ]);

        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['title']);
        }

        $validated['author_id'] = $request->user()?->id;

        $post = BlogPost::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Editorial post created successfully.',
            'data' => $post,
        ], 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $post = BlogPost::findOrFail($id);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:blog_posts,slug,' . $post->id,
            'category_id' => 'nullable|exists:blog_categories,id',
            'featured_image' => 'nullable|string',
            'excerpt' => 'nullable|string',
            'content' => 'required|string',
            'status' => 'required|in:draft,published,scheduled',
            'published_at' => 'nullable|date',
            'seo_title' => 'nullable|string|max:255',
            'seo_description' => 'nullable|string',
        ]);

        $post->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Editorial post updated successfully.',
            'data' => $post,
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        $post = BlogPost::findOrFail($id);
        $post->delete();

        return response()->json([
            'success' => true,
            'message' => 'Editorial post deleted successfully.',
        ]);
    }
}
