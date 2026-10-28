<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Color;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\Size;
use App\Models\Subcategory;
use App\Models\StockReceipt;
use App\Support\ProductImageStorage;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

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
            'categories' => Category::query()->active()->with('subcategories')->get(),
        ]);
    }

    public function editProduct(int $id)
    {
        $product = $this->panelProducts()->with(['category', 'subcategory', 'sizes', 'colors', 'images'])->findOrFail($id);
        $catalog = $product->toCatalogArray();
        $catalog['image_paths'] = $product->images->pluck('image_path')->values()->all();
        if (empty($catalog['image_paths']) && $product->image) {
            $catalog['image_paths'] = [$product->image];
        }

        return view('seller.edit-product', [
            'productId' => $id,
            'product' => $catalog,
            'categories' => Category::query()->active()->with('subcategories')->get(),
        ]);
    }

    public function sales()
    {
        return view('seller.sales');
    }

    public function newOrders()
    {
        $pendingCount = Order::where('order_status', 'Pending')->count();
        $processingCount = Order::where('order_status', 'Processing')->count();
        $shippedCount = Order::where('order_status', 'Shipped')->count();

        return view('seller.new-orders', compact('pendingCount', 'processingCount', 'shippedCount'));
    }

    public function inventory()
    {
        return view('seller.inventory');
    }

    public function analytics()
    {
        $recentOrders = Order::query()
            ->with('items')
            ->latest()
            ->take(5)
            ->get();

        $grossRevenue = (float) Order::where('order_status', '!=', 'Cancelled')->sum('total');
        $totalOrdersCount = Order::count();
        $unitsDispatched = (int) Order::whereIn('order_status', ['Shipped', 'Delivered'])
            ->with('items')
            ->get()
            ->sum(function ($order) {
                return $order->items->sum('quantity') ?: 1;
            });
        $aov = $totalOrdersCount > 0 ? round($grossRevenue / $totalOrdersCount) : 0;

        return view('seller.analytics', compact(
            'recentOrders',
            'grossRevenue',
            'unitsDispatched',
            'aov',
            'totalOrdersCount'
        ));
    }

    public function settings()
    {
        return view('seller.settings');
    }

    public function apiProducts()
    {
        $products = $this->panelProducts()
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

            $fullAddress = trim(implode(', ', array_filter([
                $order->shipping_address,
                $order->city,
                $order->state ? ($order->state . ($order->pincode ? ' - ' . $order->pincode : '')) : $order->pincode,
            ])));

            return [
                'id' => $order->order_number,
                'db_id' => $order->id,
                'date' => $order->created_at?->format('d M Y'),
                'customer' => $order->customer_name,
                'email' => $order->customer_email,
                'phone' => $order->customer_phone,
                'shipping_address' => $order->shipping_address,
                'city' => $order->city,
                'state' => $order->state,
                'pincode' => $order->pincode,
                'full_address' => $fullAddress,
                'items' => $order->items->map(fn ($item) => $item->product_name . ' (' . $item->size . ($item->dupatta ? ', ' . $item->dupatta : '') . ') x ' . $item->quantity)->implode(', '),
                'items_detail' => $order->items->map(fn ($item) => [
                    'name' => $item->product_name,
                    'size' => $item->size,
                    'color' => $item->color,
                    'dupatta' => $item->dupatta,
                    'quantity' => $item->quantity,
                    'price' => (float) $item->price,
                    'image' => $item->image_url ?? null,
                ])->all(),
                'total' => (float) $order->total,
                'payment' => $paymentLabels[$order->payment_method] ?? $order->payment_method,
                'status' => $order->order_status,
                'awb_code' => $order->awb_code,
                'courier_name' => $order->courier_name,
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
        $product = $this->panelProducts()->findOrFail($id);
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
        $this->panelProducts()->findOrFail($id)->delete();

        return response()->json(['success' => true, 'message' => 'Product removed.']);
    }

    public function updateInventory(Request $request, int $id)
    {
        $product = $this->panelProducts()->findOrFail($id);
        $oldUnits = (int) $product->stock_units;

        if ($request->has('stock_units')) {
            $units = max(0, (int) $request->input('stock_units'));
            $product->stock_units = $units;
            $product->in_stock = $units > 0;

            if ($units > $oldUnits) {
                $added = $units - $oldUnits;
                $unitCost = (float) $product->price;
                StockReceipt::create([
                    'product_id' => $product->id,
                    'user_id' => Auth::id(),
                    'quantity' => $added,
                    'unit_cost' => $unitCost,
                    'total_cost' => round($added * $unitCost, 2),
                    'sizes_breakdown' => null,
                    'source' => 'inventory_adjustment',
                    'reference' => 'Manual Stock Update',
                ]);
            }
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

    /**
     * Per-size restock: add incoming units to individual sizes.
     * Payload: { quantities: { "S": 10, "M": 5, ... }, unit_cost: 499.00 }
     * Sizes with 0 or missing qty are skipped.
     */
    public function restockSizes(Request $request, int $id)
    {
        $product = $this->panelProducts()->with('sizes')->findOrFail($id);

        $data = Validator::make($request->all(), [
            'quantities' => 'required|array|min:1',
            'quantities.*' => 'nullable|integer|min:0|max:10000',
            'unit_cost' => 'nullable|numeric|min:0',
            'reference' => 'nullable|string|max:100',
        ])->validate();

        $updated = [];
        $sizesBreakdown = [];
        $totalAdded = 0;

        foreach ($data['quantities'] as $sizeName => $qty) {
            $qty = (int) $qty;
            if ($qty <= 0) {
                continue;
            }

            $size = $product->sizes->first(fn ($s) => $s->name === $sizeName);
            if (!$size) {
                continue;
            }

            // Legacy sizes inherit the product-level stock bucket.
            $current = $size->pivot->stock !== null ? (int) $size->pivot->stock : (int) $product->stock_units;
            $newStock = $current + $qty;

            $affected = $product->sizes()->updateExistingPivot($size->id, ['stock' => $newStock]);
            if ($affected === 0) {
                $product->sizes()->attach($size->id, ['stock' => $newStock]);
            }

            $updated[$sizeName] = $newStock;
            $sizesBreakdown[$sizeName] = $qty;
            $totalAdded += $qty;
        }

        if ($totalAdded === 0) {
            return response()->json([
                'success' => false,
                'message' => 'Enter a restock quantity for at least one size.',
            ], 422);
        }

        $product->stock_units = (int) $product->stock_units + $totalAdded;
        $product->in_stock = true;
        $product->save();

        $unitCost = isset($data['unit_cost']) && (float) $data['unit_cost'] > 0
            ? (float) $data['unit_cost']
            : (float) $product->price;

        $totalCost = round($totalAdded * $unitCost, 2);

        StockReceipt::create([
            'product_id' => $product->id,
            'user_id' => Auth::id(),
            'quantity' => $totalAdded,
            'unit_cost' => $unitCost,
            'total_cost' => $totalCost,
            'sizes_breakdown' => $sizesBreakdown,
            'source' => 'inventory_restock',
            'reference' => $data['reference'] ?? 'Inventory Restock',
        ]);

        return response()->json([
            'success' => true,
            'added' => $totalAdded,
            'amount' => $totalCost,
            'updated' => $updated,
            'message' => "Restocked {$totalAdded} unit(s) (₹" . number_format($totalCost, 2) . "): " . collect($updated)
                ->map(fn ($stock, $size) => "{$size} → {$stock}")
                ->implode(', '),
            'product' => $product->fresh()->load(['category', 'subcategory', 'sizes', 'colors', 'images'])->toCatalogArray(),
        ]);
    }

    public function updateOrderStatus(Request $request, $id)
    {
        $order = Order::query()->with('items')
            ->where('id', $id)
            ->orWhere('order_number', $id)
            ->firstOrFail();

        $status = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'status' => 'required|in:Pending,Processing,Shipped,Delivered,Cancelled',
        ])->validate()['status'];

        // Cancelling must go through cancelOrder() so the stock restore and
        // buyer email always happen — never set Cancelled directly here.
        if ($status === 'Cancelled') {
            return response()->json([
                'success' => false,
                'message' => 'Use the cancel endpoint to cancel an order.',
            ], 422);
        }

        // A cancelled order has already returned its stock; reactivating it
        // through this endpoint would leave inventory short. Keep it final.
        if ($order->order_status === 'Cancelled') {
            return response()->json([
                'success' => false,
                'message' => 'This order was cancelled and its stock was restored. It cannot be moved to another status.',
            ], 422);
        }

        $oldStatus = $order->order_status;
        $order->update(['order_status' => $status]);

        // Send fulfillment email notification to the customer
        if ($oldStatus !== $status && !empty($order->customer_email)) {
            $this->sendFulfillmentEmail($order, 'status_updated', $status, $order->awb_code, $order->courier_name);
        }

        return response()->json(['success' => true]);
    }

    /**
     * Cancel an order before dispatch (Pending / In Packing only).
     *
     * Restores the reserved stock (product total + per-size buckets) and
     * notifies the buyer. Once shipped, an order can no longer be cancelled
     * from here — the courier return has to be handled separately.
     */
    public function cancelOrder(Request $request, $id)
    {
        $order = Order::query()->with('items')
            ->where('id', $id)
            ->orWhere('order_number', $id)
            ->firstOrFail();

        // Idempotent: cancelling an already-cancelled order is a no-op success.
        if ($order->order_status === 'Cancelled') {
            return response()->json([
                'success' => true,
                'message' => 'Order is already cancelled.',
                'order_status' => 'Cancelled',
            ]);
        }

        if (!in_array($order->order_status, ['Pending', 'Processing'], true)) {
            return response()->json([
                'success' => false,
                'message' => "Only new or in-packing orders can be cancelled. This order is {$order->order_status}.",
            ], 422);
        }

        \Illuminate\Support\Facades\DB::transaction(function () use ($order) {
            // Return every unit this order reserved so it goes back on sale.
            // Hidden products are included: the visibility scope must not block
            // a stock correction.
            foreach ($order->items as $item) {
                if (!$item->product_id || $item->quantity <= 0) {
                    continue;
                }

                $product = Product::query()
                    ->withoutGlobalScope(\App\Models\Scopes\ProductVisibleScope::class)
                    ->with('sizes')
                    ->find($item->product_id);

                if (!$product) {
                    continue; // Product deleted since purchase — nothing to restore.
                }

                $restored = (int) $product->stock_units + (int) $item->quantity;
                $product->update([
                    'stock_units' => $restored,
                    'in_stock' => $restored > 0,
                ]);

                $product->incrementSizeStock($item->size, (int) $item->quantity);
            }

            $order->update(['order_status' => 'Cancelled']);
        });

        // Tell the buyer their order was cancelled (template subject handles 'cancelled').
        if (!empty($order->customer_email)) {
            $this->sendFulfillmentEmail($order, 'status_updated', 'Cancelled', $order->awb_code, $order->courier_name);
        }

        return response()->json([
            'success' => true,
            'message' => 'Order cancelled. Stock restored and the customer has been notified.',
            'order_status' => 'Cancelled',
        ]);
    }

    public function updateOrderTracking(Request $request, $id)
    {
        $order = Order::query()->with('items')
            ->where('id', $id)
            ->orWhere('order_number', $id)
            ->firstOrFail();

        $data = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'awb_code' => 'nullable|string|max:60',
            'courier_name' => 'nullable|string|max:120',
        ])->validate();

        $awb = trim((string) ($data['awb_code'] ?? ''));
        $courier = trim((string) ($data['courier_name'] ?? ''));

        if ($awb === '' && $courier === '') {
            return response()->json(['success' => false, 'message' => 'Enter at least an AWB number.'], 422);
        }

        $updates = [
            'awb_code' => $awb !== '' ? $awb : $order->awb_code,
            'courier_name' => $courier !== '' ? $courier : $order->courier_name,
        ];

        // When a courier begins shipping, mark the order as shipped for the customer.
        if ($awb !== '' && in_array($order->order_status, ['Pending', 'Processing'], true)) {
            $updates['order_status'] = 'Shipped';
            $updates['shipping_status'] = 'Shipped';
        }

        $order->update($updates);
        $order->refresh();

        // Send fulfillment email with AWB tracking number to the customer
        if (!empty($order->customer_email)) {
            $this->sendFulfillmentEmail($order, 'tracking_updated', $order->order_status, $order->awb_code, $order->courier_name);
        }

        return response()->json([
            'success' => true,
            'awb_code' => $order->awb_code,
            'courier_name' => $order->courier_name,
            'order_status' => $order->order_status,
        ]);
    }

    protected function sendFulfillmentEmail(Order $order, string $event, ?string $status, ?string $awb, ?string $courier): void
    {
        if (empty($order->customer_email)) {
            return;
        }

        $mailable = new \App\Mail\OrderFulfillmentMail(
            order: $order,
            event: $event,
            status: $status,
            awbCode: $awb,
            courierName: $courier
        );

        try {
            \Illuminate\Support\Facades\Mail::to($order->customer_email)->send($mailable);
            \Illuminate\Support\Facades\Log::info('Fulfillment email sent successfully', [
                'order_number' => $order->order_number,
                'email' => $order->customer_email,
            ]);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Fulfillment email failed on default SMTP: ' . $e->getMessage(), [
                'order_number' => $order->order_number,
            ]);
            // Fallback to smtp_ssl (port 465)
            try {
                \Illuminate\Support\Facades\Mail::mailer('smtp_ssl')->to($order->customer_email)->send($mailable);
                \Illuminate\Support\Facades\Log::info('Fulfillment email sent via smtp_ssl fallback', [
                    'order_number' => $order->order_number,
                ]);
            } catch (\Throwable $e2) {
                \Illuminate\Support\Facades\Log::error('Fulfillment email failed on smtp_ssl fallback as well: ' . $e2->getMessage(), [
                    'order_number' => $order->order_number,
                ]);
            }
        }
    }

    protected function sellerStats(): array
    {
        return [
            'productCount' => $this->panelProducts()->count(),
            'stockUnits' => (int) $this->panelProducts()->sum('stock_units'),
            'lowStock' => $this->panelProducts()->where('stock_units', '<=', 5)->count(),
            'revenue' => (float) Order::where('order_status', '!=', 'Cancelled')->sum('total'),
        ];
    }

    /**
     * Product query for seller/admin panels. Bypasses the global
     * ProductVisibleScope so products with visibility OFF remain fully
     * manageable (listed, edited, restocked, deleted) in the panel.
     */
    protected function panelProducts(): \Illuminate\Database\Eloquent\Builder
    {
        return Product::query()->withoutGlobalScope(\App\Models\Scopes\ProductVisibleScope::class);
    }

    /**
     * Toggle buyer-side visibility. The product stays in the DB and in the
     * seller panel; it disappears from every buyer-facing surface.
     */
    public function toggleVisibility(Request $request, int $id)
    {
        $product = $this->panelProducts()->findOrFail($id);
        $visible = $request->has('is_visible')
            ? $request->boolean('is_visible')
            : !$product->is_visible;

        $product->is_visible = $visible;
        $product->save();

        return response()->json([
            'success' => true,
            'is_visible' => (bool) $product->is_visible,
            'message' => $product->is_visible
                ? '"' . $product->name . '" is now visible to buyers.'
                : '"' . $product->name . '" is now hidden from buyers.',
        ]);
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
            'weight' => 'nullable|numeric|min:0|max:100',
            'sku' => $skuRule,
            'material' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'sizes' => 'nullable|array',
            'size_stock' => 'nullable|array',
            'colors' => 'nullable|array',
            'color_codes' => 'nullable|array',
            'color_codes.*' => 'nullable|string|max:20',
            'images' => 'nullable|array|max:4',
            'existing_images' => 'nullable|array|max:4',
            'existing_images.*' => 'nullable|string|max:2048',
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

        $existingImages = $data['existing_images'] ?? [];
        $allowedExisting = $product->exists
            ? $product->images()->pluck('image_path')->all()
            : [];
        $imageNames = $data['image_names'] ?? $request->input('image_names', []);
        $storedPaths = [];
        $slotIndexes = array_merge(array_keys((array) $images), array_keys((array) $existingImages));
        $slotCount = empty($slotIndexes) ? 0 : min(4, max($slotIndexes) + 1);
        for ($index = 0; $index < $slotCount; $index++) {
            $existingPath = is_array($existingImages) ? ($existingImages[$index] ?? null) : null;
            if ($existingPath !== null && in_array($existingPath, $allowedExisting, true)) {
                $storedPaths[] = $existingPath;
                continue;
            }

            $image = $images[$index] ?? null;
            if (empty($image)) {
                continue;
            }

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
            'weight' => isset($data['weight']) && $data['weight'] !== '' ? (float) $data['weight'] : 0.5,
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

        $sizeNames = array_values(array_filter((array) ($data['sizes'] ?? ['S', 'M', 'L'])));
        $sizeStock = collect((array) ($data['size_stock'] ?? []))
            ->mapWithKeys(fn ($stock, $size) => [$size => max(0, (int) $stock)])
            ->all();
        $sizeIds = [];
        $sizePivots = [];
        $computedStock = null;

        foreach ($sizeNames as $sz) {
            $sizeModel = Size::firstOrCreate(['name' => $sz]);
            $sizeIds[] = $sizeModel->id;

            // Only persist per-size stock for sizes that were given an explicit value.
            $stockVal = is_array($sizeStock) ? ($sizeStock[$sz] ?? null) : null;
            if ($stockVal !== null && $stockVal !== '') {
                $sizePivots[$sizeModel->id] = ['stock' => max(0, (int) $stockVal)];
            }
        }

        if ($sizeNames && $sizeStock) {
            $product->sizes()->sync($sizePivots);
            $computedStock = array_sum(array_column($sizePivots, 'stock'));
            $product->stock_units = $computedStock;
            $product->in_stock = $computedStock > 0;
            $product->save();
        } else {
            $product->sizes()->sync($sizeIds);
        }

        $colorNames = $data['colors'] ?? ['Pink', 'White'];
        $colorCodes = array_values($data['color_codes'] ?? []);
        $colorIds = [];
        foreach ($colorNames as $index => $clr) {
            $colorModel = Color::firstOrCreate(['name' => $clr]);
            $hexCode = trim((string) ($colorCodes[$index] ?? ''));
            if ($hexCode !== '' && $colorModel->hex_code !== $hexCode) {
                $colorModel->update(['hex_code' => $hexCode]);
            }
            $colorIds[] = $colorModel->id;
        }
        $product->colors()->sync($colorIds);

        return $product;
    }
}
