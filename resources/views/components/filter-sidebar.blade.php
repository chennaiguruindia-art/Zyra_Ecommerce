<div class="zyra-filter-sidebar-wrapper">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="fw-bold m-0"><i class="bi bi-funnel me-1"></i> Filters</h5>
        <button type="button" id="clearAllFiltersBtn" class="btn btn-sm btn-link text-danger text-decoration-none p-0">
            Clear All
        </button>
    </div>

    <!-- Categories Filter -->
    <div class="zyra-filter-card">
        <div class="zyra-filter-heading">Category</div>
        <ul class="zyra-filter-list">
            <li class="zyra-filter-item">
                <label>
                    <span>
                        <input type="radio" name="filterCategory" class="filter-category-radio" value="all" checked> All Categories
                    </span>
                    <span class="text-muted small">40</span>
                </label>
            </li>
            @foreach($navCategories ?? [] as $filterCat)
                <li class="zyra-filter-item">
                    <label>
                        <span>
                            <input type="radio" name="filterCategory" class="filter-category-radio" value="{{ $filterCat['slug'] }}"> {{ $filterCat['name'] }}
                        </span>
                        <span class="text-muted small">{{ $filterCat['count'] ?? 0 }}</span>
                    </label>
                </li>
            @endforeach
        </ul>
    </div>

    <!-- Price Range Filter -->
    <div class="zyra-filter-card">
        <div class="zyra-filter-heading">Price Range</div>
        <ul class="zyra-filter-list">
            <li class="zyra-filter-item">
                <label>
                    <span><input type="radio" name="filterPrice" class="filter-price-radio" value="all" checked> All Prices</span>
                </label>
            </li>
            <li class="zyra-filter-item">
                <label>
                    <span><input type="radio" name="filterPrice" class="filter-price-radio" value="0-500"> Under ₹500</span>
                </label>
            </li>
            <li class="zyra-filter-item">
                <label>
                    <span><input type="radio" name="filterPrice" class="filter-price-radio" value="500-1000"> ₹500 - ₹1,000</span>
                </label>
            </li>
            <li class="zyra-filter-item">
                <label>
                    <span><input type="radio" name="filterPrice" class="filter-price-radio" value="1000-1500"> ₹1,000 - ₹1,500</span>
                </label>
            </li>
            <li class="zyra-filter-item">
                <label>
                    <span><input type="radio" name="filterPrice" class="filter-price-radio" value="1500-5000"> ₹1,500 & Above</span>
                </label>
            </li>
        </ul>
    </div>

    <!-- Size Filter -->
    <div class="zyra-filter-card">
        <div class="zyra-filter-heading">Size</div>
        <div class="d-flex flex-wrap gap-2">
            @foreach(['XS', 'S', 'M', 'L', 'XL', 'XXL'] as $size)
                <label class="btn btn-outline-secondary btn-sm p-1 px-2 mb-0" style="font-size: 0.8rem; cursor: pointer;">
                    <input type="checkbox" class="d-none filter-size-check" value="{{ $size }}" onchange="this.parentElement.classList.toggle('active', this.checked); this.parentElement.classList.toggle('btn-dark', this.checked); this.parentElement.classList.toggle('text-white', this.checked);">
                    {{ $size }}
                </label>
            @endforeach
        </div>
    </div>

    <!-- Color Filter -->
    <div class="zyra-filter-card">
        <div class="zyra-filter-heading">Color</div>
        <div class="color-swatches-grid">
            @php
                $colors = [
                    'Black' => '#1a1a1a',
                    'White' => '#ffffff',
                    'Pink' => '#e8b4b8',
                    'Blue' => '#3498db',
                    'Red' => '#c0392b',
                    'Green' => '#27ae60',
                    'Yellow' => '#f1c40f',
                ];
            @endphp
            @foreach($colors as $name => $hex)
                <label class="color-swatch-btn" style="background-color: {{ $hex }};" title="{{ $name }}">
                    <input type="checkbox" class="d-none filter-color-check" value="{{ $name }}" onchange="this.parentElement.classList.toggle('active', this.checked);">
                </label>
            @endforeach
        </div>
    </div>

    <!-- Customer Rating Filter -->
    <div class="zyra-filter-card">
        <div class="zyra-filter-heading">Rating</div>
        <ul class="zyra-filter-list">
            <li class="zyra-filter-item">
                <label>
                    <span><input type="radio" name="filterRating" class="filter-rating-radio" value="0" checked> Any Rating</span>
                </label>
            </li>
            <li class="zyra-filter-item">
                <label>
                    <span><input type="radio" name="filterRating" class="filter-rating-radio" value="4.5"> <span class="text-warning">★★★★½</span> & up</span>
                </label>
            </li>
            <li class="zyra-filter-item">
                <label>
                    <span><input type="radio" name="filterRating" class="filter-rating-radio" value="4.0"> <span class="text-warning">★★★★☆</span> & up</span>
                </label>
            </li>
            <li class="zyra-filter-item">
                <label>
                    <span><input type="radio" name="filterRating" class="filter-rating-radio" value="3.5"> <span class="text-warning">★★★½☆</span> & up</span>
                </label>
            </li>
        </ul>
    </div>
</div>
