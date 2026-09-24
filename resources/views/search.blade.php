@extends('layouts.app')

@section('title', 'Search Products | ZYRA Lifestyle')
@section('meta_description', 'Search and find women\'s tops, cotton leggings, kurtis, maxi dresses and nightwear at ZYRA Lifestyle.')
@section('robots', 'noindex, follow')

@section('content')

<x-breadcrumb :items="[['label' => 'Search', 'url' => '']]" />

<style>
.msrch-title { font-size: 1.35rem; font-weight: 700; }
.msrch-count { font-size: .85rem; color: #6c757d; }
.msrch-sortrow { display: flex; align-items: center; gap: 8px; flex-wrap: wrap; background: #fff; border: 1px solid #ece5e0; border-radius: 12px; padding: 10px 14px; margin: 14px 0 18px; }
.msrch-sortrow .lbl { font-size: .8rem; font-weight: 700; color: #6c757d; text-transform: uppercase; letter-spacing: .5px; margin-right: 2px; }
.msrch-sort { border: 1px solid #e0d6cf; background: #fff; color: #5a4a4a; font-size: .78rem; font-weight: 600; border-radius: 999px; padding: 6px 14px; cursor: pointer; }
.msrch-sort:hover { border-color: #8d5a5a; color: #8d5a5a; }
.msrch-sort.active { background: #18181b; border-color: #18181b; color: #fff; }
.msrch-filterbox { background: #fff; border: 1px solid #ece5e0; border-radius: 12px; padding: 16px; position: sticky; top: 150px; }
.msrch-filterbox h6 { font-size: .8rem; font-weight: 700; text-transform: uppercase; letter-spacing: .5px; color: #6c757d; margin: 16px 0 8px; padding-top: 14px; border-top: 1px solid #f0ebe4; }
.msrch-filterbox h6:first-of-type { margin-top: 12px; }
.msrch-check { display: flex; align-items: center; gap: 8px; font-size: .86rem; padding: 4px 0; cursor: pointer; color: #333; }
.msrch-check input { accent-color: #8d5a5a; width: 15px; height: 15px; cursor: pointer; }
.msrch-check .cnt { margin-left: auto; font-size: .72rem; color: #999; }
.msrch-clear { font-size: .75rem; font-weight: 600; color: #8d5a5a; background: none; border: none; cursor: pointer; padding: 0; }
/* Meesho-style card */
.msrch-card { background: #fff; border: 1px solid #ece5e0; border-radius: 12px; overflow: hidden; height: 100%; transition: box-shadow .2s ease, transform .2s ease; }
.msrch-card:hover { box-shadow: 0 10px 26px rgba(0,0,0,.1); transform: translateY(-3px); }
.msrch-thumb { position: relative; padding-top: 125%; background: #faf8f6; display: block; }
.msrch-thumb img { position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover; }
.msrch-badges { position: absolute; top: 10px; left: 10px; display: flex; flex-direction: column; gap: 5px; }
.msrch-wish { position: absolute; top: 8px; right: 8px; width: 34px; height: 34px; border-radius: 50%; border: none; background: rgba(255,255,255,.92); color: #8d5a5a; font-size: 1rem; display: flex; align-items: center; justify-content: center; cursor: pointer; box-shadow: 0 2px 8px rgba(0,0,0,.1); }
.msrch-wish.active { color: #d6336c; }
.msrch-body { padding: 10px 12px 12px; }
.msrch-name { font-size: .86rem; font-weight: 500; color: #333; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.msrch-name a { color: inherit; text-decoration: none; }
.msrch-price { display: flex; align-items: baseline; gap: 6px; margin-top: 4px; flex-wrap: wrap; }
.msrch-price .now { font-size: 1.05rem; font-weight: 800; }
.msrch-price s { font-size: .78rem; color: #999; }
.msrch-price .off { font-size: .76rem; font-weight: 700; color: #038d2c; }
.msrch-meta { display: flex; align-items: center; gap: 6px; margin-top: 6px; flex-wrap: wrap; }
.msrch-rating { display: inline-flex; align-items: center; gap: 3px; background: #038d2c; color: #fff; font-size: .72rem; font-weight: 700; border-radius: 999px; padding: 2px 8px; }
.msrch-rating small { font-weight: 400; opacity: .9; }
.msrch-freedel { font-size: .7rem; color: #666; background: #f4f4f4; border-radius: 999px; padding: 2px 8px; }
</style>

<div class="container py-4">

    <div class="d-flex align-items-baseline gap-2 flex-wrap">
        <h1 class="msrch-title mb-0">Results for “<span id="searchQueryDisplay">All Products</span>”</h1>
        <span class="msrch-count" id="searchResultCount">Loading…</span>
    </div>

    <div class="msrch-sortrow" id="searchSortRow">
        <span class="lbl">Sort by</span>
        <button type="button" class="msrch-sort active" data-sort="relevance">Relevance</button>
        <button type="button" class="msrch-sort" data-sort="new">Newest</button>
        <button type="button" class="msrch-sort" data-sort="price-asc">Price: Low to High</button>
        <button type="button" class="msrch-sort" data-sort="price-desc">Price: High to Low</button>
        <button type="button" class="msrch-sort" data-sort="rating">Ratings</button>
        <button type="button" class="msrch-sort" data-sort="discount">Discount</button>
        <button type="button" class="msrch-sort d-lg-none ms-auto" id="searchFilterToggle">Filters</button>
    </div>

    <div class="row g-4">
        <aside class="col-lg-3 d-none d-lg-block" id="searchFilterCol">
            <div class="msrch-filterbox">
                <div class="d-flex align-items-center justify-content-between">
                    <strong style="font-size:.9rem;">FILTERS</strong>
                    <button type="button" class="msrch-clear" id="searchClearFilters">CLEAR ALL</button>
                </div>

                <h6>Category</h6>
                @foreach(($categories ?? []) as $cat)
                    <label class="msrch-check">
                        <input type="checkbox" class="msrch-f-cat" value="{{ $cat['name'] ?? '' }}">
                        {{ $cat['name'] ?? '' }}
                        <span class="cnt">{{ $cat['count'] ?? 0 }}</span>
                    </label>
                @endforeach

                <h6>Price</h6>
                <label class="msrch-check"><input type="radio" name="msrch-price" class="msrch-f-price" value="" checked> All prices</label>
                <label class="msrch-check"><input type="radio" name="msrch-price" class="msrch-f-price" value="0-500"> Under ₹500</label>
                <label class="msrch-check"><input type="radio" name="msrch-price" class="msrch-f-price" value="500-1000"> ₹500 – ₹1000</label>
                <label class="msrch-check"><input type="radio" name="msrch-price" class="msrch-f-price" value="1000-1500"> ₹1000 – ₹1500</label>
                <label class="msrch-check"><input type="radio" name="msrch-price" class="msrch-f-price" value="1500-999999"> Above ₹1500</label>

                <h6>Discount</h6>
                <label class="msrch-check"><input type="radio" name="msrch-disc" class="msrch-f-disc" value="" checked> All items</label>
                <label class="msrch-check"><input type="radio" name="msrch-disc" class="msrch-f-disc" value="10"> 10% and above</label>
                <label class="msrch-check"><input type="radio" name="msrch-disc" class="msrch-f-disc" value="20"> 20% and above</label>
                <label class="msrch-check"><input type="radio" name="msrch-disc" class="msrch-f-disc" value="30"> 30% and above</label>

                <h6>Rating</h6>
                <label class="msrch-check"><input type="radio" name="msrch-rate" class="msrch-f-rate" value="" checked> All ratings</label>
                <label class="msrch-check"><input type="radio" name="msrch-rate" class="msrch-f-rate" value="4"> 4★ &amp; above</label>
                <label class="msrch-check"><input type="radio" name="msrch-rate" class="msrch-f-rate" value="3"> 3★ &amp; above</label>
            </div>
        </aside>

        <div class="col-lg-9">
            <div id="searchResultsContainer"></div>

            @push('scripts')
            <script>
                window.ZYRA_SEARCH_PRODUCTS = @json($allProducts ?? []);
            </script>
            @endpush

            <div id="searchEmptyState" class="text-center py-5 my-4" style="display: none;">
                <div class="mb-3 text-muted" style="font-size: 3.5rem;">
                    <i class="bi bi-search-heart"></i>
                </div>
                <h4 class="fw-bold mb-2">No products found</h4>
                <p class="text-muted small mb-1">Try a different keyword, or clear the filters.</p>
                <div class="d-flex justify-content-center gap-2 mt-3 mb-4 small">
                    <span class="text-muted align-self-center">Popular:</span>
                    <a href="/search?q=kurti" class="badge bg-light text-dark border text-decoration-none">Kurti</a>
                    <a href="/search?q=cotton" class="badge bg-light text-dark border text-decoration-none">Cotton</a>
                    <a href="/search?q=maxi" class="badge bg-light text-dark border text-decoration-none">Maxi Dress</a>
                    <a href="/search?q=leggings" class="badge bg-light text-dark border text-decoration-none">Leggings</a>
                </div>
                <a href="{{ route('shop') }}" class="btn btn-zyra-primary px-4 py-2">
                    View All Products <i class="bi bi-arrow-right ms-1"></i>
                </a>
            </div>
        </div>
    </div>

</div>

@endsection