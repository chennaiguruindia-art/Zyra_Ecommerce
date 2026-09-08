<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class GlobalSettingController extends Controller
{
    public function index()
    {
        $products = Product::query()->with('category')->orderBy('id')->get();

        return view('global-setting', ['products' => $products]);
    }

    public function toggle(Request $request)
    {
        $data = Validator::make($request->all(), [
            'product_id' => 'required|integer',
            'field' => 'required|in:is_best_seller,is_trending',
            'value' => 'required|boolean',
        ])->validate();

        $product = Product::query()->findOrFail((int) $data['product_id']);
        $product->update([
            $data['field'] => filter_var($data['value'], FILTER_VALIDATE_BOOLEAN),
        ]);

        return response()->json([
            'success' => true,
            'product' => $product->fresh()->load('category')->toCatalogArray(),
        ]);
    }
}