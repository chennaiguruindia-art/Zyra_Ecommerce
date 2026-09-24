<?php

namespace App\Http\Controllers;

use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Review;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    /**
     Store (or update) the logged-in customer's review for a product
     they purchased. Only non-cancelled orders qualify, and the product
     aggregates (rating / reviews_count) are recomputed.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'product_id' => 'required|integer|exists:products,id',
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1000',
        ]);

        $user = $request->user();

        $purchased = OrderItem::query()
            ->where('product_id', $data['product_id'])
            ->whereHas('order', function ($q) use ($user) {
                $q->where('user_id', $user->id)->where('order_status', '!=', 'Cancelled');
            })
            ->exists();

        if (!$purchased) {
            return response()->json([
                'success' => false,
                'message' => 'You can only review products from your own orders.',
            ], 403);
        }

        $review = Review::updateOrCreate(
            ['product_id' => $data['product_id'], 'user_id' => $user->id],
            [
                'customer_name' => $user->name,
                'rating' => $data['rating'],
                'comment' => $data['comment'] ?? null,
                'is_verified' => true,
            ]
        );

        $this->refreshAggregates((int) $data['product_id']);

        return response()->json([
            'success' => true,
            'message' => 'Thanks! Your review is now live on the product page.',
            'rating' => $review->rating,
        ]);
    }

    protected function refreshAggregates(int $productId): void
    {
        $avg = (float) Review::where('product_id', $productId)->avg('rating');
        $count = Review::where('product_id', $productId)->count();

        Product::where('id', $productId)->update([
            'rating' => round($avg, 2),
            'reviews_count' => $count,
        ]);
    }
}