<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProductReview;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $status = $request->query('status'); // approved, pending
        $rating = $request->query('rating');
        $search = $request->query('search');

        $query = ProductReview::with(['product', 'user']);

        if ($status === 'approved') {
            $query->where('is_approved', true);
        } elseif ($status === 'pending') {
            $query->where('is_approved', false);
        }

        if ($rating) {
            $query->where('rating', (int) $rating);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('review_title', 'like', "%{$search}%")
                  ->orWhere('review_text', 'like', "%{$search}%")
                  ->orWhere('customer_name', 'like', "%{$search}%")
                  ->orWhereHas('product', function ($pq) use ($search) {
                      $pq->where('name', 'like', "%{$search}%");
                  });
            });
        }

        $reviews = $query->latest()->paginate(15);

        return response()->json([
            'success' => true,
            'data' => $reviews,
        ]);
    }

    public function toggleApproval(int $id): JsonResponse
    {
        $review = ProductReview::findOrFail($id);
        $review->is_approved = !$review->is_approved;
        $review->save();

        return response()->json([
            'success' => true,
            'message' => 'Review approval status updated.',
            'data' => $review,
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        $review = ProductReview::findOrFail($id);
        $review->delete();

        return response()->json([
            'success' => true,
            'message' => 'Review deleted successfully.',
        ]);
    }
}
