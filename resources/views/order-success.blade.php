@extends('layouts.app')

@section('title', 'Order Confirmed! | ZYRA Fashion')
@section('meta_description', 'Your ZYRA fashion order has been successfully placed.')

@section('content')

<div class="container py-5 my-3">
    <div class="row justify-content-center">
        <div class="col-lg-8 text-center">
            
            <!-- Animated Checkmark Icon -->
            <div class="zyra-success-icon">
                <i class="bi bi-check-lg"></i>
            </div>

            <h1 class="display-6 fw-bold mb-2">Order Placed Successfully!</h1>
            <p class="text-muted mb-4 fs-5">
                Thank you for shopping with ZYRA. We've sent an order confirmation with tracking details to your email.
            </p>

            @php
                $orderNumber = isset($order) ? $order->order_number : 'ZYRA-849201';
                $orderDate = isset($order) ? $order->created_at->format('F d, Y') : date('F d, Y');
                $paymentMethodMap = [
                    'cod' => 'Cash on Delivery',
                    'upi' => 'UPI',
                    'card' => 'Credit / Debit Card',
                    'netbanking' => 'Net Banking'
                ];
                $paymentMethod = isset($order) ? ($paymentMethodMap[$order->payment_method] ?? ucfirst($order->payment_method)) : 'Cash on Delivery';
                $totalAmount = isset($order) ? $order->total : 1499;
                $customerName = isset($order) ? $order->customer_name : 'Aditi Sharma';
                $shippingAddress = isset($order) ? '<strong>' . e($order->customer_name) . '</strong><br>' . e($order->shipping_address) . '<br>' . e($order->city) . ', ' . e($order->state) . ' - ' . e($order->pincode) . '<br>Phone: ' . e($order->customer_phone) : '<strong>Aditi Sharma</strong><br>Flat 402, Lotus Residency, MG Road, Tower B<br>Bengaluru, Karnataka - 560001<br>Phone: 9876543210';
                $items = isset($order) ? $order->items : null;
            @endphp

            <!-- Order Details Card -->
            <div class="card border-0 shadow-sm text-start p-4 mb-4" style="background-color: var(--zyra-bg-beige);">
                <div class="row g-3 border-bottom pb-3 mb-3">
                    <div class="col-6 col-md-3">
                        <small class="text-muted text-uppercase fw-semibold" style="font-size: 0.72rem;">Order ID</small>
                        <div id="successOrderId" class="fw-bold text-dark fs-6">{{ $orderNumber }}</div>
                    </div>
                    <div class="col-6 col-md-3">
                        <small class="text-muted text-uppercase fw-semibold" style="font-size: 0.72rem;">Order Date</small>
                        <div id="successOrderDate" class="fw-bold text-dark fs-6">{{ $orderDate }}</div>
                    </div>
                    <div class="col-6 col-md-3">
                        <small class="text-muted text-uppercase fw-semibold" style="font-size: 0.72rem;">Payment Method</small>
                        <div id="successPaymentMethod" class="fw-bold text-dark fs-6">{{ $paymentMethod }}</div>
                    </div>
                    <div class="col-6 col-md-3">
                        <small class="text-muted text-uppercase fw-semibold" style="font-size: 0.72rem;">Total Amount</small>
                        <div id="successTotal" class="fw-bold text-dark fs-5 text-success">₹{{ number_format($totalAmount, 0) }}</div>
                    </div>
                </div>

                <!-- Shipping Address Snapshot -->
                <div class="mb-3">
                    <h6 class="fw-bold mb-2 small text-uppercase text-muted">Delivery Address</h6>
                    <div id="successAddress" class="small text-dark">
                        {!! $shippingAddress !!}
                    </div>
                </div>

                @if(isset($order) && $order->awb_code)
                    <div class="d-flex flex-wrap align-items-center gap-2 bg-white p-3 rounded border mb-3">
                        <i class="bi bi-truck text-muted fs-5"></i>
                        <div class="small">
                            @if($order->courier_name)
                                <span class="fw-semibold">{{ $order->courier_name }}</span> ·
                            @endif
                            AWB: <span class="fw-semibold">{{ $order->awb_code }}</span>
                            @if($order->shipping_status)
                                · <span class="text-success">{{ $order->shipping_status }}</span>
                            @endif
                        </div>
                        <a href="https://shiprocket.co/tracking/{{ $order->awb_code }}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-dark ms-auto">
                            <i class="bi bi-box-seam me-1"></i> Track Shipment
                        </a>
                    </div>
                @endif

                <!-- Items Ordered Breakdown -->
                <div class="mt-3">
                    <h6 class="fw-bold mb-2 small text-uppercase text-muted">Items In This Order</h6>
                    <div id="successItemsList" class="bg-white p-3 rounded border">
                        @if($items && count($items) > 0)
                            @foreach($items as $it)
                                <div class="d-flex align-items-center justify-content-between py-1 border-bottom">
                                    <span>{{ $it->product_name }} ({{ $it->size ?? 'M' }}) x {{ $it->quantity }}</span>
                                    <span class="fw-bold">₹{{ number_format($it->total, 0) }}</span>
                                </div>
                            @endforeach
                        @else
                            <div class="d-flex align-items-center justify-content-between py-1">
                                <span>Handblock Printed Pure Cotton Straight Kurti (M)</span>
                                <span class="fw-bold">₹999</span>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="d-flex flex-wrap justify-content-center gap-3">
                <a href="{{ route('home') }}" class="btn btn-zyra-primary px-4 py-2">
                    <i class="bi bi-house me-1"></i> Return Home
                </a>
                <a href="{{ route('shop') }}" class="btn btn-zyra-outline px-4 py-2">
                    <i class="bi bi-bag me-1"></i> Continue Shopping
                </a>
                <button type="button" class="btn btn-outline-secondary px-3 py-2" onclick="window.print()">
                    <i class="bi bi-printer me-1"></i> Print Receipt
                </button>
            </div>

        </div>
    </div>
</div>

@endsection
