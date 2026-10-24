<!-- Seller Sidebar Navigation -->
<aside class="seller-sidebar collapse d-lg-block" id="sellerSidebarCollapse">
    <div class="seller-sidebar-head d-lg-none d-flex justify-content-between align-items-center mb-3">
        <strong class="text-uppercase small text-muted">Seller Navigation</strong>
        <button type="button" class="btn-close text-reset" data-bs-toggle="collapse" data-bs-target="#sellerSidebarCollapse"></button>
    </div>
    <ul class="nav flex-column seller-nav">

        {{-- Dashboard --}}
        <li class="seller-nav-item">
            <a href="{{ route('seller.dashboard') }}" class="seller-nav-link {{ request()->routeIs('seller.dashboard') ? 'active' : '' }}">
                <i class="bi bi-speedometer2"></i> <span>Dashboard</span>
            </a>
        </li>

        @php
            $sidebarPendingCount    = \App\Models\Order::where('order_status', 'Pending')->count();
            $sidebarPackingCount    = \App\Models\Order::where('order_status', 'Processing')->count();
            $sidebarDispatchedCount = \App\Models\Order::where('order_status', 'Shipped')->count();
            $currentStage = request('stage', 'pending');
        @endphp

        {{-- ── Orders Section ── --}}
        <li class="seller-nav-section-label">
            <span>Orders &amp; Fulfillment</span>
        </li>

        <li class="seller-nav-item seller-nav-sub-item">
            <a href="{{ route('seller.orders.new') }}?stage=pending"
               class="seller-nav-link seller-nav-sub-link {{ request()->routeIs('seller.orders.new') && $currentStage === 'pending' ? 'active' : '' }}"
               id="sideNavNewOrders" title="Freshly received orders – ready for packing">
                <i class="bi bi-inbox"></i>
                <span>New Orders</span>
                <span class="badge rounded-pill ms-auto {{ $sidebarPendingCount ? 'bg-danger' : 'd-none' }}" id="sideBadgePending">{{ $sidebarPendingCount }}</span>
            </a>
        </li>

        <li class="seller-nav-item seller-nav-sub-item">
            <a href="{{ route('seller.orders.new') }}?stage=processing"
               class="seller-nav-link seller-nav-sub-link {{ request()->routeIs('seller.orders.new') && $currentStage === 'processing' ? 'active' : '' }}"
               id="sideNavPacking" title="Orders currently being packed">
                <i class="bi bi-box-seam"></i>
                <span>In Packing</span>
                <span class="badge rounded-pill ms-auto {{ $sidebarPackingCount ? 'bg-warning text-dark' : 'd-none' }}" id="sideBadgePacking">{{ $sidebarPackingCount }}</span>
            </a>
        </li>

        <li class="seller-nav-item seller-nav-sub-item">
            <a href="{{ route('seller.orders.new') }}?stage=shipped"
               class="seller-nav-link seller-nav-sub-link {{ request()->routeIs('seller.orders.new') && $currentStage === 'shipped' ? 'active' : '' }}"
               id="sideNavDispatched" title="Packed and handed to courier">
                <i class="bi bi-truck"></i>
                <span>Dispatched</span>
                <span class="badge rounded-pill ms-auto {{ $sidebarDispatchedCount ? 'bg-success' : 'd-none' }}" id="sideBadgeDispatched">{{ $sidebarDispatchedCount }}</span>
            </a>
        </li>

        <li class="seller-nav-item">
            <a href="{{ route('seller.sales') }}" class="seller-nav-link {{ request()->routeIs('seller.sales') ? 'active' : '' }}">
                <i class="bi bi-receipt"></i> <span>Orders &amp; Sales</span>
            </a>
        </li>

        {{-- ── Catalogue Section ── --}}
        <li class="seller-nav-section-label">
            <span>Catalogue</span>
        </li>

        <li class="seller-nav-item seller-nav-sub-item">
            <a href="{{ route('seller.products') }}" class="seller-nav-link seller-nav-sub-link {{ request()->routeIs('seller.products', 'seller.products.edit') ? 'active' : '' }}">
                <i class="bi bi-grid"></i> <span>My Products</span>
            </a>
        </li>
        <li class="seller-nav-item seller-nav-sub-item">
            <a href="{{ route('seller.products.add') }}" class="seller-nav-link seller-nav-sub-link {{ request()->routeIs('seller.products.add') ? 'active' : '' }}">
                <i class="bi bi-plus-circle"></i> <span>Add Product</span>
            </a>
        </li>
        <li class="seller-nav-item seller-nav-sub-item">
            <a href="{{ route('seller.inventory') }}" class="seller-nav-link seller-nav-sub-link {{ request()->routeIs('seller.inventory') ? 'active' : '' }}">
                <i class="bi bi-boxes"></i> <span>Inventory</span>
            </a>
        </li>

        {{-- ── Insights Section ── --}}
        <li class="seller-nav-section-label">
            <span>Insights</span>
        </li>

        <li class="seller-nav-item seller-nav-sub-item">
            <a href="{{ route('seller.analytics') }}" class="seller-nav-link seller-nav-sub-link {{ request()->routeIs('seller.analytics') ? 'active' : '' }}">
                <i class="bi bi-graph-up-arrow"></i> <span>Analytics</span>
            </a>
        </li>
        <li class="seller-nav-item seller-nav-sub-item">
            <a href="{{ route('seller.activity') }}" class="seller-nav-link seller-nav-sub-link {{ request()->routeIs('seller.activity*') ? 'active' : '' }}">
                <i class="bi bi-people"></i> <span>Shopping Analytics</span>
            </a>
        </li>

        {{-- ── Support Section ── --}}
        <li class="seller-nav-section-label">
            <span>Support</span>
        </li>

        <li class="seller-nav-item seller-nav-sub-item">
            <a href="{{ route('queries') }}" class="seller-nav-link seller-nav-sub-link {{ request()->routeIs('queries') ? 'active' : '' }}">
                <i class="bi bi-chat-left-text"></i> <span>Customer Queries</span>
            </a>
        </li>
        <li class="seller-nav-item seller-nav-sub-item">
            <a href="{{ route('seller.settings') }}" class="seller-nav-link seller-nav-sub-link {{ request()->routeIs('seller.settings') ? 'active' : '' }}">
                <i class="bi bi-gear"></i> <span>Store Settings</span>
            </a>
        </li>

        {{-- ── Exit ── --}}
        <li class="seller-nav-item mt-3 pt-3 border-top">
            <a href="{{ route('home') }}" class="seller-nav-link seller-nav-exit-link">
                <i class="bi bi-shop"></i> <span>View Buyer Store</span>
            </a>
        </li>

    </ul>
</aside>
