<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order History & Purchase Tally Report | ZYRA</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logo/logo.png') }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            color: #1f2937;
            background: #f8fafc;
            padding: 30px 15px;
        }
        .report-sheet {
            max-width: 1100px;
            margin: 0 auto;
            background: #ffffff;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            padding: 40px;
        }
        .brand-title {
            font-family: 'Playfair Display', serif;
            font-size: 2rem;
            letter-spacing: 0.12em;
            color: #111827;
            margin-bottom: 0;
            font-weight: 700;
        }
        .brand-subtitle {
            font-size: 0.78rem;
            letter-spacing: 0.25em;
            color: #b45309;
            text-transform: uppercase;
            font-weight: 600;
        }
        .metric-box {
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 15px;
            background: #fafafa;
        }
        .metric-val {
            font-size: 1.3rem;
            font-weight: 700;
            color: #111827;
        }
        .metric-lbl {
            font-size: 0.72rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #6b7280;
            font-weight: 600;
        }
        .table th {
            background-color: #111827 !important;
            color: #ffffff !important;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            padding: 10px 12px;
        }
        .table td {
            font-size: 0.82rem;
            padding: 10px 12px;
            vertical-align: middle;
        }
        @media print {
            body {
                background: #ffffff !important;
                padding: 0 !important;
            }
            .report-sheet {
                box-shadow: none !important;
                border: none !important;
                padding: 0 !important;
                max-width: 100% !important;
            }
            .no-print {
                display: none !important;
            }
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }
    </style>
</head>
<body>

    <!-- Non-printable top action bar -->
    <div class="report-sheet no-print mb-4 py-3 d-flex justify-content-between align-items-center">
        <div>
            <a href="{{ route('seller.order.history', request()->all()) }}" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> Back to Seller Hub
            </a>
        </div>
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-sm btn-dark" onclick="window.print()">
                <i class="bi bi-printer me-1"></i> Print / Save as PDF
            </button>
            <a href="{{ route('seller.order.history.export.excel', request()->all()) }}" class="btn btn-sm btn-outline-success">
                <i class="bi bi-file-earmark-excel me-1"></i> Download Excel
            </a>
        </div>
    </div>

    <div class="report-sheet">
        <!-- Letterhead Header -->
        <div class="d-flex justify-content-between align-items-center border-bottom pb-4 mb-4">
            <div>
                <h1 class="brand-title">ZYRA</h1>
                <div class="brand-subtitle">Luxury Indian Boutique &bull; Seller Hub</div>
                <div class="small text-muted mt-1">Official Order Purchase History &amp; Coupon Tally Reconciliation Report</div>
            </div>
            <div class="text-end">
                <span class="badge bg-dark px-3 py-2 text-uppercase mb-2">Scope: {{ ucfirst($range) }}</span>
                <div class="small text-muted">Generated: <strong>{{ now()->format('d M Y, h:i A') }}</strong></div>
                <div class="small text-muted">Store: <strong>{{ auth()->user()?->name ?? 'ZYRA Store' }}</strong></div>
            </div>
        </div>

        <!-- KPI Metrics Summary Row -->
        <div class="row g-3 mb-4">
            <div class="col-3">
                <div class="metric-box border-start border-primary border-3">
                    <div class="metric-val text-primary">₹{{ number_format($metrics['total_gross'], 2) }}</div>
                    <div class="small fw-semibold text-dark">{{ $metrics['total_orders'] }} orders placed</div>
                    <div class="metric-lbl">Original Gross Sales</div>
                </div>
            </div>
            <div class="col-3">
                <div class="metric-box border-start border-danger border-3">
                    <div class="metric-val text-danger">-₹{{ number_format($metrics['total_discount'], 2) }}</div>
                    <div class="small fw-semibold text-dark">{{ $metrics['coupon_orders_count'] }} orders with coupon</div>
                    <div class="metric-lbl">Coupon Discounts Given</div>
                </div>
            </div>
            <div class="col-3">
                <div class="metric-box border-start border-success border-3">
                    <div class="metric-val text-success">₹{{ number_format($metrics['total_net'], 2) }}</div>
                    <div class="small fw-semibold text-dark">Gross - Discounts + Taxes</div>
                    <div class="metric-lbl">Net Revenue Collected</div>
                </div>
            </div>
            <div class="col-3">
                <div class="metric-box border-start border-warning border-3">
                    <div class="metric-val text-warning">{{ $metrics['coupon_rate'] }}%</div>
                    <div class="small fw-semibold text-dark">Avg: ₹{{ number_format($metrics['avg_discount'], 2) }}</div>
                    <div class="metric-lbl">Coupon Usage Ratio</div>
                </div>
            </div>
        </div>

        <!-- Itemized Orders Table -->
        <h6 class="fw-bold text-dark text-uppercase mb-3" style="font-size: 0.85rem; letter-spacing: 0.05em;">
            Itemized Order Purchase &amp; Coupon Audit
        </h6>

        <div class="table-responsive mb-4">
            <table class="table table-bordered table-striped align-middle">
                <thead>
                    <tr>
                        <th style="width: 120px;">Order &amp; Date</th>
                        <th>Customer Name</th>
                        <th class="text-end" style="width: 110px;">Original Price</th>
                        <th class="text-center" style="width: 90px;">Coupon?</th>
                        <th style="width: 110px;">Code</th>
                        <th class="text-end" style="width: 110px;">Discount</th>
                        <th class="text-end" style="width: 120px;">Final Paid</th>
                        <th style="width: 100px;">Payment</th>
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
                        @endphp
                        <tr>
                            <td>
                                <div class="fw-bold">{{ $o->order_number }}</div>
                                <small class="text-muted">{{ $o->created_at?->format('d/m/Y') ?? '-' }}</small>
                            </td>
                            <td>
                                <div class="fw-semibold">{{ $o->customer_name }}</div>
                                <small class="text-muted">{{ $o->city ? $o->city . ' · ' : '' }}{{ $o->customer_phone ?: '-' }}</small>
                            </td>
                            <td class="text-end fw-semibold">₹{{ number_format($gross, 2) }}</td>
                            <td class="text-center">
                                @if($hasCoupon)
                                    <span class="badge bg-success text-white">YES</span>
                                @else
                                    <span class="text-muted">NO</span>
                                @endif
                            </td>
                            <td>
                                @if($hasCoupon)
                                    <span class="font-monospace fw-bold">{{ $o->coupon_code }}</span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td class="text-end {{ $hasCoupon ? 'text-danger fw-bold' : 'text-muted' }}">
                                {{ $hasCoupon ? '-₹' . number_format($discount, 2) : '₹0.00' }}
                            </td>
                            <td class="text-end fw-bold">₹{{ number_format($final, 2) }}</td>
                            <td>
                                <small class="fw-semibold">{{ strtoupper($o->payment_method) }}</small>
                                <div class="text-muted" style="font-size: 0.7rem;">{{ ucfirst($o->payment_status) }}</div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-4 text-muted">No orders recorded for this period.</td>
                        </tr>
                    @endforelse
                </tbody>
                <tfoot>
                    <tr class="table-dark fw-bold">
                        <td colspan="2" class="text-uppercase text-end">Tally Grand Totals:</td>
                        <td class="text-end text-white">₹{{ number_format($metrics['total_gross'], 2) }}</td>
                        <td colspan="2" class="text-center text-white">{{ $metrics['coupon_orders_count'] }} with coupon</td>
                        <td class="text-end text-danger text-white">-₹{{ number_format($metrics['total_discount'], 2) }}</td>
                        <td class="text-end text-white">₹{{ number_format($metrics['total_net'], 2) }}</td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <!-- Footer / Certification -->
        <div class="row pt-4 mt-4 border-top">
            <div class="col-8">
                <div class="small text-muted">
                    This document is an electronically generated statement of sales, coupon redemptions and revenue tally for <strong>{{ auth()->user()?->name ?? 'ZYRA Store' }}</strong>.
                    <br>&copy; {{ date('Y') }} ZYRA Fashion Lifestyle &bull; zyralifestyle.in
                </div>
            </div>
            <div class="col-4 text-end">
                <div class="small text-muted mb-4">Authorized Signature / Accounts:</div>
                <div style="border-bottom: 1px dashed #9ca3af; width: 180px; margin-left: auto;"></div>
            </div>
        </div>
    </div>

</body>
</html>
