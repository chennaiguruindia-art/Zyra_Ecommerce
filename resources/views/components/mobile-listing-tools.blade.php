{{-- Mobile listing tools: toolbar (Filter | title | Sort) + category chips + sort bottom sheet.
     Expects: $toolsTitle (string), $toolsActive (category slug or ''), $toolsCount (int).
     Chips are built from shared $navCategories. Drives the existing #shopSortSelect + ZyraApp filters. --}}
<!-- Mobile App-Style Toolbar -->
<div class="zyra-mobile-toolbar d-lg-none">
    <button type="button" class="zyra-mobile-tool-btn" onclick="ZyraApp.toggleMobileFilters(true)">
        <i class="bi bi-funnel"></i>
        <span>Filter</span>
    </button>

    <div class="zyra-mobile-tool-title">
        <div class="zyra-mobile-tool-title-main">{{ $toolsTitle }}</div>
        <span class="text-muted">Showing {{ $toolsCount }} products</span>
    </div>

    <button type="button" class="zyra-mobile-tool-btn" onclick="ZyraApp.toggleMobileSort(true)">
        <i class="bi bi-arrow-down-up"></i>
        <span>Sort</span>
    </button>
</div>

<!-- Mobile Category Chips -->
<div class="zyra-cat-chips d-lg-none" id="mobileCatChips">
    <button type="button" class="zyra-cat-chip {{ ($toolsActive ?? '') === '' ? 'active' : '' }}"
        data-cat="" onclick="ZyraApp.selectMobileChip(this)">All</button>
    @foreach($navCategories ?? [] as $navCat)
        <button type="button" class="zyra-cat-chip {{ ($toolsActive ?? '') === ($navCat['slug'] ?? '') ? 'active' : '' }}"
            data-cat="{{ $navCat['slug'] ?? '' }}" onclick="ZyraApp.selectMobileChip(this)">{{ $navCat['name'] ?? '' }}</button>
    @endforeach
</div>

<!-- Sort Bottom Sheet -->
<div class="zyra-sort-sheet" id="mobileSortSheet">
    <div class="zyra-sort-sheet-handle"></div>
    <h6 class="zyra-sort-sheet-title">Sort By</h6>
    <div class="zyra-sort-options" id="mobileSortOptions">
        <button type="button" class="zyra-sort-option active" data-sort="featured">Featured</button>
        <button type="button" class="zyra-sort-option" data-sort="newest">Newest Arrivals</button>
        <button type="button" class="zyra-sort-option" data-sort="price-low">Price: Low to High</button>
        <button type="button" class="zyra-sort-option" data-sort="price-high">Price: High to Low</button>
        <button type="button" class="zyra-sort-option" data-sort="rating">Best Rated</button>
        <button type="button" class="zyra-sort-option" data-sort="popular">Most Popular</button>
    </div>
</div>

@include('components.mobile-filter-sheet')