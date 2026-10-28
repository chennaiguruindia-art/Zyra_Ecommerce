<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stock Inward & Valuation Report | ZYRA Seller Hub</title>
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
            max-width: 1050px;
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
            <a href="{{ route('seller.stock') }}" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> Back to Seller Hub
            </a>
        </div>
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-sm btn-dark" onclick="window.print()">
                <i class="bi bi-printer me-1"></i> Print / Save as PDF
            </button>
            <a href="{{ route('seller.stock.export.excel', ['range' => $range, 'search' => request('search')]) }}" class="btn btn-sm btn-outline-success">
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
                <div class="small text-muted mt-1">Official Inventory &amp; Stock Inward Audit Report</div>
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
                    <div class="metric-val text-primary">₹{{ number_format($metrics['today']['amount'], 2) }}</div>
                    <div class="small fw-semibold text-dark">{{ $metrics['today']['units'] }} units</div>
                    <div class="metric-lbl">Received Today</div>
                </div>
            </div>
            <div class="col-3">
                <div class="metric-box border-start border-info border-3">
                    <div class="metric-val text-info">₹{{ number_format($metrics['week']['amount'], 2) }}</div>
                    <div class="small fw-semibold text-dark">{{ $metrics['week']['units'] }} units</div>
                    <div class="metric-lbl">Received This Week</div>
                </div>
            </div>
            <div class="col-3">
                <div class="metric-box border-start border-success border-3">
                    <div class="metric-val text-success">₹{{ number_format($metrics['month']['amount'], 2) }}</div>
                    <div class="small fw-semibold text-dark">{{ $metrics['month']['units'] }} units</div>
                    <div class="metric-lbl">Received This Month</div>
                </div>
            </div>
            <div class="col-3">
                <div class="metric-box border-start border-warning border-3">
                    <div class="metric-val text-warning">₹{{ number_format($metrics['inventory']['total_valuation'], 2) }}</div>
                    <div class="small fw-semibold text-dark">{{ number_format($metrics['inventory']['total_units']) }} total units</div>
                    <div class="metric-lbl">Current Stock Valuation</div>
                </div>
            </div>
        </div>

        <!-- Inward Transactions Table -->
        <h6 class="fw-bold text-dark text-uppercase mb-3" style="font-size: 0.85rem; letter-spacing: 0.05em;">
            Itemized Stock Inward Logs
        </h6>

        <div class="table-responsive mb-4">
            <table class="table table-bordered table-striped align-middle">
                <thead>
                    <tr>
                        <th style="width: 130px;">Receipt ID &amp; Date</th>
                        <th>Product Details</th>
                        <th>Sizes Breakdown</th>
                        <th class="text-center" style="width: 90px;">Units</th>
                        <th class="text-end" style="width: 110px;">Unit Cost</th>
                        <th class="text-end" style="width: 130px;">Total Amount</th>
                        <th style="width: 130px;">Ref / Batch</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $grandUnits = 0;
                        $grandAmount = 0.0;
                    @endphp
                    @forelse($receipts as $r)
                        @php
                            $grandUnits += $r->quantity;
                            $grandAmount += (float)$r->total_cost;
                        @endphp
                        <tr>
                            <td>
                                <div class="fw-bold">#SR-{{ str_pad($r->id, 5, '0', STR_PAD_LEFT) }}</div>
                                <small class="text-muted">{{ $r->created_at->format('d/m/Y H:i') }}</small>
                            </td>
                            <td>
                                <div class="fw-semibold">{{ $r->product?->name ?? 'Deleted Product' }}</div>
                                <small class="text-muted">SKU: {{ $r->product?->sku ?: '-' }} &bull; {{ $r->product?->category?->name ?? '-' }}</small>
                            </td>
                            <td>
                                @if(!empty($r->sizes_breakdown) && is_array($r->sizes_breakdown))
                                    @foreach($r->sizes_breakdown as $s => $q)
                                        <span class="badge bg-light text-dark border me-1">{{ $s }}: +{{ $q }}</span>
                                    @endforeach
                                @else
                                    <span class="text-muted small">Bulk (+{{ $r->quantity }})</span>
                                @endif
                            </td>
                            <td class="text-center fw-bold text-success">+{{ $r->quantity }}</td>
                            <td class="text-end">₹{{ number_format($r->unit_cost, 2) }}</td>
                            <td class="text-end fw-bold">₹{{ number_format($r->total_cost, 2) }}</td>
                            <td>
                                <div class="small fw-semibold">{{ $r->reference ?: '-' }}</div>
                                <small class="text-muted">{{ ucfirst(str_replace('_', ' ', $r->source)) }}</small>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-4 text-muted">No stock inward records found for this period.</td>
                        </tr>
                    @endforelse
                </tbody>
                <tfoot>
                    <tr class="table-dark fw-bold">
                        <td colspan="3" class="text-uppercase text-end">Grand Total Inward:</td>
                        <td class="text-center text-white">+{{ $grandUnits }}</td>
                        <td></td>
                        <td class="text-end text-white">₹{{ number_format($grandAmount, 2) }}</td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <!-- Footer / Certification -->
        <div class="row pt-4 mt-4 border-top">
            <div class="col-8">
                <div class="small text-muted">
                    This document is an electronically generated statement of inventory inward and stock valuation for <strong>{{ auth()->user()?->name ?? 'ZYRA Store' }}</strong>.
                    <br>&copy; {{ date('Y') }} ZYRA Fashion Lifestyle &bull; zyralifestyle.in
                </div>
            </div>
            <div class="col-4 text-end">
                <div class="small text-muted mb-4">Authorized Signature / Store Stamp:</div>
                <div style="border-bottom: 1px dashed #9ca3af; width: 180px; margin-left: auto;"></div>
            </div>
        </div>
    </div>

</body>
</html>
