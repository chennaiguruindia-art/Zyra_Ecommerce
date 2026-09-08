@extends('layouts.seller')

@section('title', 'Sales Analytics & Earnings | ZYRA Seller Hub')
@section('meta_description', 'View sales performance analytics, category breakdown and weekly settlement payouts.')

@push('scripts')
<script>
    window.ZyraSeller.page = 'analytics';
</script>
@endpush

@section('content')

<div class="seller-page-header">
    <div>
        <h1 class="h3 fw-bold mb-1">Store Analytics & Earnings</h1>
        <p class="text-muted mb-0">Track revenue growth, average order value, and settlement payout history.</p>
    </div>
    <div class="d-flex gap-2">
        <button type="button" class="btn btn-outline-dark btn-sm">
            <i class="bi bi-calendar3 me-1"></i> Last 30 Days
        </button>
    </div>
</div>

<!-- KPI Cards -->
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="seller-stat-card">
            <div class="seller-stat-icon bg-gold-soft text-gold"><i class="bi bi-wallet2"></i></div>
            <div>
                <div class="seller-stat-value" id="analyticsRevenue">₹6,594</div>
                <div class="seller-stat-label">Gross Revenue</div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="seller-stat-card">
            <div class="seller-stat-icon bg-primary-soft text-primary"><i class="bi bi-bag-check"></i></div>
            <div>
                <div class="seller-stat-value" id="analyticsUnitsSold">148 units</div>
                <div class="seller-stat-label">Units Dispatched</div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="seller-stat-card">
            <div class="seller-stat-icon bg-success-soft text-success"><i class="bi bi-graph-up"></i></div>
            <div>
                <div class="seller-stat-value" id="analyticsAov">₹1,318</div>
                <div class="seller-stat-label">Average Order Value (AOV)</div>
            </div>
        </div>
    </div>
</div>

<!-- Category Performance & Settlement History -->
<div class="row g-4">
    <!-- Category Revenue Share -->
    <div class="col-lg-5">
        <div class="seller-card h-100">
            <div class="seller-card-header">
                <h5 class="mb-0 fw-bold"><i class="bi bi-pie-chart me-2 text-muted"></i>Revenue By Category</h5>
            </div>
            <div class="seller-card-body">
                <div class="mb-3">
                    <div class="d-flex justify-content-between small fw-bold mb-1">
                        <span>Kurtis & Anarkalis</span>
                        <span>38%</span>
                    </div>
                    <div class="progress" style="height: 8px;">
                        <div class="progress-bar bg-dark" style="width: 38%"></div>
                    </div>
                </div>

                <div class="mb-3">
                    <div class="d-flex justify-content-between small fw-bold mb-1">
                        <span>Tops & Peplums</span>
                        <span>26%</span>
                    </div>
                    <div class="progress" style="height: 8px;">
                        <div class="progress-bar bg-secondary" style="width: 26%"></div>
                    </div>
                </div>

                <div class="mb-3">
                    <div class="d-flex justify-content-between small fw-bold mb-1">
                        <span>Maxi Dresses</span>
                        <span>18%</span>
                    </div>
                    <div class="progress" style="height: 8px;">
                        <div class="progress-bar" style="width: 18%; background-color: var(--zyra-secondary);"></div>
                    </div>
                </div>

                <div class="mb-3">
                    <div class="d-flex justify-content-between small fw-bold mb-1">
                        <span>Leggings & Churidars</span>
                        <span>12%</span>
                    </div>
                    <div class="progress" style="height: 8px;">
                        <div class="progress-bar bg-info" style="width: 12%"></div>
                    </div>
                </div>

                <div>
                    <div class="d-flex justify-content-between small fw-bold mb-1">
                        <span>Nightwear & Loungewear</span>
                        <span>6%</span>
                    </div>
                    <div class="progress" style="height: 8px;">
                        <div class="progress-bar bg-warning" style="width: 6%"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Payout Settlement History -->
    <div class="col-lg-7">
        <div class="seller-card h-100">
            <div class="seller-card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fw-bold"><i class="bi bi-bank me-2 text-muted"></i>Settlement Payouts</h5>
                <span class="badge bg-success-subtle text-success">Next Payout: Friday</span>
            </div>
            <div class="seller-card-body p-0">
                <div class="table-responsive">
                    <table class="table seller-table mb-0">
                        <thead>
                            <tr>
                                <th>Payout Ref</th>
                                <th>Period</th>
                                <th>Amount</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="fw-bold small">PAY-20260901-01</td>
                                <td><small class="text-muted">25 Aug - 31 Aug 2026</small></td>
                                <td class="fw-bold">₹24,850</td>
                                <td><span class="badge bg-success">Settled</span></td>
                            </tr>
                            <tr>
                                <td class="fw-bold small">PAY-20260825-02</td>
                                <td><small class="text-muted">18 Aug - 24 Aug 2026</small></td>
                                <td class="fw-bold">₹19,420</td>
                                <td><span class="badge bg-success">Settled</span></td>
                            </tr>
                            <tr>
                                <td class="fw-bold small">PAY-20260818-03</td>
                                <td><small class="text-muted">11 Aug - 17 Aug 2026</small></td>
                                <td class="fw-bold">₹31,100</td>
                                <td><span class="badge bg-success">Settled</span></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
