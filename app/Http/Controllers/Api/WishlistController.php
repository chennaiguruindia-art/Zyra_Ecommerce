<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CustomerActivity;
use App\Models\Product;
use App\Models\Wishlist;
use App\Services\ActivityTracker;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WishlistController extends Controller
{
    protected array $withRelations = ['category', 'subcategory', 'sizes', 'colors', 'images'];

    /**
     * Get all wishlist products for the visitor.
     */
    public function index(Request $request): JsonResponse
    {
        $ids = $this->resolveWishlistIds($request);

        $products = Product::query()
            ->with($this->withRelations)
            ->whereIn('id', $ids)
            ->get()
            ->map->toCatalogArray()
            ->values();

        return response()->json([
            'success' => true,
            'count' => count($products),
            'products' => $products,
        ]);
    }

    /**
     * Get just the array of wishlist product IDs.
     */
    public function ids(Request $request): JsonResponse
    {
        return response()->json([
            'success' => true,
            'ids' => $this->resolveWishlistIds($request),
        ]);
    }

    /**
     * Toggle wishlist item (Add if not present, Remove if present).
     */
    public function toggle(Request $request): JsonResponse
    {
        $productId = (int) $request->input('product_id');
        if ($productId <= 0) {
            return response()->json(['success' => false, 'message' => 'Invalid product ID.'], 422);
        }

        $userId = Auth::id();
        $sessionId = $request->header('X-Device-ID') ?: $request->input('device_id') ?: session()->getId();

        $query = Wishlist::query();
        if ($userId) {
            $query->where('user_id', $userId);
        } else {
            $query->where('session_id', $sessionId);
        }

        $query->where('product_id', $productId);
        $exists = $query->exists();

        if ($exists) {
            $query->delete();
            $added = false;
            $message = 'Product removed from wishlist.';
        } else {
            Wishlist::create([
                'user_id' => $userId,
                'session_id' => $userId ? null : $sessionId,
                'product_id' => $productId,
            ]);
            $added = true;
            $message = 'Product added to wishlist.';
            ActivityTracker::track(CustomerActivity::WISHLIST_ADDED, $productId);
        }

        return response()->json([
            'success' => true,
            'added' => $added,
            'message' => $message,
            'ids' => $this->resolveWishlistIds($request),
        ]);
    }

    protected function resolveWishlistIds(Request $request): array
    {
        $userId = Auth::id();
        $sessionId = $request->header('X-Device-ID') ?: $request->input('device_id') ?: session()->getId();

        $query = Wishlist::query();
        if ($userId) {
            $query->where('user_id', $userId);
        } else {
            $query->where('session_id', $sessionId);
        }

        return $query->pluck('product_id')->map(fn ($id) => (int) $id)->values()->all();
    }
}
