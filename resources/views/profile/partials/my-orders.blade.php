{{-- Expects: $orders (Collection of Order with items) --}}
@php
    $paymentLabels = [
        'cod' => 'Cash on Delivery',
        'upi' => 'UPI',
        'card' => 'Credit / Debit Card',
        'netbanking' => 'Net Banking',
    ];
@endphp

<!-- Order History -->
<div class="{{ $wrapClass ?? 'row mt-5' }}">
    <div class="col-12">
        <div class="card border-0 shadow-sm p-4">
            <div class="d-flex align-items-center justify-content-between mb-4">
                <h5 class="fw-bold mb-0"><i class="bi bi-receipt me-2"></i>My Orders</h5>
                <span class="badge bg-dark">{{ count($orders) }} Order{{ count($orders) === 1 ? '' : 's' }}</span>
            </div>

            @if (count($orders) === 0)
                <div class="text-center py-5 text-muted">
                    <i class="bi bi-bag-x" style="font-size: 2.5rem;"></i>
                    <p class="mt-3 mb-1 fw-semibold text-dark">No orders yet</p>
                    <p class="small">When you place an order, it will appear here.</p>
                    <a href="{{ route('shop') }}" class="btn btn-zyra-primary btn-sm mt-2">Start Shopping</a>
                </div>
            @else
                <div class="accordion" id="ordersAccordion{{ $accordionSuffix ?? '' }}">
                    @foreach ($orders as $order)
                        <div class="accordion-item border rounded-3 mb-3 overflow-hidden">
                            <h2 class="accordion-header" id="orderHead{{ $order->id }}">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#orderCollapse{{ $order->id }}" aria-expanded="false" aria-controls="orderCollapse{{ $order->id }}">
                                    <div class="d-flex flex-wrap gap-3 align-items-center w-100 pe-2">
                                        <div>
                                            <span class="fw-bold">{{ $order->order_number }}</span>
                                            <small class="d-block text-muted">{{ \Carbon\Carbon::parse($order->created_at)->format('d M Y, h:i A') }}</small>
                                        </div>
                                        <span class="bg-zyra-primary px-2 py-1 rounded text-white small fw-semibold">
                                            {{ \Illuminate\Support\Str::title($order->order_status) }}
                                        </span>
                                        <small class="text-muted d-block d-md-inline">
                                            <i class="bi bi-box-seam me-1"></i>{{ count($order->items) }} item{{ count($order->items) === 1 ? '' : 's' }}
                                        </small>
                                        <span class="ms-auto fw-bold">₹{{ number_format($order->total, 2) }}</span>
                                    </div>
                                </button>
                            </h2>
                            <div id="orderCollapse{{ $order->id }}" class="accordion-collapse collapse" aria-labelledby="orderHead{{ $order->id }}" data-bs-parent="#ordersAccordion{{ $accordionSuffix ?? '' }}">
                                <div class="accordion-body border-top">
                                    @foreach ($order->items as $item)
                                        <div class="d-flex align-items-center gap-3 py-2 border-bottom">
                                            <div class="position-relative">
                                                <img src="{{ $item->image_url }}" alt="{{ $item->product_name }}" style="width: 56px; height: 72px; object-fit: cover; border-radius: 6px;">
                                            </div>
                                            <div class="flex-grow-1">
                                                <div class="fw-semibold small">{{ $item->product_name }}</div>
                                                <small class="text-muted d-block">
                                                    Qty: {{ $item->quantity }}
                                                    @if($item->size) | Size: {{ $item->size }} @endif
                                                    @if($item->color) | Color: {{ $item->color }} @endif
                                                </small>
                                            </div>
                                            <div class="text-end">
                                                <div class="fw-bold">₹{{ number_format($item->total, 2) }}</div>
                                                <small class="text-muted">₹{{ number_format($item->price, 2) }} each</small>
                                            </div>
                                        </div>
                                    @endforeach

                                    <div class="row g-3 mt-2">
                                        <div class="col-md-6">
                                            <div class="small text-muted mb-1 fw-semibold text-uppercase" style="letter-spacing: 0.5px;">Payment</div>
                                            <p class="mb-0 small">
                                                {{ $paymentLabels[$order->payment_method] ?? ucfirst(str_replace('_', ' ', $order->payment_method)) }}
                                                <span class="badge {{ $order->payment_status === 'paid' ? 'bg-success' : 'bg-warning text-dark' }} ms-1">
                                                    {{ ucfirst($order->payment_status) }}
                                                </span>
                                            </p>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="small text-muted mb-1 fw-semibold text-uppercase" style="letter-spacing: 0.5px;">Shipping To</div>
                                            <p class="mb-0 small">{{ $order->customer_name }} · {{ $order->shipping_address }}, {{ $order->city }} {{ $order->pincode }}</p>
                                        </div>
                                    </div>

                                    @if ($order->discount > 0 && $order->coupon_code)
                                        <div class="d-flex justify-content-between small mt-2">
                                            <span>Coupon ({{ $order->coupon_code }})</span>
                                            <span class="text-success">-₹{{ number_format($order->discount, 2) }}</span>
                                        </div>
                                    @endif
                                    <div class="d-flex justify-content-between small mt-1">
                                        <span>Subtotal</span>
                                        <span>₹{{ number_format($order->subtotal, 2) }}</span>
                                    </div>
                                    <div class="d-flex justify-content-between small">
                                        <span>Shipping</span>
                                        <span>{{ $order->shipping_cost == 0 ? 'FREE' : '₹' . number_format($order->shipping_cost, 2) }}</span>
                                    </div>
                                    <hr class="my-2">
                                    <div class="d-flex justify-content-between fw-bold">
                                        <span>Total</span>
                                        <span>₹{{ number_format($order->total, 2) }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>