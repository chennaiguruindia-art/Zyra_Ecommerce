{{-- Flipkart-style mobile filter sheet (two-pane). Drives the existing sidebar inputs,
     so the same ZyraApp filter engine applies. Included on all listing pages. --}}
<div class="mfilter d-lg-none" id="mobileFilterSheet" aria-hidden="true">
    <div class="mfilter-head">
        <button type="button" class="mfilter-back" aria-label="Close filters" onclick="ZyraApp.toggleMobileFilters(false)">&lsaquo;</button>
        <strong>Filters</strong>
        <button type="button" class="mfilter-clear" id="mobileFilterClear">CLEAR ALL</button>
    </div>
    <div class="mfilter-body">
        <div class="mfilter-tabs" role="tablist">
            <button type="button" class="mfilter-tab active" data-pane="category">Category</button>
            <button type="button" class="mfilter-tab" data-pane="size">Size</button>
            <button type="button" class="mfilter-tab" data-pane="color">Color</button>
            <button type="button" class="mfilter-tab" data-pane="price">Price</button>
            <button type="button" class="mfilter-tab" data-pane="rating">Rating</button>
        </div>
        <div class="mfilter-panes">
            <div class="mfilter-pane active" data-pane="category">
                <label class="mfilter-row"><input type="radio" name="mf-category" class="mf-category" value="all" checked> <span>All Categories</span></label>
                @foreach($navCategories ?? [] as $navCat)
                    <label class="mfilter-row"><input type="radio" name="mf-category" class="mf-category" value="{{ $navCat['slug'] ?? '' }}"> <span>{{ $navCat['name'] ?? '' }}</span> <em>{{ $navCat['count'] ?? 0 }}</em></label>
                @endforeach
            </div>
            <div class="mfilter-pane" data-pane="size">
                <div class="mfilter-chips">
                    @foreach(['XS', 'S', 'M', 'L', 'XL', 'XXL'] as $size)
                        <label class="mfilter-chip"><input type="checkbox" class="mf-size" value="{{ $size }}"> {{ $size }}</label>
                    @endforeach
                </div>
            </div>
            <div class="mfilter-pane" data-pane="color">
                @php
                    // Only colors actually used by products — no defaults/unused colors.
                    $sheetColors = \App\Models\Color::query()
                        ->whereIn('id', function ($q) { $q->select('color_id')->from('product_color'); })
                        ->orderBy('name')->get();
                @endphp
                @forelse($sheetColors as $color)
                    <label class="mfilter-row"><input type="checkbox" class="mf-color" value="{{ $color->name }}"> <span class="mfilter-dot" style="background-color: {{ $color->hex_code ?? '#cccccc' }};"></span> <span>{{ $color->name }}</span></label>
                @empty
                    <p class="text-muted small px-3 py-2">No colors available</p>
                @endforelse
            </div>
            <div class="mfilter-pane" data-pane="price">
                <label class="mfilter-row"><input type="radio" name="mf-price" class="mf-price" value="all" checked> <span>All Prices</span></label>
                <label class="mfilter-row"><input type="radio" name="mf-price" class="mf-price" value="0-500"> <span>Under ₹500</span></label>
                <label class="mfilter-row"><input type="radio" name="mf-price" class="mf-price" value="500-1000"> <span>₹500 – ₹1,000</span></label>
                <label class="mfilter-row"><input type="radio" name="mf-price" class="mf-price" value="1000-1500"> <span>₹1,000 – ₹1,500</span></label>
                <label class="mfilter-row"><input type="radio" name="mf-price" class="mf-price" value="1500-5000"> <span>₹1,500 &amp; Above</span></label>
            </div>
            <div class="mfilter-pane" data-pane="rating">
                <label class="mfilter-row"><input type="radio" name="mf-rating" class="mf-rating" value="0" checked> <span>Any Rating</span></label>
                <label class="mfilter-row"><input type="radio" name="mf-rating" class="mf-rating" value="4.5"> <span class="text-warning">★★★★½</span> <span>&amp; up</span></label>
                <label class="mfilter-row"><input type="radio" name="mf-rating" class="mf-rating" value="4.0"> <span class="text-warning">★★★★☆</span> <span>&amp; up</span></label>
                <label class="mfilter-row"><input type="radio" name="mf-rating" class="mf-rating" value="3.5"> <span class="text-warning">★★★½☆</span> <span>&amp; up</span></label>
            </div>
        </div>
    </div>
    <div class="mfilter-foot">
        <span id="mobileFilterCount">products found</span>
        <button type="button" id="mobileFilterApply">Apply</button>
    </div>
</div>

<style>
.mfilter { position: fixed; top: 0; right: 0; bottom: 0; width: min(430px, 100%); background: #fff; z-index: 1060; display: flex; flex-direction: column; transform: translateX(105%); transition: transform .3s ease; box-shadow: -8px 0 40px rgba(0,0,0,.18); }
.mfilter.open { transform: translateX(0); }
.mfilter-head { display: flex; align-items: center; gap: 10px; padding: 12px 16px; border-bottom: 1px solid #f0ebe4; }
.mfilter-back { border: none; background: none; font-size: 1.8rem; line-height: 1; color: #333; padding: 0 4px; cursor: pointer; }
.mfilter-head strong { font-size: 1rem; }
.mfilter-clear { margin-left: auto; border: none; background: none; color: #8d5a5a; font-size: .75rem; font-weight: 700; cursor: pointer; }
.mfilter-body { flex: 1; display: flex; min-height: 0; }
.mfilter-tabs { flex: 0 0 132px; background: #f4f1ec; overflow-y: auto; }
.mfilter-tab { display: block; width: 100%; border: none; background: none; text-align: left; font-size: .83rem; font-weight: 600; color: #555; padding: 13px 14px; border-left: 3px solid transparent; cursor: pointer; }
.mfilter-tab.active { background: #fff; color: #8d5a5a; border-left-color: #8d5a5a; }
.mfilter-panes { flex: 1; overflow-y: auto; padding: 8px 0 16px; }
.mfilter-pane { display: none; }
.mfilter-pane.active { display: block; }
.mfilter-row { display: flex; align-items: center; gap: 10px; font-size: .87rem; color: #333; padding: 9px 16px; cursor: pointer; }
.mfilter-row input { accent-color: #8d5a5a; width: 16px; height: 16px; flex: 0 0 auto; cursor: pointer; }
.mfilter-row em { margin-left: auto; font-style: normal; font-size: .72rem; color: #999; }
.mfilter-dot { width: 18px; height: 18px; border-radius: 50%; border: 1px solid #ddd; flex: 0 0 auto; }
.mfilter-chips { display: flex; flex-wrap: wrap; gap: 8px; padding: 12px 16px; }
.mfilter-chip { border: 1px solid #e0d6cf; border-radius: 8px; font-size: .83rem; font-weight: 600; padding: 8px 14px; cursor: pointer; color: #555; }
.mfilter-chip input { display: none; }
.mfilter-chip.on { background: #18181b; border-color: #18181b; color: #fff; }
.mfilter-foot { display: flex; align-items: center; border-top: 1px solid #f0ebe4; padding: 10px 16px calc(10px + env(safe-area-inset-bottom)); gap: 12px; }
.mfilter-foot span { font-size: .8rem; color: #666; }
.mfilter-foot button { margin-left: auto; border: none; background: #8d5a5a; color: #fff; font-weight: 700; font-size: .85rem; border-radius: 8px; padding: 10px 34px; cursor: pointer; }
</style>
