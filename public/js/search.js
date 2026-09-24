/**
 * ZYRA - Live Search & Search Page Manager
 */

const ZyraSearch = {
    debounceTimer: null,

    init() {
        this.bindHeaderSearch();
        if (window.location.pathname.includes('/search')) {
            this.initSearchPage();
        }
    },

    bindHeaderSearch() {
        const desktopInput = document.getElementById('headerSearchInput');
        const mobileInput = document.getElementById('mobileSearchInput');
        const dropdown = document.getElementById('headerSearchDropdown');

        const handleInput = (e) => {
            const query = e.target.value.trim();
            clearTimeout(this.debounceTimer);

            if (query.length < 2) {
                if (dropdown) {
                    dropdown.classList.remove('active');
                    dropdown.innerHTML = '';
                }
                return;
            }

            this.debounceTimer = setTimeout(() => {
                this.performLiveSearch(query, dropdown);
            }, 250);
        };

        if (desktopInput) {
            desktopInput.addEventListener('input', handleInput);
            desktopInput.addEventListener('focus', handleInput);
            desktopInput.addEventListener('keypress', (e) => {
                if (e.key === 'Enter') {
                    window.location.href = `/search?q=${encodeURIComponent(desktopInput.value.trim())}`;
                }
            });
        }

        if (mobileInput) {
            mobileInput.addEventListener('keypress', (e) => {
                if (e.key === 'Enter') {
                    window.location.href = `/search?q=${encodeURIComponent(mobileInput.value.trim())}`;
                }
            });
        }

        // Close search dropdown on click outside
        document.addEventListener('click', (e) => {
            if (dropdown && !dropdown.contains(e.target) && (!desktopInput || !desktopInput.contains(e.target))) {
                dropdown.classList.remove('active');
            }
        });
    },

    performLiveSearch(query, dropdown) {
        if (!dropdown) return;

        fetch(`/search/live?q=${encodeURIComponent(query)}`)
            .then(res => res.json())
            .then(data => {
                const results = data.products || [];
                this.renderLiveDropdown(query, results, dropdown);
            })
            .catch(() => {
                if (window.ZyraDB) {
                    const fallback = window.ZyraDB.searchProducts(query);
                    this.renderLiveDropdown(query, fallback, dropdown);
                }
            });
    },

    renderLiveDropdown(query, results, dropdown) {
        dropdown.classList.add('active');

        if (results.length === 0) {
            dropdown.innerHTML = `
                <div class="text-center py-4 text-muted">
                    <i class="bi bi-search mb-2 d-block" style="font-size: 1.5rem;"></i>
                    <p class="mb-0 small">No products found for "<strong>${this.escapeHtml(query)}</strong>"</p>
                </div>
            `;
            return;
        }

        const previewItems = results.slice(0, 5);
        let html = `
            <div class="d-flex justify-content-between align-items-center mb-2 pb-2 border-bottom">
                <small class="text-muted text-uppercase fw-bold" style="font-size: 0.72rem;">Products (${results.length})</small>
                <a href="/search?q=${encodeURIComponent(query)}" class="small text-decoration-none fw-bold" style="color: var(--zyra-primary);">View All</a>
            </div>
            <div class="live-search-list">
        `;

        previewItems.forEach(item => {
            html += `
                <a href="/product/${item.id}" class="d-flex align-items-center gap-2 py-2 border-bottom text-decoration-none text-dark hover-bg">
                    <img src="${item.image}" alt="${this.escapeHtml(item.name)}" style="width: 42px; height: 52px; object-fit: cover; border-radius: 4px;">
                    <div class="overflow-hidden flex-grow-1">
                        <div class="text-truncate fw-semibold small">${this.escapeHtml(item.name)}</div>
                        <div class="d-flex align-items-center gap-2">
                            <span class="text-muted" style="font-size: 0.72rem;">${item.category}</span>
                            <span class="fw-bold small text-dark">₹${item.price}</span>
                        </div>
                    </div>
                </a>
            `;
        });

        html += `
            </div>
            <div class="mt-2 text-center">
                <a href="/search?q=${encodeURIComponent(query)}" class="btn btn-sm btn-zyra-primary w-100 py-1" style="font-size: 0.78rem;">
                    See all ${results.length} results
                </a>
            </div>
        `;

        dropdown.innerHTML = html;
    },

    initSearchPage() {
        const urlParams = new URLSearchParams(window.location.search);
        this.searchState = {
            query: urlParams.get('q') || '',
            sort: 'relevance',
        };

        const rerender = () => this.renderSearchResults();

        const sortRow = document.getElementById('searchSortRow');
        if (sortRow) {
            sortRow.addEventListener('click', (e) => {
                const btn = e.target.closest('.msrch-sort[data-sort]');
                if (!btn) return;
                sortRow.querySelectorAll('.msrch-sort[data-sort]').forEach((b) => b.classList.remove('active'));
                btn.classList.add('active');
                this.searchState.sort = btn.dataset.sort;
                rerender();
            });
        }

        document.querySelectorAll('.msrch-f-cat, .msrch-f-price, .msrch-f-disc, .msrch-f-rate').forEach((el) => {
            el.addEventListener('change', rerender);
        });

        const clearBtn = document.getElementById('searchClearFilters');
        if (clearBtn) {
            clearBtn.addEventListener('click', () => {
                document.querySelectorAll('.msrch-f-cat').forEach((c) => { c.checked = false; });
                document.querySelectorAll('.msrch-f-price, .msrch-f-disc, .msrch-f-rate').forEach((r) => {
                    const first = r.name ? document.querySelector(`input[name="${r.name}"]`) : null;
                    if (first) first.checked = true;
                });
                rerender();
            });
        }

        const filterToggle = document.getElementById('searchFilterToggle');
        const filterCol = document.getElementById('searchFilterCol');
        if (filterToggle && filterCol) {
            filterToggle.addEventListener('click', () => {
                filterCol.classList.toggle('d-none');
                window.scrollTo({ top: 0, behavior: 'smooth' });
            });
        }

        this.renderSearchResults();
    },

    getSearchFilters() {
        const cats = Array.from(document.querySelectorAll('.msrch-f-cat:checked')).map((c) => c.value.toLowerCase());
        const price = document.querySelector('.msrch-f-price:checked')?.value || '';
        const disc = parseFloat(document.querySelector('.msrch-f-disc:checked')?.value || '') || 0;
        const rate = parseFloat(document.querySelector('.msrch-f-rate:checked')?.value || '') || 0;
        let minPrice = null;
        let maxPrice = null;
        if (price) {
            const parts = price.split('-');
            minPrice = parseFloat(parts[0]);
            maxPrice = parseFloat(parts[1]);
        }
        return { cats, minPrice, maxPrice, disc, rate };
    },

    renderSearchResults() {
        const container = document.getElementById('searchResultsContainer');
        const countEl = document.getElementById('searchResultCount');
        const queryDisplayEl = document.getElementById('searchQueryDisplay');
        const emptyState = document.getElementById('searchEmptyState');
        if (!container) return;

        const query = (this.searchState && this.searchState.query) || '';
        const sort = (this.searchState && this.searchState.sort) || 'relevance';
        const filters = this.getSearchFilters();

        const allProducts = Array.isArray(window.ZYRA_SEARCH_PRODUCTS)
            ? window.ZYRA_SEARCH_PRODUCTS
            : [];

        let results = [];
        if (!query) {
            results = allProducts.slice();
            if (queryDisplayEl) queryDisplayEl.textContent = 'All Products';
        } else {
            const q = query.toLowerCase();
            results = allProducts.filter(p =>
                (p.name || '').toLowerCase().includes(q) ||
                (p.category || '').toLowerCase().includes(q) ||
                (p.subcategory || '').toLowerCase().includes(q) ||
                (p.description || '').toLowerCase().includes(q) ||
                (p.material || '').toLowerCase().includes(q)
            );
            if (queryDisplayEl) queryDisplayEl.textContent = `"${query}"`;
        }

        // Sidebar filters
        if (filters.cats.length) {
            results = results.filter((p) => filters.cats.includes((p.category || '').toLowerCase()));
        }
        if (filters.minPrice !== null) {
            results = results.filter((p) => parseFloat(p.price) >= filters.minPrice);
        }
        if (filters.maxPrice !== null) {
            results = results.filter((p) => parseFloat(p.price) <= filters.maxPrice);
        }
        if (filters.disc > 0) {
            results = results.filter((p) => parseFloat(p.discount || 0) >= filters.disc);
        }
        if (filters.rate > 0) {
            results = results.filter((p) => parseFloat(p.rating || 0) >= filters.rate);
        }

        // Sorting
        const priceOf = (p) => parseFloat(p.price) || 0;
        const ratingOf = (p) => parseFloat(p.rating) || 0;
        const discOf = (p) => parseFloat(p.discount) || 0;
        if (sort === 'price-asc') results.sort((a, b) => priceOf(a) - priceOf(b));
        else if (sort === 'price-desc') results.sort((a, b) => priceOf(b) - priceOf(a));
        else if (sort === 'rating') results.sort((a, b) => ratingOf(b) - ratingOf(a));
        else if (sort === 'discount') results.sort((a, b) => discOf(b) - discOf(a));
        else if (sort === 'new') results.sort((a, b) => (b.id || 0) - (a.id || 0));

        if (countEl) countEl.textContent = results.length === 1 ? '1 Product Found' : `${results.length} Products Found`;

        if (results.length === 0) {
            container.innerHTML = '';
            if (emptyState) emptyState.style.display = 'block';
            return;
        }

        if (emptyState) emptyState.style.display = 'none';

        let html = '<div class="row g-3 g-md-4">';
        results.forEach(p => {
            const rating = Math.round(parseFloat(p.rating) || 0);
            const price = parseFloat(p.price) || 0;
            const oldPrice = parseFloat(p.old_price) || 0;
            const discount = parseFloat(p.discount) || 0;
            const freeDel = price >= 999;
            html += `
                <div class="col-6 col-md-4 col-xl-3">
                    <div class="msrch-card">
                        <a class="msrch-thumb" href="/product/${p.id}">
                            <img src="${p.image}" alt="${this.escapeHtml(p.name)}" loading="lazy">
                            <span class="msrch-badges">
                                ${p.badge ? `<span class="badge-zyra-${p.badge.toLowerCase().replace(/\s+/g, '')}">${p.badge}</span>` : ''}
                            </span>
                        </a>
                        <button type="button" class="msrch-wish" data-product-id="${p.id}" onclick="ZyraWishlist.toggleWishlist(${p.id}, this)" title="Wishlist" aria-label="Wishlist">
                            <i class="bi bi-heart"></i>
                        </button>
                        <div class="msrch-body">
                            <div class="msrch-name" title="${this.escapeHtml(p.name)}">
                                <a href="/product/${p.id}">${this.escapeHtml(p.name)}</a>
                            </div>
                            <div class="msrch-price">
                                <span class="now">₹${price.toLocaleString('en-IN')}</span>
                                ${oldPrice > price ? `<s>₹${oldPrice.toLocaleString('en-IN')}</s>` : ''}
                                ${discount > 0 ? `<span class="off">${discount}% off</span>` : ''}
                            </div>
                            <div class="msrch-meta">
                                ${rating > 0 ? `<span class="msrch-rating">${rating} ★ <small>(${(p.reviews ?? 0)})</small></span>` : ''}
                                ${freeDel ? '<span class="msrch-freedel">Free Delivery</span>' : ''}
                            </div>
                        </div>
                    </div>
                </div>
            `;
        });
        html += '</div>';

        container.innerHTML = html;
        if (window.ZyraWishlist) {
            window.ZyraWishlist.syncHeartIcons();
        }
    },

    filterSearchPage(query) {
        if (this.searchState) this.searchState.query = query || '';
        this.renderSearchResults();
    },

    escapeHtml(str) {
        if (!str) return '';
        return str.replace(/[&<>"']/g, m => ({
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        }[m]));
    },

    escapeAttr(str) {
        if (!str) return '';
        return String(str).replace(/&/g, '&amp;').replace(/"/g, '&quot;').replace(/'/g, '&#039;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
    }
};

document.addEventListener('DOMContentLoaded', () => {
    ZyraSearch.init();
});
