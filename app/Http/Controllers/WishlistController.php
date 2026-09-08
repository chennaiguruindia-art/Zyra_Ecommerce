<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class WishlistController extends Controller
{
    public function items(Request $request)
    {
        $ids = $request->input('ids', []);
        if (is_string($ids)) {
            $ids = array_filter(explode(',', $ids));
        }

        $products = Product::query()
            ->with(['category', 'subcategory', 'sizes', 'colors', 'images'])
            ->whereIn('id', $ids)
            ->get()
            ->map->toCatalogArray()
            ->all();

        return response()->json(['products' => $products]);
    }

    public function toggle(Request $request)
    {
        $id = (int) $request->input('product_id');
        $wishlist = session()->get('wishlist', []);

        if (in_array($id, $wishlist, true)) {
            $wishlist = array_values(array_diff($wishlist, [$id]));
            $added = false;
        } else {
            $wishlist[] = $id;
            $added = true;
        }

        session(['wishlist' => $wishlist]);

        return response()->json(['success' => true, 'added' => $added, 'ids' => $wishlist]);
    }
}
