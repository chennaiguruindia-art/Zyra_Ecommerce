<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Wishlist;
use App\Models\CustomerActivity;
use App\Services\ActivityTracker;
use Illuminate\Http\Request;

class WishlistController extends Controller
{
    /**
     * Return full product data for an array of IDs (used by wishlist page).
     */
    public function items(Request $request)
    {
        // Accept either ?ids=1,2,3 or a DB-backed lookup for current user
        $ids = $request->input('ids');

        if ($ids !== null) {
            // Legacy / JS-driven call with explicit IDs
            if (is_string($ids)) {
                $ids = array_filter(array_map('intval', explode(',', $ids)));
            }
        } else {
            // No IDs supplied — load from DB for the current visitor
            $ids = Wishlist::currentIds();
        }

        $products = Product::query()
            ->with(['category', 'subcategory', 'sizes', 'colors', 'images'])
            ->whereIn('id', $ids)
            ->get()
            ->map->toCatalogArray()
            ->all();

        return response()->json(['products' => $products]);
    }

    /**
     * Return just the product IDs saved by the current visitor.
     * Called by wishlist.js on page load to sync heart icons.
     */
    public function ids()
    {
        return response()->json(['ids' => Wishlist::currentIds()]);
    }

    /**
     * Add or remove a product from the wishlist (DB-backed).
     */
    public function toggle(Request $request)
    {
        $productId = (int) $request->input('product_id');

        if ($productId <= 0) {
            return response()->json(['success' => false, 'message' => 'Invalid product.'], 422);
        }

        $query = Wishlist::forCurrentUser()->where('product_id', $productId);
        $exists = $query->exists();

        if ($exists) {
            $query->delete();
            $added = false;
        } else {
            Wishlist::create([
                'user_id'    => auth()->check() ? auth()->id() : null,
                'session_id' => auth()->check() ? null : session()->getId(),
                'product_id' => $productId,
            ]);
            $added = true;
        }

        // Track the activity event (this was already working before)
        ActivityTracker::track(
            $added ? CustomerActivity::WISHLIST_ADDED : CustomerActivity::WISHLIST_REMOVED,
            $productId
        );

        $ids = Wishlist::currentIds();

        return response()->json(['success' => true, 'added' => $added, 'ids' => $ids]);
    }
}
