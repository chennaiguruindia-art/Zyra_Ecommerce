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
        const initialQuery = urlParams.get('q') || '';
        const searchInput = document.getElementById('searchPageInput');
        const resultsContainer = document.getElementById('searchResultsContainer');
        const countEl = document.getElementById('searchResultCount');
        const queryDisplayEl = document.getElementById('searchQueryDisplay');
        const emptyState = document.getElementById('searchEmptyState');

        if (searchInput) {
            searchInput.value = initialQuery;
            searchInput.addEventListener('input', (e) => {
                clearTimeout(this.debounceTimer);
                this.debounceTimer = setTimeout(() => {
                    this.filterSearchPage(e.target.value.trim(), resultsContainer, countEl, queryDisplayEl, emptyState);
                }, 200);
            });
        }

        this.filterSearchPage(initialQuery, resultsContainer, countEl, queryDisplayEl, emptyState);
    },

    filterSearchPage(query, container, countEl, queryDisplayEl, emptyState) {
        if (!container) return;

        const allProducts = Array.isArray(window.ZYRA_SEARCH_PRODUCTS)
            ? window.ZYRA_SEARCH_PRODUCTS
            : [];

        let results = [];
        if (!query) {
            results = allProducts;
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

        if (countEl) countEl.textContent = `${results.length} Products Found`;

        if (results.length === 0) {
            container.innerHTML = '';
            if (emptyState) emptyState.style.display = 'block';
            return;
        }

        if (emptyState) emptyState.style.display = 'none';

        let html = '<div class="row g-4">';
        results.forEach(p => {
            html += `
                <div class="col-6 col-md-4 col-lg-3">
                    <div class="zyra-product-card">
                        <div class="zyra-product-thumb">
                            <img src="${p.image}" alt="${this.escapeHtml(p.name)}" loading="lazy">
                            <div class="zyra-badge-stack">
                                ${p.badge ? `<span class="badge-zyra-${p.badge.toLowerCase().replace(/\s+/g, '')}">${p.badge}</span>` : ''}
                                ${p.discount ? `<span class="badge-zyra-discount">${p.discount}% OFF</span>` : ''}
                            </div>
                            <button type="button" class="zyra-wishlist-btn" data-product-id="${p.id}" onclick="ZyraWishlist.toggleWishlist(${p.id}, this)" title="Love it" aria-label="Love it">
                                <i class="bi bi-heart"></i>
                            </button>
                            <div class="zyra-card-actions">
                                <button type="button" class="btn-card-quickview" onclick="ZyraApp.openQuickView(${this.escapeAttr(JSON.stringify(p))})">
                                    <i class="bi bi-eye"></i> Quick View
                                </button>
                                <button type="button" class="btn-card-addcart" onclick="ZyraCart.addToCart(${p.id}, null, null, 1, ${this.escapeAttr(JSON.stringify(p))})" title="Add to Cart">
                                    <i class="bi bi-bag-plus"></i>
                                </button>
                            </div>
                        </div>
                        <div class="zyra-product-body">
                            <span class="zyra-product-category">${p.category}</span>
                            <h6 class="zyra-product-title">
                                <a href="/product/${p.id}">${this.escapeHtml(p.name)}</a>
                            </h6>
                            <div class="zyra-product-rating">
                                <span>${'★'.repeat(Math.round(p.rating))}${'☆'.repeat(5 - Math.round(p.rating))}</span>
                                <span class="review-count">(${p.reviews})</span>
                            </div>
                            <div class="zyra-product-price-box">
                                <span class="zyra-current-price">₹${p.price}</span>
                                ${p.old_price ? `<span class="zyra-old-price">₹${p.old_price}</span>` : ''}
                                ${p.discount ? `<span class="zyra-discount-tag">${p.discount}% OFF</span>` : ''}
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
