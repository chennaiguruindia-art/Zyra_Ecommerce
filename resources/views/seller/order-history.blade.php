@extends('layouts.seller')

@section('title', 'Order History & Purchase Tally | ZYRA Seller Hub')
@section('meta_description', 'Detailed purchase history, coupon discounts, original vs final prices, and financial tally.')

@section('content')

@php
    $currentRange = request('range', 'all');
    $currentCoupon = request('coupon', 'all');
    $currentPayment = request('payment_status', 'all');
@endphp

<div class="seller-page-header">
    <div>
        <h1 class="h3 fw-bold mb-1">Order History &amp; Purchase Tally</h1>
        <p class="text-muted mb-0">Audit customer purchases, track coupon discounts, compare original vs final prices, and reconcile revenue.</p>
    </div>
    <div class="d-flex flex-wrap gap-2">
        <a href="{{ route('seller.order.history.export.excel', request()->all()) }}" class="btn btn-outline-success">
            <i class="bi bi-file-earmark-excel me-1"></i> Download Excel
        </a>
        <a href="{{ route('seller.order.history.export.pdf', request()->all()) }}" target="_blank" class="btn btn-outline-dark">
            <i class="bi bi-printer me-1"></i> Download / Print PDF
        </a>
    </div>
</div>

<!-- Financial Tally Summary KPI Cards -->
<div class="row g-3 mb-4">
    <!-- Gross Original Sales -->
    <div class="col-6 col-xl-3">
        <div class="seller-stat-card border-start border-primary border-3">
            <div class="seller-stat-icon bg-primary-soft text-primary"><i class="bi bi-receipt"></i></div>
            <div>
                <div class="seller-stat-value fs-4 mb-0">₹{{ number_format($metrics['total_gross'], 2) }}</div>
                <div class="text-muted small fw-semibold">Across {{ $metrics['total_orders'] }} order(s)</div>
                <div class="seller-stat-label text-uppercase mt-1" style="font-size: 0.72rem;">Original Gross Sales</div>
            </div>
        </div>
    </div>

    <!-- Total Discounts Deducted -->
    <div class="col-6 col-xl-3">
        <div class="seller-stat-card border-start border-danger border-3">
            <div class="seller-stat-icon bg-danger-soft text-danger"><i class="bi bi-tags"></i></div>
            <div>
                <div class="seller-stat-value fs-4 mb-0 text-danger">-₹{{ number_format($metrics['total_discount'], 2) }}</div>
                <div class="text-muted small fw-semibold">Avg ₹{{ number_format($metrics['avg_discount'], 2) }} / coupon order</div>
                <div class="seller-stat-label text-uppercase mt-1" style="font-size: 0.72rem;">Total Discounts Deducted</div>
            </div>
        </div>
    </div>

    <!-- Final Net Revenue Collected -->
    <div class="col-6 col-xl-3">
        <div class="seller-stat-card border-start border-success border-3">
            <div class="seller-stat-icon bg-success-soft text-success"><i class="bi bi-currency-rupee"></i></div>
            <div>
                <div class="seller-stat-value fs-4 mb-0 text-success">₹{{ number_format($metrics['total_net'], 2) }}</div>
                <div class="text-muted small fw-semibold">Gross - Discounts + Taxes</div>
                <div class="seller-stat-label text-uppercase mt-1" style="font-size: 0.72rem;">Final Net Revenue</div>
            </div>
        </div>
    </div>

    <!-- Coupon Usage Ratio -->
    <div class="col-6 col-xl-3">
        <div class="seller-stat-card border-start border-warning border-3">
            <div class="seller-stat-icon bg-gold-soft text-gold"><i class="bi bi-percent"></i></div>
            <div>
                <div class="seller-stat-value fs-4 mb-0">{{ $metrics['coupon_orders_count'] }} <span class="fs-6 text-muted fw-normal">/ {{ $metrics['total_orders'] }}</span></div>
                <div class="text-muted small fw-semibold">{{ $metrics['coupon_rate'] }}% of orders used coupons</div>
                <div class="seller-stat-label text-uppercase mt-1" style="font-size: 0.72rem;">Coupon Adoption Rate</div>
            </div>
        </div>
    </div>
</div>

<!-- Modern Filter Toolbar -->
<div class="seller-card mb-4">
    <div class="seller-card-header p-3">
        <form method="GET" action="{{ route('seller.order.history') }}" class="row g-2 align-items-center">
            <!-- Period Quick Filters -->
            <div class="col-12 col-lg-4">
                <div class="btn-group btn-group-sm w-100" role="group">
                    <a href="{{ route('seller.order.history', array_merge(request()->all(), ['range' => 'all'])) }}" class="btn {{ $currentRange === 'all' ? 'btn-dark' : 'btn-outline-secondary' }}">All Time</a>
                    <a href="{{ route('seller.order.history', array_merge(request()->all(), ['range' => 'today'])) }}" class="btn {{ $currentRange === 'today' ? 'btn-dark' : 'btn-outline-secondary' }}">Today</a>
                    <a href="{{ route('seller.order.history', array_merge(request()->all(), ['range' => 'week'])) }}" class="btn {{ $currentRange === 'week' ? 'btn-dark' : 'btn-outline-secondary' }}">This Week</a>
                    <a href="{{ route('seller.order.history', array_merge(request()->all(), ['range' => 'month'])) }}" class="btn {{ $currentRange === 'month' ? 'btn-dark' : 'btn-outline-secondary' }}">This Month</a>
                </div>
                <input type="hidden" name="range" value="{{ $currentRange }}">
            </div>

            <!-- Coupon Status Filter -->
            <div class="col-6 col-lg-3">
                <select name="coupon" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="all" {{ $currentCoupon === 'all' ? 'selected' : '' }}>All Orders (Coupons &amp; Non-Coupons)</option>
                    <option value="with_coupon" {{ $currentCoupon === 'with_coupon' ? 'selected' : '' }}>🎟️ Coupon Used Only</option>
                    <option value="no_coupon" {{ $currentCoupon === 'no_coupon' ? 'selected' : '' }}>⚪ No Coupon Used</option>
                </select>
            </div>

            <!-- Payment Status Filter -->
            <div class="col-6 col-lg-2">
                <select name="payment_status" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="all" {{ $currentPayment === 'all' ? 'selected' : '' }}>Payment: All</option>
                    <option value="paid" {{ $currentPayment === 'paid' ? 'selected' : '' }}>Paid Only</option>
                    <option value="pending" {{ $currentPayment === 'pending' ? 'selected' : '' }}>Pending Only</option>
                </select>
            </div>

            <!-- Search Bar -->
            <div class="col-12 col-lg-3">
                <div class="input-group input-group-sm">
                    <input type="text" name="search" class="form-control" placeholder="Search customer, order, coupon..." value="{{ request('search') }}">
                    <button class="btn btn-outline-secondary" type="submit"><i class="bi bi-search"></i></button>
                    @if(request('search') || request('coupon') != 'all' || request('range') != 'all')
                        <a href="{{ route('seller.order.history') }}" class="btn btn-outline-danger" title="Clear Filters"><i class="bi bi-x-lg"></i></a>
                    @endif
                </div>
            </div>
        </form>
    </div>

    <!-- Order History & Purchase Tally Table -->
    <div class="seller-card-body p-0">
        <div class="table-responsive">
            <table class="table seller-table align-middle mb-0">
                <thead>
                    <tr>
                        <th>Order &amp; Date</th>
                        <th>Customer / Purchaser</th>
                        <th>Items Summary</th>
                        <th class="text-end">Original Price</th>
                        <th class="text-center">Coupon Used?</th>
                        <th>Coupon Code</th>
                        <th class="text-end">Discount Deducted</th>
                        <th class="text-end">Final Price Paid</th>
                        <th>Payment &amp; Status</th>
                        <th class="text-center">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($orders as $o)
                        @php
                            $gross = (float) $o->subtotal;
                            if ($gross <= 0 && $o->items->isNotEmpty()) {
                                $gross = (float) $o->items->sum(fn ($i) => (float)$i->price * (int)$i->quantity);
                            }
                            if ($gross <= 0) {
                                $gross = (float) $o->total + (float) $o->discount;
                            }

                            $discount = (float) $o->discount;
                            $hasCoupon = !empty($o->coupon_code) && $discount > 0;
                            $discountPct = $gross > 0 && $discount > 0 ? round(($discount / $gross) * 100, 1) : 0;
                            $final = (float) $o->total;

                            $orderDataJson = [
                                'order_number' => $o->order_number,
                                'date' => $o->created_at?->format('d M Y, h:i A') ?? '-',
                                'customer_name' => $o->customer_name,
                                'customer_email' => $o->customer_email ?: '-',
                                'customer_phone' => $o->customer_phone ?: '-',
                                'shipping_address' => $o->shipping_address,
                                'city' => $o->city ?: '-',
                                'state' => $o->state ?: '-',
                                'pincode' => $o->pincode ?: '-',
                                'gross' => number_format($gross, 2),
                                'discount' => number_format($discount, 2),
                                'discount_pct' => $discountPct,
                                'has_coupon' => $hasCoupon,
                                'coupon_code' => $o->coupon_code ?: '-',
                                'shipping_cost' => number_format((float)$o->shipping_cost, 2),
                                'tax' => number_format((float)$o->tax, 2),
                                'final' => number_format($final, 2),
                                'payment_method' => strtoupper($o->payment_method),
                                'payment_status' => ucfirst($o->payment_status),
                                'order_status' => $o->order_status,
                                'items' => $o->items->map(fn($item) => [
                                    'name' => $item->product_name,
                                    'size' => $item->size ?: 'Free',
                                    'quantity' => $item->quantity,
                                    'price' => number_format((float)$item->price, 2),
                                    'line_total' => number_format((float)$item->price * (int)$item->quantity, 2),
                                ])->toArray()
                            ];
                        @endphp
                        <tr>
                            <td>
                                <div class="fw-bold small text-dark">{{ $o->order_number }}</div>
                                <small class="text-muted">{{ $o->created_at?->format('d M Y, h:i A') ?? '-' }}</small>
                            </td>
                            <td>
                                <div class="fw-semibold small text-dark">{{ $o->customer_name }}</div>
                                <div class="small text-muted">{{ $o->customer_phone ?: $o->customer_email ?: '-' }}</div>
                                <small class="text-muted">{{ $o->city ? $o->city . ', ' . $o->state : '' }}</small>
                            </td>
                            <td>
                                <div class="small">
                                    <span class="badge bg-light text-dark border">{{ $o->items->sum('quantity') }} items</span>
                                    <div class="text-muted text-truncate" style="max-width: 160px;" title="{{ $o->items->pluck('product_name')->implode(', ') }}">
                                        {{ $o->items->first()?->product_name ?? 'Item' }}{{ $o->items->count() > 1 ? ' +' . ($o->items->count() - 1) . ' more' : '' }}
                                    </div>
                                </div>
                            </td>
                            <td class="text-end">
                                <div class="fw-bold text-dark">₹{{ number_format($gross, 2) }}</div>
                                <small class="text-muted">Original</small>
                            </td>
                            <td class="text-center">
                                @if($hasCoupon)
                                    <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1">
                                        <i class="bi bi-tag-fill me-1"></i> YES
                                    </span>
                                @else
                                    <span class="badge bg-light text-muted border px-2 py-1">NO</span>
                                @endif
                            </td>
                            <td>
                                @if($hasCoupon)
                                    <span class="badge bg-dark font-monospace px-2 py-1">{{ $o->coupon_code }}</span>
                                @else
                                    <span class="text-muted small">—</span>
                                @endif
                            </td>
                            <td class="text-end">
                                @if($hasCoupon)
                                    <div class="fw-bold text-danger">-₹{{ number_format($discount, 2) }}</div>
                                    <small class="text-success fw-semibold">({{ $discountPct }}% off)</small>
                                @else
                                    <span class="text-muted small">₹0.00</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <div class="fw-bold fs-6 text-dark">₹{{ number_format($final, 2) }}</div>
                                <small class="text-muted" style="font-size: 0.72rem;">
                                    ₹{{ number_format($gross, 2) }} - ₹{{ number_format($discount, 2) }}
                                </small>
                            </td>
                            <td>
                                <div>
                                    <span class="badge {{ $o->payment_status === 'paid' ? 'bg-success-subtle text-success' : 'bg-warning-subtle text-dark' }} border">
                                        {{ ucfirst($o->payment_status) }} &bull; {{ strtoupper($o->payment_method) }}
                                    </span>
                                </div>
                                <div class="mt-1">
                                    <span class="badge bg-light text-dark border" style="font-size: 0.7rem;">
                                        {{ $o->order_status }}
                                    </span>
                                </div>
                            </td>
                            <td class="text-center">
                                <button type="button" class="btn btn-sm btn-outline-dark" onclick='openOrderTallyModal(@json($orderDataJson))' title="View Step-by-Step Tally Calculation">
                                    <i class="bi bi-calculator me-1"></i> View Tally
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="text-center py-5 text-muted">
                                <i class="bi bi-receipt fs-1 d-block mb-2 text-secondary"></i>
                                <h6>No orders found matching the selected criteria</h6>
                                <p class="small mb-0">Try clearing your search or switching filters to "All Time".</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($orders->hasPages())
            <div class="p-3 border-top">
                {{ $orders->links() }}
            </div>
        @endif
    </div>
</div>

<!-- ========================================================================= -->
<!-- ORDER TALLY & INVOICE CALCULATION MODAL -->
<!-- ========================================================================= -->
<div class="modal fade" id="orderTallyModal" tabindex="-1" aria-labelledby="orderTallyModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 14px;">
            <div class="modal-header border-0 pb-0">
                <div class="d-flex align-items-center gap-2">
                    <span class="rounded-circle bg-dark text-white d-inline-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                        <i class="bi bi-calculator fs-5"></i>
                    </span>
                    <div>
                        <h5 class="modal-title fw-bold mb-0" id="tallyModalOrderNo">Order Tally Breakdown</h5>
                        <small class="text-muted" id="tallyModalDate">Date &amp; Time</small>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body pt-3">
                <!-- Customer & Address Summary Card -->
                <div class="p-3 bg-light rounded-3 mb-3 border">
                    <div class="row g-2">
                        <div class="col-sm-6">
                            <span class="text-muted small text-uppercase fw-bold" style="font-size: 0.7rem;">Customer Details</span>
                            <div class="fw-bold text-dark" id="tallyModalCustName">Customer Name</div>
                            <div class="small text-muted" id="tallyModalCustContact">Email &bull; Phone</div>
                        </div>
                        <div class="col-sm-6 text-sm-end">
                            <span class="text-muted small text-uppercase fw-bold" style="font-size: 0.7rem;">Shipping Address</span>
                            <div class="small text-dark" id="tallyModalCustAddress">Address line</div>
                            <div class="small text-muted" id="tallyModalCustLocation">City, State - Pincode</div>
                        </div>
                    </div>
                </div>

                <!-- Itemized Purchased Products Table -->
                <h6 class="fw-bold text-dark mb-2" style="font-size: 0.85rem;"><i class="bi bi-bag-check me-1 text-muted"></i>Purchased Items Breakdown</h6>
                <div class="table-responsive border rounded-3 mb-3">
                    <table class="table table-sm align-middle mb-0">
                        <thead class="bg-light">
                            <tr class="small text-muted">
                                <th class="ps-3">Item</th>
                                <th class="text-center">Size</th>
                                <th class="text-center">Qty</th>
                                <th class="text-end">Unit Price</th>
                                <th class="text-end pe-3">Line Total</th>
                            </tr>
                        </thead>
                        <tbody id="tallyModalItemsBody">
                            <!-- Populated dynamically -->
                        </tbody>
                    </table>
                </div>

                <!-- Financial Tally Calculation Breakdown -->
                <div class="p-3 bg-white border rounded-3 mb-3">
                    <h6 class="fw-bold text-dark mb-3" style="font-size: 0.85rem;">
                        <i class="bi bi-calculator-fill me-1 text-primary"></i>Financial Tally Arithmetic
                    </h6>

                    <div class="d-flex justify-content-between align-items-center py-1 border-bottom">
                        <span class="text-muted">Original Gross Items Subtotal:</span>
                        <strong class="text-dark" id="tallyCalcGross">₹0.00</strong>
                    </div>

                    <div class="d-flex justify-content-between align-items-center py-2 border-bottom bg-light px-2 rounded my-1">
                        <div>
                            <span class="fw-bold text-dark">Coupon Applied:</span>
                            <span class="badge bg-dark font-monospace ms-1" id="tallyCalcCouponCode">—</span>
                        </div>
                        <div class="text-end">
                            <span class="fw-bold text-danger" id="tallyCalcDiscount">-₹0.00</span>
                            <small class="text-success ms-1" id="tallyCalcDiscountPct">(0% off)</small>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between align-items-center py-1 border-bottom">
                        <span class="text-muted">Net Taxable Subtotal (Original - Discount):</span>
                        <strong class="text-dark" id="tallyCalcNetSubtotal">₹0.00</strong>
                    </div>

                    <div class="d-flex justify-content-between align-items-center py-1 border-bottom">
                        <span class="text-muted">Shipping Charges:</span>
                        <span class="text-dark fw-semibold" id="tallyCalcShipping">₹0.00</span>
                    </div>

                    <div class="d-flex justify-content-between align-items-center py-1 border-bottom">
                        <span class="text-muted">Taxes &amp; GST:</span>
                        <span class="text-dark fw-semibold" id="tallyCalcTax">₹0.00</span>
                    </div>

                    <!-- Final Collected Total -->
                    <div class="d-flex justify-content-between align-items-center pt-2 mt-2">
                        <div>
                            <div class="h5 fw-bold text-dark mb-0">Final Net Paid Amount:</div>
                            <small class="text-muted" id="tallyCalcPaymentTag">Paid via UPI</small>
                        </div>
                        <div class="text-end">
                            <div class="h4 fw-bold text-success mb-0" id="tallyCalcFinal">₹0.00</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-dark" onclick="window.print()">
                    <i class="bi bi-printer me-1"></i> Print Receipt
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function openOrderTallyModal(data) {
        document.getElementById('tallyModalOrderNo').textContent = 'Order #' + data.order_number;
        document.getElementById('tallyModalDate').textContent = 'Placed on ' + data.date + ' &bull; Status: ' + data.order_status;

        // Customer
        document.getElementById('tallyModalCustName').textContent = data.customer_name;
        document.getElementById('tallyModalCustContact').textContent = (data.customer_email || '-') + ' · ' + (data.customer_phone || '-');
        document.getElementById('tallyModalCustAddress').textContent = data.shipping_address || 'Standard Address';
        document.getElementById('tallyModalCustLocation').textContent = (data.city || '-') + ', ' + (data.state || '-') + ' ' + (data.pincode ? '- ' + data.pincode : '');

        // Items
        const itemsBody = document.getElementById('tallyModalItemsBody');
        let html = '';
        if (data.items && data.items.length > 0) {
            data.items.forEach(it => {
                html += `
                    <tr>
                        <td class="ps-3 fw-semibold small">${it.name}</td>
                        <td class="text-center small"><span class="badge bg-light text-dark border">${it.size}</span></td>
                        <td class="text-center small">${it.quantity}</td>
                        <td class="text-end small">₹${it.price}</td>
                        <td class="text-end pe-3 fw-bold small">₹${it.line_total}</td>
                    </tr>
                `;
            });
        } else {
            html = `<tr><td colspan="5" class="text-center text-muted py-2 small">No itemized breakdown recorded</td></tr>`;
        }
        itemsBody.innerHTML = html;

        // Tally Math
        document.getElementById('tallyCalcGross').textContent = '₹' + data.gross;

        if (data.has_coupon) {
            document.getElementById('tallyCalcCouponCode').textContent = data.coupon_code;
            document.getElementById('tallyCalcCouponCode').classList.remove('bg-secondary');
            document.getElementById('tallyCalcCouponCode').classList.add('bg-dark');
            document.getElementById('tallyCalcDiscount').textContent = '-₹' + data.discount;
            document.getElementById('tallyCalcDiscountPct').textContent = '(' + data.discount_pct + '% off)';
        } else {
            document.getElementById('tallyCalcCouponCode').textContent = 'NO COUPON';
            document.getElementById('tallyCalcCouponCode').classList.remove('bg-dark');
            document.getElementById('tallyCalcCouponCode').classList.add('bg-secondary');
            document.getElementById('tallyCalcDiscount').textContent = '₹0.00';
            document.getElementById('tallyCalcDiscountPct').textContent = '(0% off)';
        }

        const netSubtotal = Math.max(0, parseFloat(data.gross.replace(/,/g, '')) - parseFloat(data.discount.replace(/,/g, '')));
        document.getElementById('tallyCalcNetSubtotal').textContent = '₹' + netSubtotal.toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2});

        document.getElementById('tallyCalcShipping').textContent = '₹' + data.shipping_cost;
        document.getElementById('tallyCalcTax').textContent = '₹' + data.tax;
        document.getElementById('tallyCalcFinal').textContent = '₹' + data.final;
        document.getElementById('tallyCalcPaymentTag').textContent = 'Payment: ' + data.payment_status + ' via ' + data.payment_method;

        const modalEl = document.getElementById('orderTallyModal');
        if (modalEl && window.bootstrap) {
            bootstrap.Modal.getOrCreateInstance(modalEl).show();
        }
    }
</script>
@endpush

@endsection
