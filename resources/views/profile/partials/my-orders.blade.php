{{-- Expects: $orders (Collection of Order with items) --}}
@php
    $paymentLabels = [
        'cod' => 'Cash on Delivery',
        'upi' => 'UPI',
        'card' => 'Credit / Debit Card',
        'netbanking' => 'Net Banking',
    ];
    $userReviews = $userReviews ?? collect();
@endphp

<style>
.zyra-stars { direction: rtl; display: inline-flex; gap: 2px; }
.zyra-stars input { display: none; }
.zyra-stars label { cursor: pointer; font-size: 1.5rem; color: #ddd; line-height: 1; }
.zyra-stars input:checked ~ label,
.zyra-stars label:hover,
.zyra-stars label:hover ~ label { color: #f5a623; }
.zyra-review-box { background: #faf8f6; }
</style>

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
                                                    @if($item->dupatta) | {{ $item->dupatta }} @endif
                                                </small>
                                            </div>
                                            <div class="text-end">
                                                <div class="fw-bold">₹{{ number_format($item->total, 2) }}</div>
                                                <small class="text-muted">₹{{ number_format($item->price, 2) }} each</small>
                                            </div>
                                        </div>

                                        {{-- Review this product (verified purchase) --}}
                                        @if ($item->product_id && $order->order_status !== 'Cancelled')
                                            @php $existingReview = $userReviews[$item->product_id] ?? null; @endphp
                                            <div class="zyra-review-box mt-1 mb-2 p-3 rounded border" data-review-box="{{ $item->product_id }}">
                                                @if ($existingReview)
                                                    <div class="zyra-review-done small">
                                                        <span class="text-muted">Your rating:</span>
                                                        <span class="text-warning">{{ str_repeat('★', (int) $existingReview->rating) }}{{ str_repeat('☆', 5 - (int) $existingReview->rating) }}</span>
                                                        <a href="{{ route('product.details', $item->product_id) }}" class="ms-2">View on product →</a>
                                                        <button type="button" class="btn btn-link btn-sm p-0 ms-2 zyra-review-edit">Edit</button>
                                                    </div>
                                                @endif
                                                <form class="zyra-review-form mt-1" data-product-id="{{ $item->product_id }}" @if($existingReview) hidden @endif>
                                                    <div class="small fw-semibold mb-1">{{ $existingReview ? 'Update your review' : 'Rate this product' }}</div>
                                                    <div class="zyra-stars mb-2">
                                                        @for ($s = 5; $s >= 1; $s--)
                                                            <input type="radio" id="rv-{{ $order->id }}-{{ $item->product_id }}-{{ $s }}" name="rating" value="{{ $s }}" @if($existingReview && (int) $existingReview->rating === $s) checked @endif>
                                                            <label for="rv-{{ $order->id }}-{{ $item->product_id }}-{{ $s }}" title="{{ $s }} star{{ $s === 1 ? '' : 's' }}">★</label>
                                                        @endfor
                                                    </div>
                                                    <textarea name="comment" rows="2" maxlength="1000" class="form-control form-control-sm mb-2" placeholder="Share what you liked (optional)…">{{ $existingReview->comment ?? '' }}</textarea>
                                                    <div class="d-flex align-items-center gap-2">
                                                        <button type="submit" class="btn btn-sm btn-dark">Submit review</button>
                                                        <span class="zyra-review-msg small"></span>
                                                    </div>
                                                </form>
                                            </div>
                                        @endif
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

                                    @if ($order->awb_code)
                                        <div class="d-flex flex-wrap align-items-center gap-2 mt-3 pt-3 border-top">
                                            <i class="bi bi-truck text-muted"></i>
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
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>

<script>
(function () {
    function csrf() {
        return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    }
    function stars(n) {
        n = Math.max(1, Math.min(5, parseInt(n, 10) || 0));
        return '★'.repeat(n) + '☆'.repeat(5 - n);
    }
    document.addEventListener('click', function (e) {
        const editBtn = e.target.closest('.zyra-review-edit');
        if (editBtn) {
            const box = editBtn.closest('[data-review-box]');
            const form = box?.querySelector('.zyra-review-form');
            if (form) form.hidden = !form.hidden;
        }
    });
    document.addEventListener('submit', function (e) {
        const form = e.target.closest('.zyra-review-form');
        if (!form) return;
        e.preventDefault();

        const box = form.closest('[data-review-box]');
        const msg = form.querySelector('.zyra-review-msg');
        const checked = form.querySelector('input[name="rating"]:checked');
        const comment = form.querySelector('textarea[name="comment"]').value.trim();

        if (!checked) {
            msg.textContent = 'Please pick a star rating first.';
            msg.className = 'zyra-review-msg small text-danger';
            return;
        }

        const btn = form.querySelector('button[type="submit"]');
        btn.disabled = true;
        msg.textContent = 'Publishing…';
        msg.className = 'zyra-review-msg small text-muted';

        fetch('/reviews', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrf()
            },
            body: JSON.stringify({
                product_id: parseInt(form.dataset.productId, 10),
                rating: parseInt(checked.value, 10),
                comment: comment
            })
        })
            .then(async (res) => {
                const data = await res.json().catch(() => ({}));
                if (!res.ok || !data.success) throw new Error(data.message || 'Could not save your review.');
                return data;
            })
            .then((data) => {
                box.innerHTML =
                    '<div class="small"><span class="text-success fw-semibold">✓ ' +
                    (data.message || 'Review published!') +
                    '</span><br><span class="text-muted">Your rating:</span> ' +
                    '<span class="text-warning">' + stars(data.rating) + '</span> ' +
                    '<a href="/product/' + encodeURIComponent(form.dataset.productId) + '" class="ms-2">View on product →</a></div>';
            })
            .catch((err) => {
                msg.textContent = err.message;
                msg.className = 'zyra-review-msg small text-danger';
                btn.disabled = false;
            });
    });
})();
</script>