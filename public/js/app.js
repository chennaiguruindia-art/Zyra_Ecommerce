/**
 * ZYRA - Main Application Script
 * Toasts, Quick View, Shop Filtering & Sorting, Pincode Checker, Image Gallery
 */

window.ZyraApp = {
    // Current shop filter state
    shopFilters: {
        category: 'all',
        subcategories: [],
        priceRange: 'all',
        sizes: [],
        colors: [],
        rating: 0,
        inStockOnly: false,
        sort: 'featured'
    },

    init() {
        this.bindNewsletter();
        this.bindContactForm();
        this.bindPincodeChecker();
        this.bindProductGallery();
        this.initShopPage();
        window.addEventListener('zyra:catalog-loaded', () => {
            if (document.getElementById('shopProductGrid') && window.ZyraApp) {
                window.ZyraApp.applyShopFilters();
            }
        });
    },

    // Login guard: browsing is public; purchase/login actions require an account.
    isLoggedIn() {
        return document.body.getAttribute('data-auth') === 'true';
    },

    requireLogin(message = 'Please login to continue.') {
        if (this.isLoggedIn()) return true;
        this.showToast(message, 'warning');
        setTimeout(() => {
            window.location.href = '/login';
        }, 900);
        return false;
    },

    // Toast Notification Manager
    showToast(message, type = 'success') {
        let container = document.getElementById('zyraToastContainer');
        if (!container) {
            container = document.createElement('div');
            container.id = 'zyraToastContainer';
            container.className = 'zyra-toast-container';
            document.body.appendChild(container);
        }

        const icons = {
            success: 'bi-check-circle-fill',
            info: 'bi-info-circle-fill',
            danger: 'bi-exclamation-triangle-fill',
            warning: 'bi-exclamation-circle-fill'
        };

        const toast = document.createElement('div');
        toast.className = `zyra-toast toast-${type}`;
        toast.innerHTML = `
            <i class="bi ${icons[type] || 'bi-info-circle'} fs-5"></i>
            <span class="flex-grow-1">${message}</span>
            <button type="button" class="btn-close btn-close-white ms-2" style="font-size: 0.7rem;"></button>
        `;

        const closeBtn = toast.querySelector('.btn-close');
        closeBtn.addEventListener('click', () => {
            toast.remove();
        });

        container.appendChild(toast);

        setTimeout(() => {
            if (toast.parentElement) {
                toast.style.animation = 'slideInRight 0.3s ease-out reverse';
                setTimeout(() => toast.remove(), 300);
            }
        }, 3500);
    },

    // Quick View Modal
    openQuickView(productOrId) {
        let product = null;

        if (typeof productOrId === 'string') {
            try {
                product = JSON.parse(productOrId);
            } catch (e) {
                product = null;
            }
        }

        if (!product && productOrId && typeof productOrId === 'object') {
            product = productOrId;
        }

        if (!product) {
            const id = parseInt(productOrId);
            if (window.ZyraDB && typeof window.ZyraDB.getProductById === 'function') {
                product = window.ZyraDB.getProductById(id);
            }
            if (!product && Array.isArray(window.ZYRA_SEARCH_PRODUCTS)) {
                product = window.ZYRA_SEARCH_PRODUCTS.find(p => parseInt(p.id) === id);
            }
            if (!product && typeof ZYRA_PRODUCTS !== 'undefined' && Array.isArray(ZYRA_PRODUCTS)) {
                product = ZYRA_PRODUCTS.find(p => parseInt(p.id) === id);
            }
            if (!product) return;
        }

        const modalEl = document.getElementById('quickViewModal');
        if (!modalEl) return;

        const imgEl = document.getElementById('qvProductImage');
        const titleEl = document.getElementById('qvProductTitle');
        const catEl = document.getElementById('qvProductCategory');
        const ratingEl = document.getElementById('qvProductRating');
        const reviewsEl = document.getElementById('qvProductReviews');
        const priceEl = document.getElementById('qvProductPrice');
        const oldPriceEl = document.getElementById('qvProductOldPrice');
        const discountEl = document.getElementById('qvProductDiscount');
        const descEl = document.getElementById('qvProductDescription');
        const sizesWrap = document.getElementById('qvProductSizes');
        const colorsWrap = document.getElementById('qvProductColors');
        const addBtn = document.getElementById('qvAddToCartBtn');
        const viewLink = document.getElementById('qvViewDetailsLink');
        const qtyInput = document.getElementById('qvQuantityInput');

        if (imgEl) imgEl.src = product.image;
        if (titleEl) titleEl.textContent = product.name;
        if (catEl) catEl.textContent = product.category;
        if (ratingEl) ratingEl.textContent = '★'.repeat(Math.round(product.rating)) + '☆'.repeat(5 - Math.round(product.rating));
        if (reviewsEl) reviewsEl.textContent = `(${product.reviews} reviews)`;
        if (priceEl) priceEl.textContent = `₹${product.price}`;
        if (oldPriceEl) oldPriceEl.textContent = product.old_price ? `₹${product.old_price}` : '';
        if (discountEl) discountEl.textContent = product.discount ? `${product.discount}% OFF` : '';
        if (descEl) descEl.textContent = product.description;
        if (viewLink) viewLink.href = `/product/${product.id}`;
        if (qtyInput) qtyInput.value = 1;

        // Populate Sizes
        let selectedSize = product.sizes && product.sizes.length ? product.sizes[0] : 'M';
        if (sizesWrap && product.sizes) {
            let sHtml = '';
            product.sizes.forEach((s, idx) => {
                sHtml += `<button type="button" class="btn btn-outline-dark btn-sm me-1 mb-1 qv-size-btn ${idx === 0 ? 'active' : ''}" data-size="${s}">${s}</button>`;
            });
            sizesWrap.innerHTML = sHtml;

            sizesWrap.querySelectorAll('.qv-size-btn').forEach(btn => {
                btn.addEventListener('click', (e) => {
                    sizesWrap.querySelectorAll('.qv-size-btn').forEach(b => b.classList.remove('active'));
                    e.currentTarget.classList.add('active');
                    selectedSize = e.currentTarget.getAttribute('data-size');
                });
            });
        }

        // Populate Colors
        let selectedColor = product.colors && product.colors.length ? product.colors[0] : 'Standard';
        if (colorsWrap && product.colors) {
            let cHtml = '';
            product.colors.forEach((c, idx) => {
                const code = (product.color_codes && product.color_codes[idx]) || '#1a1a1a';
                cHtml += `
                    <button type="button" class="color-swatch-btn me-1 mb-1 qv-color-btn ${idx === 0 ? 'active' : ''}" 
                        style="background-color: ${code};" data-color="${c}" title="${c}"></button>
                `;
            });
            colorsWrap.innerHTML = cHtml;

            colorsWrap.querySelectorAll('.qv-color-btn').forEach(btn => {
                btn.addEventListener('click', (e) => {
                    colorsWrap.querySelectorAll('.qv-color-btn').forEach(b => b.classList.remove('active'));
                    e.currentTarget.classList.add('active');
                    selectedColor = e.currentTarget.getAttribute('data-color');
                });
            });
        }

        // Add to Cart handler
        if (addBtn) {
            addBtn.onclick = () => {
                const qty = qtyInput ? parseInt(qtyInput.value) || 1 : 1;
                if (window.ZyraCart) {
                    window.ZyraCart.addToCart(product.id, selectedSize, selectedColor, qty, product);
                    const bsModal = bootstrap.Modal.getInstance(modalEl);
                    if (bsModal) bsModal.hide();
                }
            };
        }

        // Show Modal
        if (window.bootstrap) {
            const bsModal = bootstrap.Modal.getOrCreateInstance(modalEl);
            bsModal.show();
        }
    },

    // Pincode Delivery Checker
    bindPincodeChecker() {
        const checkBtn = document.getElementById('checkPincodeBtn');
        const input = document.getElementById('pincodeInput');
        const feedback = document.getElementById('pincodeFeedback');

        if (!checkBtn || !input || !feedback) return;

        checkBtn.addEventListener('click', () => {
            const pin = input.value.trim();
            if (!/^\d{6}$/.test(pin)) {
                feedback.innerHTML = `<span class="text-danger small"><i class="bi bi-x-circle me-1"></i> Please enter a valid 6-digit PIN code.</span>`;
                return;
            }

            // Calculate estimated date (+3 to +5 days)
            const date = new Date();
            date.setDate(date.getDate() + 4);
            const formattedDate = date.toLocaleDateString('en-IN', { weekday: 'short', month: 'short', day: 'numeric' });

            feedback.innerHTML = `
                <div class="alert alert-success py-2 px-3 small mt-2 mb-0">
                    <i class="bi bi-truck me-1"></i> <strong>Delivery available by ${formattedDate}</strong><br>
                    <span class="text-muted">Standard Delivery - Order Anything | Free Delivery Available</span>
                </div>
            `;
        });
    },

    // Product Gallery on Details Page
    bindProductGallery() {
        const mainImage = document.getElementById('productMainImage');
        const thumbs = document.querySelectorAll('.zyra-thumb-item');

        if (!mainImage || thumbs.length === 0) return;

        thumbs.forEach(thumb => {
            thumb.addEventListener('click', () => {
                thumbs.forEach(t => t.classList.remove('active'));
                thumb.classList.add('active');
                const newSrc = thumb.getAttribute('data-img-src') || thumb.querySelector('img').src;
                mainImage.src = newSrc;
            });
        });

        // Hover Zoom effect
        mainImage.addEventListener('mousemove', (e) => {
            const rect = mainImage.getBoundingClientRect();
            const x = ((e.clientX - rect.left) / rect.width) * 100;
            const y = ((e.clientY - rect.top) / rect.height) * 100;
            mainImage.style.transformOrigin = `${x}% ${y}%`;
            mainImage.style.transform = 'scale(1.35)';
        });

        mainImage.addEventListener('mouseleave', () => {
            mainImage.style.transform = 'scale(1)';
        });
    },

    // === Mobile App-Style Shop Toolbar (filter drawer + sort sheet + category chips) ===
    toggleMobileFilters(forceOpen) {
        const sheet = document.getElementById('mobileFilterSheet');
        if (sheet) {
            const backdrop = document.getElementById('shopFilterBackdrop');
            const shouldOpen = forceOpen === undefined ? !sheet.classList.contains('open') : forceOpen;
            sheet.classList.toggle('open', shouldOpen);
            sheet.setAttribute('aria-hidden', shouldOpen ? 'false' : 'true');
            if (backdrop) backdrop.classList.toggle('show', shouldOpen);
            document.body.classList.toggle('overflow-hidden', shouldOpen);
            if (shouldOpen) this.syncMobileFilterSheet();
            return;
        }
        const filterCol = document.getElementById('shopFilterCol');
        const backdrop = document.getElementById('shopFilterBackdrop');
        if (!filterCol) return;

        const shouldOpen = forceOpen === undefined ? !filterCol.classList.contains('open') : forceOpen;
        filterCol.classList.toggle('open', shouldOpen);
        if (backdrop) backdrop.classList.toggle('show', shouldOpen);
        document.body.classList.toggle('overflow-hidden', shouldOpen);
    },

    toggleMobileSort(forceOpen) {
        const sheet = document.getElementById('mobileSortSheet');
        const backdrop = document.getElementById('shopFilterBackdrop');
        if (!sheet) return;

        const shouldOpen = forceOpen === undefined ? !sheet.classList.contains('open') : forceOpen;

        // Only one overlay at a time.
        if (shouldOpen) {
            const filterCol = document.getElementById('shopFilterCol');
            if (filterCol) filterCol.classList.remove('open');
        }
        sheet.classList.toggle('open', shouldOpen);
        if (backdrop) backdrop.classList.toggle('show', shouldOpen);
        document.body.classList.toggle('overflow-hidden', shouldOpen);
    },

    // === Flipkart-style mobile filter sheet (mirrors the sidebar inputs) ===
    bindMobileFilterSheet() {
        const sheet = document.getElementById('mobileFilterSheet');
        if (!sheet || sheet.dataset.bound) return;
        sheet.dataset.bound = '1';

        const mirrorRadio = (sheetSel, sideSel) => {
            sheet.querySelectorAll(sheetSel).forEach(el => {
                el.addEventListener('change', () => {
                    const side = document.querySelector(`${sideSel}[value="${CSS.escape(el.value)}"]`);
                    if (side) {
                        side.checked = true;
                        side.dispatchEvent(new Event('change', { bubbles: true }));
                    }
                    this.updateMobileFilterCount();
                });
            });
        };
        const mirrorCheck = (sheetSel, sideSel, chipClass) => {
            sheet.querySelectorAll(sheetSel).forEach(el => {
                el.addEventListener('change', () => {
                    const side = document.querySelector(`${sideSel}[value="${CSS.escape(el.value)}"]`);
                    if (side) {
                        side.checked = el.checked;
                        if (chipClass && side.parentElement) {
                            side.parentElement.classList.toggle(chipClass, el.checked);
                        }
                        side.dispatchEvent(new Event('change', { bubbles: true }));
                    }
                    if (el.closest('.mfilter-chip')) {
                        el.closest('.mfilter-chip').classList.toggle('on', el.checked);
                    }
                    this.updateMobileFilterCount();
                });
            });
        };

        mirrorRadio('.mf-category', '.filter-category-radio');
        mirrorRadio('.mf-price', '.filter-price-radio');
        mirrorRadio('.mf-rating', '.filter-rating-radio');
        mirrorCheck('.mf-size', '.filter-size-check', null);
        mirrorCheck('.mf-color', '.filter-color-check', null);

        // Size chips + color rows visual state
        sheet.querySelectorAll('.mf-size').forEach(el => {
            el.closest('.mfilter-chip')?.classList.toggle('on', el.checked);
        });

        // Tabs
        sheet.querySelectorAll('.mfilter-tab').forEach(tab => {
            tab.addEventListener('click', () => {
                sheet.querySelectorAll('.mfilter-tab').forEach(t => t.classList.remove('active'));
                sheet.querySelectorAll('.mfilter-pane').forEach(p => p.classList.remove('active'));
                tab.classList.add('active');
                sheet.querySelector(`.mfilter-pane[data-pane="${tab.dataset.pane}"]`)?.classList.add('active');
            });
        });

        // Clear + Apply
        sheet.querySelector('#mobileFilterClear')?.addEventListener('click', () => {
            this.resetShopFilters();
            this.syncMobileFilterSheet();
        });
        sheet.querySelector('#mobileFilterApply')?.addEventListener('click', () => {
            this.toggleMobileFilters(false);
        });

        this.syncMobileFilterSheet();
    },

    // Copy sidebar filter state into the mobile sheet (open + after reset).
    syncMobileFilterSheet() {
        const sheet = document.getElementById('mobileFilterSheet');
        if (!sheet) return;

        const copyRadio = (sheetSel, sideSel) => {
            const checked = document.querySelector(`${sideSel}:checked`);
            const val = checked ? checked.value : null;
            sheet.querySelectorAll(sheetSel).forEach(el => {
                el.checked = (val !== null && el.value === val);
            });
        };
        const copyCheck = (sheetSel, sideSel) => {
            const vals = Array.from(document.querySelectorAll(`${sideSel}:checked`)).map(c => c.value);
            sheet.querySelectorAll(sheetSel).forEach(el => {
                el.checked = vals.includes(el.value);
                if (el.closest('.mfilter-chip')) {
                    el.closest('.mfilter-chip').classList.toggle('on', el.checked);
                }
            });
        };

        copyRadio('.mf-category', '.filter-category-radio');
        copyRadio('.mf-price', '.filter-price-radio');
        copyRadio('.mf-rating', '.filter-rating-radio');
        copyCheck('.mf-size', '.filter-size-check');
        copyCheck('.mf-color', '.filter-color-check');
        this.updateMobileFilterCount();
    },

    updateMobileFilterCount() {
        const label = document.getElementById('mobileFilterCount');
        if (!label) return;
        const countEl = document.getElementById('shopProductCount');
        const m = countEl ? (countEl.textContent || '').match(/(\d+)/) : null;
        if (m) {
            const n = parseInt(m[1], 10);
            label.textContent = `${n} product${n === 1 ? '' : 's'} found`;
            return;
        }
        const grid = document.getElementById('shopProductGrid');
        const n = grid ? grid.children.length : 0;
        label.textContent = `${n} product${n === 1 ? '' : 's'} found`;
    },

    selectMobileChip(chipEl) {
        const cat = chipEl.getAttribute('data-cat') || 'all';
        this.shopFilters.category = cat;
        this.syncMobileChips(cat, cat);
        this.applyShopFilters();
    },

    // Mirrors a selected category slug onto the mobile chip row + the sidebar
    // category radio so both shared views stay visually in sync.
    syncMobileChips(catSlug, radioSlug) {
        document.querySelectorAll('#mobileCatChips .zyra-cat-chip').forEach(c => {
            c.classList.toggle('active', (c.getAttribute('data-cat') || 'all') === (catSlug || 'all'));
        });
        document.querySelectorAll('.filter-category-radio').forEach(r => {
            r.checked = (r.value.toLowerCase() === (radioSlug || 'all').toLowerCase());
        });
    },

    // Shop Page Dynamic AJAX Filtering & Sorting (server-side via /shop/filter)
    initShopPage() {
        const shopGrid = document.getElementById('shopProductGrid');
        if (!shopGrid) return;

        // Preserve the current category when on a category/collection page.
        const pageCategory = shopGrid.getAttribute('data-category-slug');
        if (pageCategory) {
            this.shopFilters.category = pageCategory;
            document.querySelectorAll('.filter-category-radio').forEach(r => {
                r.checked = (r.value.toLowerCase() === pageCategory.toLowerCase());
            });
        }

        // Bind Category Filters
        document.querySelectorAll('.filter-category-radio').forEach(radio => {
            radio.addEventListener('change', (e) => {
                this.shopFilters.category = e.target.value;
                this.applyShopFilters();
            });
        });

        // Bind Price Filters
        document.querySelectorAll('.filter-price-radio').forEach(radio => {
            radio.addEventListener('change', (e) => {
                this.shopFilters.priceRange = e.target.value;
                this.applyShopFilters();
            });
        });

        // Bind Size Checkboxes
        document.querySelectorAll('.filter-size-check').forEach(check => {
            check.addEventListener('change', () => {
                const checked = Array.from(document.querySelectorAll('.filter-size-check:checked')).map(c => c.value);
                this.shopFilters.sizes = checked;
                this.applyShopFilters();
            });
        });

        // Bind Color Checkboxes
        document.querySelectorAll('.filter-color-check').forEach(check => {
            check.addEventListener('change', () => {
                const checked = Array.from(document.querySelectorAll('.filter-color-check:checked')).map(c => c.value);
                this.shopFilters.colors = checked;
                this.applyShopFilters();
            });
        });

        // Bind Rating Filter
        document.querySelectorAll('.filter-rating-radio').forEach(radio => {
            radio.addEventListener('change', (e) => {
                this.shopFilters.rating = parseFloat(e.target.value) || 0;
                this.applyShopFilters();
            });
        });

        // Bind Sorting Select
        const sortSelect = document.getElementById('shopSortSelect');
        if (sortSelect) {
            sortSelect.addEventListener('change', (e) => {
                this.shopFilters.sort = e.target.value;
                this.applyShopFilters();
            });
        }

        // Bind Mobile Sort Sheet Options
        document.querySelectorAll('#mobileSortOptions .zyra-sort-option').forEach(opt => {
            opt.addEventListener('click', () => {
                const sortVal = opt.getAttribute('data-sort');
                this.shopFilters.sort = sortVal;

                if (sortSelect) sortSelect.value = sortVal;
                document.querySelectorAll('#mobileSortOptions .zyra-sort-option').forEach(o => o.classList.remove('active'));
                opt.classList.add('active');

                this.toggleMobileSort(false);
                this.applyShopFilters();
            });
        });

        // Init mobile sort option highlight from current query param
        const urlSort = new URLSearchParams(window.location.search).get('sort');
        if (urlSort && sortSelect) {
            sortSelect.value = urlSort;
            this.shopFilters.sort = urlSort;
            document.querySelectorAll('#mobileSortOptions .zyra-sort-option').forEach(o => {
                o.classList.toggle('active', o.getAttribute('data-sort') === urlSort);
            });
        }

        // Bind Clear All Filters Button
        const clearBtn = document.getElementById('clearAllFiltersBtn');
        if (clearBtn) {
            clearBtn.addEventListener('click', () => {
                this.resetShopFilters();
            });
        }

        // Flipkart-style mobile filter sheet (listing pages)
        this.bindMobileFilterSheet();
    },

    resetShopFilters() {
        const shopGrid = document.getElementById('shopProductGrid');
        const pageCategory = shopGrid ? shopGrid.getAttribute('data-category-slug') : null;

        this.shopFilters = {
            category: pageCategory || 'all',
            subcategories: [],
            priceRange: 'all',
            sizes: [],
            colors: [],
            rating: 0,
            inStockOnly: false,
            sort: 'featured'
        };

        // Reset inputs
        if (pageCategory) {
            document.querySelectorAll('.filter-category-radio').forEach(r => {
                r.checked = (r.value.toLowerCase() === pageCategory.toLowerCase());
            });
        } else {
            document.querySelectorAll('.filter-category-radio[value="all"]').forEach(r => r.checked = true);
        }
        this.syncMobileChips(pageCategory || 'all', pageCategory || 'all');
        document.querySelectorAll('.filter-price-radio[value="all"]').forEach(r => r.checked = true);
        document.querySelectorAll('.filter-rating-radio[value="0"]').forEach(r => r.checked = true);
        document.querySelectorAll('.filter-size-check').forEach(c => c.checked = false);
        document.querySelectorAll('.filter-color-check').forEach(c => c.checked = false);

        const sortSelect = document.getElementById('shopSortSelect');
        if (sortSelect) sortSelect.value = 'featured';
        document.querySelectorAll('#mobileSortOptions .zyra-sort-option').forEach(o => {
            o.classList.toggle('active', o.getAttribute('data-sort') === 'featured');
        });

        this.applyShopFilters();
        this.syncMobileFilterSheet();
    },

    applyShopFilters() {
        const shopGrid = document.getElementById('shopProductGrid');
        const countDisplay = document.getElementById('shopProductCount');
        const countDisplayDesktop = document.getElementById('shopProductCountDesktop');
        const emptyState = document.getElementById('shopEmptyState');
        const activeFiltersBar = document.getElementById('shopActiveFiltersBar');

        if (!shopGrid) return;

        // Show subtle loading effect
        shopGrid.style.opacity = '0.5';

        const params = new URLSearchParams();
        if (this.shopFilters.category && this.shopFilters.category !== 'all') {
            params.append('category', this.shopFilters.category);
        }
        if (this.shopFilters.priceRange && this.shopFilters.priceRange !== 'all') {
            params.append('price_range', this.shopFilters.priceRange);
        }
        this.shopFilters.sizes.forEach(s => params.append('sizes[]', s));
        this.shopFilters.colors.forEach(c => params.append('colors[]', c));
        if (this.shopFilters.rating > 0) {
            params.append('rating', this.shopFilters.rating);
        }
        params.append('sort', this.shopFilters.sort || 'featured');

        // Preserve the page-level filter (new / sale / trending) from the URL so
        // sorting and refining keeps the same curated collection on /shop?filter=...
        const pageFilter = new URLSearchParams(window.location.search).get('filter');
        if (pageFilter) {
            params.append('filter', pageFilter);
        }

        // Keep the dupatta-only constraint when filtering/sorting on /duppata.
        if (shopGrid.getAttribute('data-dupatta') === '1') {
            params.append('dupatta', '1');
        }

        // Render Active Filters Tag Bar immediately
        if (activeFiltersBar) {
            let tagsHtml = '';
            if (this.shopFilters.category !== 'all') {
                tagsHtml += `<span class="active-filter-tag">Category: ${this.shopFilters.category} <button type="button" onclick="ZyraApp.removeFilter('category')">&times;</button></span>`;
            }
            if (this.shopFilters.priceRange !== 'all') {
                tagsHtml += `<span class="active-filter-tag">Price: ${this.shopFilters.priceRange} <button type="button" onclick="ZyraApp.removeFilter('price')">&times;</button></span>`;
            }
            this.shopFilters.sizes.forEach(s => {
                tagsHtml += `<span class="active-filter-tag">Size: ${s} <button type="button" onclick="ZyraApp.removeFilter('size', '${s}')">&times;</button></span>`;
            });
            this.shopFilters.colors.forEach(c => {
                tagsHtml += `<span class="active-filter-tag">Color: ${c} <button type="button" onclick="ZyraApp.removeFilter('color', '${c}')">&times;</button></span>`;
            });
            if (this.shopFilters.rating > 0) {
                tagsHtml += `<span class="active-filter-tag">Rating: ${this.shopFilters.rating}★+ <button type="button" onclick="ZyraApp.removeFilter('rating')">&times;</button></span>`;
            }
            if (tagsHtml) {
                tagsHtml += `<button type="button" class="btn btn-sm btn-link text-danger p-0 ms-2" onclick="ZyraApp.resetShopFilters()">Clear All</button>`;
            }
            activeFiltersBar.innerHTML = tagsHtml;
        }

        fetch(`/shop/filter?${params.toString()}`, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
            .then(res => res.json())
            .then(data => {
                if (!shopGrid) return;
                if (countDisplay) {
                    countDisplay.textContent = `Showing ${data.count} products`;
                }
                if (countDisplayDesktop) {
                    countDisplayDesktop.textContent = `Showing ${data.count} products`;
                }
                if (!data.html || data.count === 0) {
                    shopGrid.innerHTML = '';
                    if (emptyState) emptyState.style.display = 'block';
                } else {
                    if (emptyState) emptyState.style.display = 'none';
                    shopGrid.innerHTML = data.html;
                }
                shopGrid.style.opacity = '1';
                if (window.ZyraWishlist) {
                    window.ZyraWishlist.syncHeartIcons();
                }
            })
            .catch(() => {
                if (shopGrid) shopGrid.style.opacity = '1';
            });
    },

    removeFilter(type, value = null) {
        const shopGrid = document.getElementById('shopProductGrid');
        const pageCategory = shopGrid ? shopGrid.getAttribute('data-category-slug') : null;

        if (type === 'category') {
            this.shopFilters.category = pageCategory || 'all';
            document.querySelectorAll('.filter-category-radio').forEach(r => {
                r.checked = (r.value.toLowerCase() === (pageCategory || 'all').toLowerCase());
            });
            this.syncMobileChips(pageCategory || 'all', pageCategory || 'all');
        } else if (type === 'price') {
            this.shopFilters.priceRange = 'all';
            document.querySelectorAll('.filter-price-radio[value="all"]').forEach(r => r.checked = true);
        } else if (type === 'size' && value) {
            this.shopFilters.sizes = this.shopFilters.sizes.filter(s => s !== value);
            const el = document.querySelector(`.filter-size-check[value="${value}"]`);
            if (el) el.checked = false;
        } else if (type === 'color' && value) {
            this.shopFilters.colors = this.shopFilters.colors.filter(c => c !== value);
            const el = document.querySelector(`.filter-color-check[value="${value}"]`);
            if (el) el.checked = false;
        } else if (type === 'rating') {
            this.shopFilters.rating = 0;
            document.querySelectorAll('.filter-rating-radio[value="0"]').forEach(r => r.checked = true);
        }
        this.applyShopFilters();
    },

    bindNewsletter() {
        document.querySelectorAll('.newsletter-form').forEach(form => {
            form.addEventListener('submit', (e) => {
                e.preventDefault();
                const input = form.querySelector('input[type="email"]');
                if (input && input.value) {
                    this.showToast('Thank you for subscribing to ZYRA VIP Fashion updates!', 'success');
                    input.value = '';
                }
            });
        });
    },

    bindContactForm() {
        const contactForm = document.getElementById('zyraContactForm');
        if (contactForm) {
            contactForm.addEventListener('submit', (e) => {
                e.preventDefault();
                if (!contactForm.checkValidity()) {
                    contactForm.reportValidity();
                    return;
                }
                this.showToast('Thank you! Your message has been received. Our concierge will contact you within 24 hours.', 'success');
                contactForm.reset();
            });
        }
    }
};

document.addEventListener('DOMContentLoaded', () => {
    ZyraApp.init();
});
