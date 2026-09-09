<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Color;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\Size;
use App\Models\Subcategory;
use App\Support\ProductImageStorage;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SellerController extends Controller
{
    public function dashboard()
    {
        return view('seller.dashboard', $this->sellerStats());
    }

    public function products()
    {
        return view('seller.products');
    }

    public function addProduct()
    {
        return view('seller.add-product', [
            'categories' => Category::query()->active()->get(),
        ]);
    }

    public function editProduct(int $id)
    {
        $product = Product::query()->with(['category', 'subcategory', 'sizes', 'colors', 'images'])->findOrFail($id);

        return view('seller.edit-product', [
            'productId' => $id,
            'product' => $product->toCatalogArray(),
            'categories' => Category::query()->active()->get(),
        ]);
    }

    public function sales()
    {
        return view('seller.sales');
    }

    public function inventory()
    {
        return view('seller.inventory');
    }

    public function analytics()
    {
        return view('seller.analytics');
    }

    public function settings()
    {
        return view('seller.settings');
    }

    public function apiProducts()
    {
        $products = Product::query()
            ->with(['category', 'subcategory', 'sizes', 'colors', 'images'])
            ->latest()
            ->get()
            ->map(function (Product $product) {
                $data = $product->toCatalogArray();
                $data['stock'] = (bool) $product->in_stock;
                $data['status'] = $product->in_stock ? 'active' : 'inactive';
                return $data;
            })
            ->all();

        return response()->json($products);
    }

    public function apiOrders()
    {
        $orders = Order::query()->with('items')->latest()->get()->map(function (Order $order) {
            $paymentLabels = [
                'cod' => 'Cash on Delivery',
                'upi' => 'UPI',
                'card' => 'Card',
                'netbanking' => 'Net Banking',
            ];

            return [
                'id' => $order->order_number,
                'db_id' => $order->id,
                'date' => $order->created_at?->format('d M Y'),
                'customer' => $order->customer_name,
                'phone' => $order->customer_phone,
                'city' => $order->city,
                'items' => $order->items->map(fn ($item) => $item->product_name . ' (' . $item->size . ') x ' . $item->quantity)->implode(', '),
                'total' => (float) $order->total,
                'payment' => $paymentLabels[$order->payment_method] ?? $order->payment_method,
                'status' => $order->order_status,
            ];
        })->all();

        return response()->json($orders);
    }

    public function storeProduct(Request $request)
    {
        $data = $this->validateProduct($request);
        $product = $this->persistProduct(new Product(), $data, $request);

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Product "' . $product->name . '" added successfully!',
                'product' => $product->load(['category', 'subcategory', 'sizes', 'colors', 'images'])->toCatalogArray(),
            ]);
        }

        return redirect()->route('seller.products')->with('success', 'Product "' . $product->name . '" added successfully!');
    }

    public function updateProduct(Request $request, int $id)
    {
        $product = Product::query()->findOrFail($id);
        $data = $this->validateProduct($request, $product->id);
        $product = $this->persistProduct($product, $data, $request);

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Product updated successfully!',
                'product' => $product->load(['category', 'subcategory', 'sizes', 'colors', 'images'])->toCatalogArray(),
            ]);
        }

        return redirect()->route('seller.products')->with('success', 'Product updated successfully!');
    }

    public function destroyProduct(int $id)
    {
        Product::query()->findOrFail($id)->delete();

        return response()->json(['success' => true, 'message' => 'Product removed.']);
    }

    public function updateInventory(Request $request, int $id)
    {
        $product = Product::query()->findOrFail($id);
        if ($request->has('stock_units')) {
            $units = max(0, (int) $request->input('stock_units'));
            $product->stock_units = $units;
            $product->in_stock = $units > 0;
        }
        if ($request->has('in_stock') && !$request->has('stock_units')) {
            $product->in_stock = $request->boolean('in_stock');
            if (!$product->in_stock) {
                $product->stock_units = 0;
            } elseif ($product->stock_units === 0) {
                $product->stock_units = 15;
            }
        }
        $product->save();

        return response()->json(['success' => true, 'product' => $product->fresh()->toCatalogArray()]);
    }

    public function updateOrderStatus(Request $request, int $id)
    {
        $order = Order::query()->findOrFail($id);
        $status = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'status' => 'required|in:Pending,Processing,Shipped,Delivered,Cancelled',
        ])->validate()['status'];

        $order->update(['order_status' => $status]);

        return response()->json(['success' => true]);
    }

    protected function sellerStats(): array
    {
        return [
            'productCount' => Product::count(),
            'stockUnits' => (int) Product::sum('stock_units'),
            'lowStock' => Product::where('stock_units', '<=', 5)->count(),
            'revenue' => (float) Order::where('order_status', '!=', 'Cancelled')->sum('total'),
        ];
    }

    protected function validateProduct(Request $request, ?int $ignoreId = null): array
    {
        $input = $request->all();
        if (isset($input['old_price']) && $input['old_price'] === '') {
            $input['old_price'] = null;
        }
        if (isset($input['sku']) && trim((string) $input['sku']) === '') {
            $input['sku'] = null;
        }

        $skuRule = 'nullable|string|max:80';
        if (!empty($input['sku'])) {
            $skuRule .= '|unique:products,sku' . ($ignoreId ? ',' . $ignoreId : '');
        }

        return \Illuminate\Support\Facades\Validator::make($input, [
            'name' => 'required|string|max:255',
            'category' => 'required|string',
            'subcategory' => 'nullable|string',
            'price' => 'required|numeric|min:1',
            'old_price' => 'nullable|numeric|min:0',
            'stock_units' => 'required|integer|min:0',
            'sku' => $skuRule,
            'material' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'sizes' => 'nullable|array',
            'colors' => 'nullable|array',
            'images' => 'nullable|array|max:4',
            'image_names' => 'nullable|array|max:4',
            'image_names.*' => 'nullable|string|max:255',
        ])->validate();
    }

    protected function persistProduct(Product $product, array $data, Request $request): Product
    {
        $categorySlug = Str::slug($data['category']);
        $category = Category::query()
            ->where('slug', $categorySlug)
            ->orWhere('name', $data['category'])
            ->first();

        if (!$category) {
            $category = Category::create([
                'name' => ucfirst($data['category']),
                'slug' => $categorySlug ?: 'category-' . Str::random(4),
                'is_active' => true,
                'sort_order' => Category::count() + 1,
            ]);
        }

        $subcategory = null;
        if (!empty($data['subcategory'])) {
            $subcategory = Subcategory::query()->firstOrCreate(
                [
                    'category_id' => $category->id,
                    'slug' => Str::slug($data['subcategory']),
                ],
                ['name' => $data['subcategory']]
            );
        }

        $oldPrice = !empty($data['old_price']) ? (float) $data['old_price'] : null;
        $price = (float) $data['price'];
        $discount = ($oldPrice && $oldPrice > $price) ? (int) round((($oldPrice - $price) / $oldPrice) * 100) : 0;

        $images = $data['images'] ?? [];
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $index => $file) {
                if ($file) {
                    $images[$index] = $file;
                }
            }
        }

        $imageNames = $data['image_names'] ?? $request->input('image_names', []);
        $storedPaths = [];
        foreach ($images as $index => $image) {
            $originalName = is_array($imageNames) ? ($imageNames[$index] ?? null) : null;
            $path = ProductImageStorage::store($image, 'products', $index, $originalName);
            if ($path) {
                $storedPaths[] = $path;
            }
        }

        $cover = $storedPaths[0] ?? $product->image ?? 'https://images.unsplash.com/photo-1534126511673-b6899657816a?auto=format&fit=crop&w=800&q=80';

        $product->fill([
            'category_id' => $category->id,
            'subcategory_id' => $subcategory?->id,
            'name' => $data['name'],
            'slug' => Str::slug($data['name']) . '-' . ($product->id ?: Str::random(4)),
            'sku' => !empty($data['sku']) ? $data['sku'] : ('ZYR-NEW-' . strtoupper(Str::random(5))),
            'price' => $price,
            'old_price' => $oldPrice,
            'discount' => $discount,
            'stock_units' => (int) $data['stock_units'],
            'in_stock' => ((int) $data['stock_units']) > 0,
            'image' => $cover,
            'material' => $data['material'] ?? $product->material,
            'description' => $data['description'] ?? $product->description,
            'badge' => $product->badge ?: 'New',
        ]);
        $product->save();

        if (!$product->wasRecentlyCreated && str_ends_with($product->slug, (string) $product->id) === false) {
            $product->update(['slug' => Str::slug($product->name) . '-' . $product->id]);
        }

        if (!empty($storedPaths)) {
            $product->images()->delete();
            foreach ($storedPaths as $index => $path) {
                ProductImage::create([
                    'product_id' => $product->id,
                    'image_path' => $path,
                    'is_cover' => $index === 0,
                    'sort_order' => $index,
                ]);
            }
        }

        $sizeNames = $data['sizes'] ?? ['S', 'M', 'L'];
        $sizeIds = [];
        foreach ($sizeNames as $sz) {
            $sizeModel = Size::firstOrCreate(['name' => $sz]);
            $sizeIds[] = $sizeModel->id;
        }
        $product->sizes()->sync($sizeIds);

        $colorNames = $data['colors'] ?? ['Pink', 'White'];
        $colorIds = [];
        foreach ($colorNames as $clr) {
            $colorModel = Color::firstOrCreate(['name' => $clr]);
            $colorIds[] = $colorModel->id;
        }
        $product->colors()->sync($colorIds);

        return $product;
    }
}
