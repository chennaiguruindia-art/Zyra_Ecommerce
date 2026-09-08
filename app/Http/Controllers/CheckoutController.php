<?php

namespace App\Http\Controllers;

use App\Models\Coupon;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\RazorpayTransaction;
use App\Services\RazorpayService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CheckoutController extends Controller
{
    protected const ONLINE_METHODS = ['upi', 'card', 'netbanking'];

    public function index()
    {
        if (!Auth::check()) {
            return redirect()->route('login')->with('message', 'Please login to proceed to checkout.');
        }

        $user = Auth::user();
        $latestOrder = Order::where('user_id', $user->id)->latest()->first();

        $nameParts = explode(' ', trim($user->name));
        $firstName = $nameParts[0] ?? '';
        $lastName = count($nameParts) > 1 ? implode(' ', array_slice($nameParts, 1)) : '';

        $customer = [
            'first_name' => $firstName,
            'last_name' => $lastName,
            'email' => $user->email,
            'phone' => $user->phone_number ?? $latestOrder?->customer_phone ?? '',
            'address' => $user->address ?? $latestOrder?->shipping_address ?? '',
            'apartment' => $user->nearby_area ?? '',
            'city' => $user->district ?? $latestOrder?->city ?? '',
            'state' => $user->state ?? $latestOrder?->state ?? '',
            'pincode' => $user->pincode ?? $latestOrder?->pincode ?? '',
        ];

        $cartItems = (new CartController())->resolvedCart();
        $cartSubtotal = 0;
        foreach ($cartItems as $item) {
            $cartSubtotal += ((float) ($item['price'] ?? 0)) * ((int) ($item['quantity'] ?? 1));
        }
        $cartShipping = 0;
        $cartTotal = $cartSubtotal + $cartShipping;

        return view('checkout', [
            'user' => $user,
            'customer' => $customer,
            'cartItems' => $cartItems,
            'cartSubtotal' => $cartSubtotal,
            'cartShipping' => $cartShipping,
            'cartTotal' => $cartTotal,
            'razorpayKey' => app(RazorpayService::class)->keyId(),
        ]);
    }

    public function placeOrder(Request $request)
    {
        $data = $this->validatedOrderData($request);

        $paymentMethod = $data['payment_method'];

        // Online payments: create a Razorpay order and defer DB order creation until payment is verified.
        if (in_array($paymentMethod, self::ONLINE_METHODS, true)) {
            return $this->initiateOnlinePayment($data);
        }

        // Cash on Delivery: create the order directly.
        $order = $this->createDatabaseOrder($data, [], $request->input('notes'));

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'requires_payment' => false,
                'order_number' => $order->order_number,
                'redirect' => route('order.success', $order->order_number),
            ]);
        }

        return redirect()->route('order.success', $order->order_number);
    }

    public function verifyPayment(Request $request)
    {
        $data = $request->validate([
            'razorpay_order_id' => 'required|string',
            'razorpay_payment_id' => 'required|string',
            'razorpay_signature' => 'required|string',
        ]);

        $pending = session()->get('zyra_pending_order');
        if (!$pending || ($pending['_rzp_order_id'] ?? null) !== $data['razorpay_order_id']) {
            return response()->json([
                'success' => false,
                'message' => 'Payment session expired. Please try again.',
            ], 422);
        }

        $razorpay = app(RazorpayService::class);

        if (!$razorpay->verifySignature($data)) {
            return response()->json([
                'success' => false,
                'message' => 'Payment verification failed. Your order was not placed.',
            ], 422);
        }

        try {
            $payment = $razorpay->fetchPayment($data['razorpay_payment_id']);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'We could not confirm the payment with Razorpay. Please try again.',
            ], 422);
        }

        $expectedAmount = (int) round((float) ($pending['_total'] ?? 0) * 100);
        if (($payment['order_id'] ?? null) !== $data['razorpay_order_id']) {
            return response()->json([
                'success' => false,
                'message' => 'Payment does not belong to this order.',
            ], 422);
        }

        if ((int) ($payment['amount'] ?? 0) !== $expectedAmount) {
            return response()->json([
                'success' => false,
                'message' => 'Payment amount mismatch. Please contact support.',
            ], 422);
        }

        if (($payment['status'] ?? null) === 'authorized') {
            try {
                $payment = $razorpay->capturePayment($data['razorpay_payment_id'], $expectedAmount);
            } catch (\Throwable $e) {
                return response()->json([
                    'success' => false,
                    'message' => 'Payment was authorized but could not be captured. Please try again.',
                ], 422);
            }
        }

        if (($payment['status'] ?? null) !== 'captured') {
            return response()->json([
                'success' => false,
                'message' => 'Payment is not captured yet. Please complete the payment or try again.',
            ], 422);
        }

        $order = $this->createDatabaseOrder($pending, [
            'razorpay_order_id' => $data['razorpay_order_id'],
            'razorpay_payment_id' => $data['razorpay_payment_id'],
            'razorpay_signature' => $data['razorpay_signature'],
            'razorpay_amount' => $expectedAmount,
            'razorpay_status' => $payment['status'] ?? 'captured',
            'payment_status' => 'paid',
        ]);

        RazorpayTransaction::where('razorpay_order_id', $data['razorpay_order_id'])
            ->update([
                'order_id' => $order->id,
                'razorpay_payment_id' => $data['razorpay_payment_id'],
                'razorpay_signature' => $data['razorpay_signature'],
                'status' => $payment['status'] ?? 'captured',
                'method' => $payment['method'] ?? null,
                'fee' => $payment['fee'] ?? null,
                'tax' => $payment['tax'] ?? null,
                'raw_response' => $payment,
            ]);

        session()->forget('zyra_pending_order');

        return response()->json([
            'success' => true,
            'order_number' => $order->order_number,
            'redirect' => route('order.success', $order->order_number),
        ]);
    }

    /**
     * Validate the shared order payload.
     */
    protected function validatedOrderData(Request $request): array
    {
        return $request->validate([
            'first_name' => 'required|string|max:100',
            'last_name' => 'nullable|string|max:100',
            'email' => 'required|email',
            'phone' => 'required|string|max:20',
            'address' => 'required|string|max:500',
            'apartment' => 'nullable|string|max:200',
            'city' => 'required|string|max:100',
            'state' => 'required|string|max:100',
            'pincode' => 'required|string|max:10',
            'payment_method' => 'required|in:cod,upi,card,netbanking',
            'items' => 'nullable|array',
            'items.*.id' => 'required_with:items|integer',
            'items.*.quantity' => 'nullable|integer|min:1',
            'items.*.size' => 'nullable|string',
            'items.*.color' => 'nullable|string',
            'coupon_code' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);
    }

    /**
     * Create a Razorpay order, store the pending payload in the session, and return the widget config.
     */
    protected function initiateOnlinePayment(array $data)
    {
        $totals = $this->calculateTotals($data);

        if ($totals['total'] <= 0) {
            return response()->json([
                'success' => false,
                'message' => 'Order total must be greater than zero.',
            ], 422);
        }

        $razorpay = app(RazorpayService::class);

        try {
            $paymentOrder = $razorpay->createOrder($totals['total'], $totals['receipt'], [
                'user_email' => $data['email'],
                'user_phone' => $data['phone'],
            ]);
        } catch (\Throwable $e) {
            Log::warning('Razorpay order creation failed', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'The payment gateway is unreachable. Please try again in a moment.',
            ], 502);
        }

        session()->put('zyra_pending_order', array_merge($data, [
            '_rzp_order_id' => $paymentOrder['id'],
            '_total' => $totals['total'],
        ]));

        RazorpayTransaction::create([
            'razorpay_order_id' => $paymentOrder['id'],
            'amount' => (int) round($totals['total'] * 100),
            'currency' => $razorpay->currency(),
            'status' => 'created',
            'raw_response' => $paymentOrder,
        ]);

        $amountPaise = (int) round($totals['total'] * 100);

        return response()->json([
            'success' => true,
            'requires_payment' => true,
            'razorpay' => [
                'key_id' => $razorpay->keyId(),
                'order_id' => $paymentOrder['id'],
                'amount' => $amountPaise,
                'currency' => $razorpay->currency(),
                'name' => 'ZYRA Fashion',
                'description' => 'Payment for order ' . $totals['receipt'],
                'prefill' => [
                    'name' => trim($data['first_name'] . ' ' . ($data['last_name'] ?? '')),
                    'email' => $data['email'],
                    'contact' => $data['phone'],
                ],
                'theme' => ['color' => '#18181b'],
            ],
        ]);
    }

    /**
     * Compute subtotal, discount, shipping and total for cart + coupon data.
     */
    protected function calculateTotals(array $data): array
    {
        $cartItems = array_values(session()->get('cart', []));
        if (empty($cartItems) && !empty($data['items'])) {
            (new CartController())->replaceCartFromItems($data['items']);
            $cartItems = array_values(session()->get('cart', []));
        }

        $subtotal = 0;
        foreach ($cartItems as $item) {
            $product = Product::query()->find($item['id'] ?? 0);
            if (!$product) {
                continue;
            }
            $subtotal += ((float) $product->price) * (int) ($item['quantity'] ?? 1);
        }

        if ($subtotal <= 0) {
            abort(422, 'Your cart is empty or products are no longer available.');
        }

        $couponCode = strtoupper(trim((string) ($data['coupon_code'] ?? '')));
        $discount = 0;
        if ($couponCode !== '') {
            $coupon = Coupon::query()->where('code', $couponCode)->first();
            if ($coupon && $coupon->isValidFor($subtotal)) {
                $discount = $coupon->calculateDiscount($subtotal);
            }
        }

        $shipping = 0;
        $total = max(0, $subtotal - $discount + $shipping);

        return [
            'subtotal' => $subtotal,
            'discount' => $discount,
            'shipping' => $shipping,
            'total' => $total,
            'coupon_code' => $couponCode,
            'receipt' => Order::generateOrderNumber(),
        ];
    }

    /**
     * Persist the order and its line items, decrement stock, and clear the cart.
     */
    protected function createDatabaseOrder(array $data, array $razorpay = [], ?string $notes = null): Order
    {
        return DB::transaction(function () use ($data, $razorpay, $notes) {
            $cartItems = array_values(session()->get('cart', []));
            if (empty($cartItems) && !empty($data['items'])) {
                (new CartController())->replaceCartFromItems($data['items']);
                $cartItems = array_values(session()->get('cart', []));
            }

            $subtotal = 0;
            $lineItems = [];

            foreach ($cartItems as $item) {
                $product = Product::query()->find($item['id'] ?? 0);
                if (!$product) {
                    continue;
                }

                $qty = (int) ($item['quantity'] ?? 1);
                $price = (float) $product->price;
                $lineTotal = $price * $qty;
                $subtotal += $lineTotal;

                $lineItems[] = [
                    'product' => $product,
                    'quantity' => $qty,
                    'size' => $item['size'] ?? null,
                    'color' => $item['color'] ?? null,
                    'price' => $price,
                    'total' => $lineTotal,
                ];
            }

            if (empty($lineItems)) {
                abort(422, 'Your cart is empty or products are no longer available.');
            }

            $discount = 0;
            $couponCode = strtoupper(trim((string) ($data['coupon_code'] ?? '')));
            if ($couponCode !== '') {
                $coupon = Coupon::query()->where('code', $couponCode)->first();
                if ($coupon && $coupon->isValidFor($subtotal)) {
                    $discount = $coupon->calculateDiscount($subtotal);
                    $coupon->increment('used_count');
                }
            }

            $shipping = 0;
            $total = max(0, $subtotal - $discount + $shipping);

            $paymentMethod = $data['payment_method'];
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
                'payment_method' => $paymentMethod,
                'payment_status' => $razorpay['payment_status'] ?? ($paymentMethod === 'cod' ? 'pending' : 'paid'),
                'subtotal' => $subtotal,
                'discount' => $discount,
                'shipping_cost' => $shipping,
                'tax' => 0,
                'total' => $total,
                'coupon_code' => $couponCode !== '' ? $couponCode : null,
                'order_status' => 'Pending',
                'notes' => $notes ?? $data['notes'] ?? null,
            ], $razorpay));

            foreach ($lineItems as $line) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $line['product']->id,
                    'product_name' => $line['product']->name,
                    'product_image' => $line['product']->image,
                    'price' => $line['price'],
                    'quantity' => $line['quantity'],
                    'size' => $line['size'],
                    'color' => $line['color'],
                    'total' => $line['total'],
                ]);

                $newStock = max(0, $line['product']->stock_units - $line['quantity']);
                $line['product']->update([
                    'stock_units' => $newStock,
                    'in_stock' => $newStock > 0,
                ]);
            }

            session()->forget(['cart', 'coupon']);

            return $order;
        });
    }
}