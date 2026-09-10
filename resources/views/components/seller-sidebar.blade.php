<!-- Seller Sidebar Navigation -->
<aside class="col-lg-2 col-xl-2 col-12 seller-sidebar collapse d-lg-block" id="sellerSidebarCollapse">
    <div class="seller-sidebar-head d-lg-none d-flex justify-content-between align-items-center mb-3">
        <strong class="text-uppercase small text-muted">Seller Navigation</strong>
        <button type="button" class="btn-close text-reset" data-bs-toggle="collapse" data-bs-target="#sellerSidebarCollapse"></button>
    </div>
    <ul class="nav flex-column seller-nav">
        <li class="seller-nav-item">
            <a href="{{ route('seller.dashboard') }}" class="seller-nav-link {{ request()->routeIs('seller.dashboard') ? 'active' : '' }}">
                <i class="bi bi-speedometer2 me-2"></i> Dashboard
            </a>
        </li>
        <li class="seller-nav-item">
            <a href="{{ route('seller.products') }}" class="seller-nav-link {{ request()->routeIs('seller.products', 'seller.products.edit') ? 'active' : '' }}">
                <i class="bi bi-box-seam me-2"></i> My Products
            </a>
        </li>
        <li class="seller-nav-item">
            <a href="{{ route('seller.products.add') }}" class="seller-nav-link {{ request()->routeIs('seller.products.add') ? 'active' : '' }}">
                <i class="bi bi-plus-circle me-2"></i> Add Product
            </a>
        </li>
        <li class="seller-nav-item">
            <a href="{{ route('seller.inventory') }}" class="seller-nav-link {{ request()->routeIs('seller.inventory') ? 'active' : '' }}">
                <i class="bi bi-boxes me-2"></i> Inventory
            </a>
        </li>
        <li class="seller-nav-item">
            <a href="{{ route('seller.sales') }}" class="seller-nav-link {{ request()->routeIs('seller.sales') ? 'active' : '' }}">
                <i class="bi bi-receipt me-2"></i> Orders & Sales
            </a>
        </li>
        <li class="seller-nav-item">
            <a href="{{ route('queries') }}" class="seller-nav-link {{ request()->routeIs('queries') ? 'active' : '' }}">
                <i class="bi bi-chat-left-text me-2"></i> Customer Queries
            </a>
        </li>
        <li class="seller-nav-item">
            <a href="{{ route('seller.analytics') }}" class="seller-nav-link {{ request()->routeIs('seller.analytics') ? 'active' : '' }}">
                <i class="bi bi-graph-up-arrow me-2"></i> Analytics
            </a>
        </li>
        <li class="seller-nav-item">
            <a href="{{ route('seller.settings') }}" class="seller-nav-link {{ request()->routeIs('seller.settings') ? 'active' : '' }}">
                <i class="bi bi-gear me-2"></i> Store Settings
            </a>
        </li>
        <li class="seller-nav-item mt-4 pt-3 border-top">
            <a href="{{ route('home') }}" class="seller-nav-link text-muted">
                <i class="bi bi-shop me-2"></i> View Buyer Store
            </a>
        </li>
    </ul>
</aside>