<?php

namespace App\Http\Controllers\Api\V1\Public;

use App\Http\Controllers\Controller;
use App\Models\BlogCategory;
use App\Models\BlogPost;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BlogController extends Controller
{
    /**
     * Posts visible on the storefront: published (or scheduled) posts whose
     * publish date is empty or has already passed.
     */
    private function visible(): Builder
    {
        return BlogPost::query()
            ->whereIn('status', ['published', 'scheduled'])
            ->where(function (Builder $q) {
                $q->where(function (Builder $q) {
                    $q->where('status', 'published')->whereNull('published_at');
                })->orWhere('published_at', '<=', now());
            });
    }

    public function index(Request $request): JsonResponse
    {
        $query = $this->visible()->with(['category:id,name,slug', 'author:id,name']);

        if ($slug = $request->query('category')) {
            $query->whereHas('category', fn (Builder $q) => $q->where('slug', $slug));
        }

        if ($search = $request->query('search')) {
            $query->where(function (Builder $q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('excerpt', 'like', "%{$search}%");
            });
        }

        $posts = $query
            ->select(['id', 'title', 'slug', 'category_id', 'author_id', 'featured_image', 'excerpt', 'published_at', 'views_count', 'created_at'])
            ->orderByRaw('COALESCE(published_at, created_at) DESC')
            ->paginate(min((int) $request->query('per_page', 9), 30));

        return response()->json([
            'success' => true,
            'data' => [
                'posts' => $posts,
                'categories' => BlogCategory::where('is_active', true)->orderBy('name')->get(['id', 'name', 'slug']),
            ],
        ]);
    }

    public function show(string $slug): JsonResponse
    {
        $post = $this->visible()
            ->with(['category:id,name,slug', 'author:id,name'])
            ->where('slug', $slug)
            ->firstOrFail();

        $post->increment('views_count');

        $related = $this->visible()
            ->where('id', '!=', $post->id)
            ->when($post->category_id, fn (Builder $q) => $q->where('category_id', $post->category_id))
            ->orderByRaw('COALESCE(published_at, created_at) DESC')
            ->limit(3)
            ->get(['id', 'title', 'slug', 'featured_image', 'excerpt', 'published_at', 'created_at']);

        return response()->json([
            'success' => true,
            'data' => [
                'post' => $post,
                'related' => $related,
            ],
        ]);
    }
}
