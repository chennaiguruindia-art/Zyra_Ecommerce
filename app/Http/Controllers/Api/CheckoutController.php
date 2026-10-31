<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\Coupon;
use App\Models\CustomerActivity;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\RazorpayTransaction;
use App\Services\ActivityTracker;
use App\Services\RazorpayService;
use App\Services\ShiprocketService;
use App\Support\MetaConversionsApi;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class CheckoutController extends Controller
{
    protected const ONLINE_METHODS = ['upi', 'card', 'netbanking', 'online'];

    /**
     * Get checkout initialization data: user profile address, active cart items, pricing breakdown, razorpay key.
     */
    public function summary(Request $request): JsonResponse
    {
        $user = Auth::user();
        $cartRecord = $this->resolveCartRecord($request);
        $items = $this->extractItems($request, $cartRecord);

        $couponCode = $request->input('coupon_code');
        $pricing = $this->calculatePricing($items, $couponCode, $user?->id);

        return response()->json([
            'success' => true,
            'user' => $user ? [
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone_number,
                'address' => $user->address,
                'nearby_area' => $user->nearby_area,
                'city' => $user->district,
                'state' => $user->state,
                'pincode' => $user->pincode,
            ] : null,
            'items' => $pricing['line_items'],
            'subtotal' => $pricing['subtotal'],
            'discount' => $pricing['discount'],
            'coupon_code' => $pricing['coupon_code'],
            'shipping' => $pricing['shipping'],
            'total' => $pricing['total'],
            'razorpay_key' => app(RazorpayService::class)->keyId(),
        ]);
    }

    /**
     * Place an order (COD creates immediately; Online initializes Razorpay order).
     */
    public function placeOrder(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:100',
            'last_name' => 'nullable|string|max:100',
            'email' => 'required|email|max:150',
            'phone' => 'required|string|regex:/^[0-9]{10}$/',
            'address' => 'required|string|max:500',
            'apartment' => 'nullable|string|max:255',
            'city' => 'required|string|max:100',
            'state' => 'required|string|max:100',
            'pincode' => 'required|digits:6',
            'payment_method' => 'required|string|in:cod,upi,card,netbanking,online',
            'coupon_code' => 'nullable|string|max:50',
            'notes' => 'nullable|string|max:500',
            'items' => 'nullable|array',
        ]);

        $cartRecord = $this->resolveCartRecord($request);
        $items = $this->extractItems($request, $cartRecord);

        if (empty($items)) {
            return response()->json(['success' => false, 'message' => 'Your cart is empty.'], 422);
        }

        $userId = Auth::id();
        $pricing = $this->calculatePricing($items, $validated['coupon_code'] ?? null, $userId);

        // Verify stock before proceeding
        foreach ($pricing['line_items'] as $line) {
            $product = $line['product'];
            $available = $product->stockForSize($line['size']);
            if ($available <= 0) {
                return response()->json([
                    'success' => false,
                    'message' => "Size {$line['size']} of \"{$product->name}\" is out of stock.",
                ], 422);
            }
            if ($line['quantity'] > $available) {
                return response()->json([
                    'success' => false,
                    'message' => "Only {$available} unit(s) of \"{$product->name}\" in size {$line['size']} are available.",
                ], 422);
            }
        }

        $paymentMethod = $validated['payment_method'];

        if (in_array($paymentMethod, self::ONLINE_METHODS, true)) {
            $razorpay = app(RazorpayService::class);
            $receipt = 'RCPT-' . strtoupper(substr(uniqid(), -8));

            try {
                $rzpOrder = $razorpay->createOrder($pricing['total'], $receipt, [
                    'email' => $validated['email'],
                    'phone' => $validated['phone'],
                ]);

                return response()->json([
                    'success' => true,
                    'requires_payment' => true,
                    'razorpay_order_id' => $rzpOrder['id'],
                    'amount' => $rzpOrder['amount'],
                    'currency' => $rzpOrder['currency'],
                    'key_id' => $razorpay->keyId(),
                    'receipt' => $receipt,
                    'pricing' => [
                        'subtotal' => $pricing['subtotal'],
                        'discount' => $pricing['discount'],
                        'total' => $pricing['total'],
                    ],
                ]);
            } catch (\Throwable $e) {
                Log::error('Razorpay API Order creation failed', ['error' => $e->getMessage()]);
                return response()->json([
                    'success' => false,
                    'message' => 'Unable to initiate online payment. Please try Cash on Delivery or retry.',
                ], 500);
            }
        }

        // Cash on delivery
        $order = $this->createOrderInDb($validated, $pricing, [
            'payment_method' => 'cod',
            'payment_status' => 'pending',
        ], $cartRecord);

        return response()->json([
            'success' => true,
            'requires_payment' => false,
            'order_number' => $order->order_number,
            'total' => (float) $order->total,
            'message' => 'Order placed successfully.',
        ], 201);
    }

    /**
     * Verify online Razorpay payment from mobile app and create order.
     */
    public function verifyPayment(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'razorpay_order_id' => 'required|string',
            'razorpay_payment_id' => 'required|string',
            'razorpay_signature' => 'required|string',
            'order_data' => 'required|array',
            'order_data.first_name' => 'required|string',
            'order_data.last_name' => 'nullable|string',
            'order_data.email' => 'required|email',
            'order_data.phone' => 'required|string',
            'order_data.address' => 'required|string',
            'order_data.city' => 'required|string',
            'order_data.state' => 'required|string',
            'order_data.pincode' => 'required|string',
            'order_data.coupon_code' => 'nullable|string',
            'order_data.notes' => 'nullable|string',
            'order_data.items' => 'nullable|array',
        ]);

        $razorpay = app(RazorpayService::class);
        $signatureValid = $razorpay->verifySignature([
            'razorpay_order_id' => $validated['razorpay_order_id'],
            'razorpay_payment_id' => $validated['razorpay_payment_id'],
            'razorpay_signature' => $validated['razorpay_signature'],
        ]);

        if (!$signatureValid) {
            return response()->json([
                'success' => false,
                'message' => 'Payment signature verification failed.',
            ], 422);
        }

        $orderData = $validated['order_data'];
        $cartRecord = $this->resolveCartRecord($request);
        $items = $this->extractItems($request, $cartRecord, $orderData['items'] ?? null);

        $userId = Auth::id();
        $pricing = $this->calculatePricing($items, $orderData['coupon_code'] ?? null, $userId);

        $order = $this->createOrderInDb($orderData, $pricing, [
            'payment_method' => 'online',
            'payment_status' => 'paid',
            'razorpay_order_id' => $validated['razorpay_order_id'],
            'razorpay_payment_id' => $validated['razorpay_payment_id'],
            'razorpay_signature' => $validated['razorpay_signature'],
            'razorpay_amount' => $pricing['total'],
            'razorpay_status' => 'authorized',
        ], $cartRecord);

        // Record transaction
        try {
            RazorpayTransaction::create([
                'order_id' => $order->id,
                'razorpay_order_id' => $validated['razorpay_order_id'],
                'razorpay_payment_id' => $validated['razorpay_payment_id'],
                'razorpay_signature' => $validated['razorpay_signature'],
                'amount' => $pricing['total'],
                'currency' => 'INR',
                'status' => 'paid',
            ]);
        } catch (\Throwable $e) {
            Log::warning('Razorpay transaction record creation failed', ['error' => $e->getMessage()]);
        }

        return response()->json([
            'success' => true,
            'order_number' => $order->order_number,
            'total' => (float) $order->total,
            'message' => 'Payment verified and order placed successfully.',
        ]);
    }

    protected function createOrderInDb(array $data, array $pricing, array $paymentAttributes, Cart $cartRecord): Order
    {
        $order = DB::transaction(function () use ($data, $pricing, $paymentAttributes, $cartRecord) {
            $couponCode = $pricing['coupon_code'];
            if (!empty($couponCode)) {
                $coupon = Coupon::query()->where('code', $couponCode)->first();
                if ($coupon) {
                    $coupon->increment('used_count');
                }
            }

            $order = Order::create(array_merge([
                'order_number' => Order::generateOrderNumber(),
                'user_id' => Auth::id(),
                'customer_name' => trim($data['first_name'] . ' ' . ($data['last_name'] ?? '')),
                'customer_email' => $data['email'],
                'customer_phone' => $data['phone'],
                'shipping_address' => trim($data['address'] . (!empty($data['apartment']) ? ', ' . $data['apartment'] : '')),
                'city' => $data['city'],
                'state' => $data['state'],
                'pincode' => $data['pincode'],
                'payment_method' => $paymentAttributes['payment_method'] ?? 'cod',
                'payment_status' => $paymentAttributes['payment_status'] ?? 'pending',
                'subtotal' => $pricing['subtotal'],
                'discount' => $pricing['discount'],
                'shipping_cost' => $pricing['shipping'],
                'tax' => 0,
                'total' => $pricing['total'],
                'coupon_code' => $couponCode,
                'order_status' => 'Pending',
                'notes' => $data['notes'] ?? null,
            ], $paymentAttributes));

            foreach ($pricing['line_items'] as $line) {
                $product = $line['product'];
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'product_image' => $product->image,
                    'price' => $line['price'],
                    'quantity' => $line['quantity'],
                    'size' => $line['size'],
                    'color' => $line['color'],
                    'dupatta' => $line['dupatta'] ? 'With Dupatta' : 'Without Dupatta',
                    'total' => $line['total'],
                ]);

                // Decrement stock
                $newStock = max(0, $product->stock_units - $line['quantity']);
                $product->update([
                    'stock_units' => $newStock,
                    'in_stock' => $newStock > 0,
                ]);

                $product->decrementSizeStock($line['size'], $line['quantity']);
            }

            ActivityTracker::trackMany(
                CustomerActivity::PURCHASED,
                $order->items()->get(['product_id', 'size', 'color', 'quantity'])->all()
            );

            // Empty cart record
            $cartRecord->items = [];
            $cartRecord->status = Cart::STATUS_CONVERTED;
            $cartRecord->converted_at = now();
            $cartRecord->save();

            return $order;
        });

        // Non-blocking background integrations
        try {
            $this->pushToShiprocket($order);
        } catch (\Throwable $e) {
            Log::warning('Shiprocket auto-push API error: ' . $e->getMessage());
        }

        try {
            $this->sendOrderEmails($order);
        } catch (\Throwable $e) {
            Log::warning('Order confirmation email API error: ' . $e->getMessage());
        }

        return $order;
    }

    protected function pushToShiprocket(Order $order): void
    {
        $shiprocket = app(ShiprocketService::class);
        if ($shiprocket->isConfigured()) {
            $shiprocket->createCustomOrder($order);
        }
    }

    protected function sendOrderEmails(Order $order): void
    {
        $order->loadMissing('items');
        $adminEmail = config('mail.from.address', 'order@shopwithzyra.in');

        try {
            Mail::to($order->customer_email)->send(new \App\Mail\OrderConfirmationMail($order));
        } catch (\Throwable $e) {
            Log::warning('Customer order confirmation email failed: ' . $e->getMessage());
        }

        try {
            Mail::to($adminEmail)->send(new \App\Mail\AdminOrderNotificationMail($order));
        } catch (\Throwable $e) {
            Log::warning('Admin order notification email failed: ' . $e->getMessage());
        }
    }

    protected function extractItems(Request $request, Cart $cartRecord, ?array $overrideItems = null): array
    {
        $raw = $overrideItems ?: ($request->input('items') ?: ($cartRecord->items ?? []));
        $items = [];

        foreach ($raw as $entry) {
            $productId = $entry['product_id'] ?? $entry['id'] ?? null;
            if (!$productId) {
                continue;
            }

            $product = Product::query()->with(['sizes', 'colors', 'images'])->find($productId);
            if (!$product || !$product->is_visible) {
                continue;
            }

            $size = $entry['size'] ?? ($product->sizes->first()?->name ?? 'M');
            $color = $entry['color'] ?? ($product->colors->first()?->name ?? 'Standard');
            $withDupatta = filter_var($entry['dupatta'] ?? true, FILTER_VALIDATE_BOOLEAN);
            $qty = (int) ($entry['quantity'] ?? 1);
            $price = $withDupatta ? (float) $product->price : $product->withoutDupattaPrice();

            $items[] = [
                'product' => $product,
                'product_id' => $product->id,
                'name' => $product->name,
                'size' => $size,
                'color' => $color,
                'dupatta' => $withDupatta,
                'quantity' => $qty,
                'price' => $price,
                'total' => $price * $qty,
            ];
        }

        return $items;
    }

    protected function calculatePricing(array $items, ?string $couponCode, ?int $userId): array
    {
        $subtotal = 0.0;
        foreach ($items as $item) {
            $subtotal += $item['total'];
        }

        $discount = 0.0;
        $appliedCode = null;

        if ($couponCode) {
            $coupon = Coupon::query()->where('code', strtoupper(trim($couponCode)))->first();
            if ($coupon && $coupon->isValidFor($subtotal, $userId)) {
                $discount = $coupon->calculateDiscount($subtotal);
                $appliedCode = $coupon->code;
            }
        }

        $shipping = 0.0;
        $total = max(0, $subtotal - $discount + $shipping);

        return [
            'line_items' => $items,
            'subtotal' => round($subtotal, 2),
            'discount' => round($discount, 2),
            'coupon_code' => $appliedCode,
            'shipping' => $shipping,
            'total' => round($total, 2),
        ];
    }

    protected function resolveCartRecord(Request $request): Cart
    {
        $user = Auth::user();
        $deviceId = $request->header('X-Device-ID') ?: $request->input('device_id') ?: session()->getId();

        if ($user) {
            $cart = Cart::query()->where('user_id', $user->id)->where('status', Cart::STATUS_ACTIVE)->first();
            if (!$cart) {
                $cart = Cart::create([
                    'user_id' => $user->id,
                    'email' => $user->email,
                    'phone' => $user->phone_number,
                    'session_id' => $deviceId,
                    'status' => Cart::STATUS_ACTIVE,
                    'items' => [],
                    'last_activity_at' => now(),
                ]);
            }
            return $cart;
        }

        $cart = Cart::query()->where('session_id', $deviceId)->where('status', Cart::STATUS_ACTIVE)->first();
        if (!$cart) {
            $cart = Cart::create([
                'session_id' => $deviceId,
                'status' => Cart::STATUS_ACTIVE,
                'items' => [],
                'last_activity_at' => now(),
            ]);
        }
        return $cart;
    }
}
