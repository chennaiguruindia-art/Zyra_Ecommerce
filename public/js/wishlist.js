/**
 * ZYRA - Wishlist Management
 * Uses LocalStorage ('zyra_wishlist')
 */

const ZyraWishlist = {
    STORAGE_KEY: 'zyra_wishlist',

    getWishlist() {
        try {
            const data = localStorage.getItem(this.STORAGE_KEY);
            return data ? JSON.parse(data) : [];
        } catch (e) {
            console.error('Error reading wishlist from localStorage', e);
            return [];
        }
    },

    saveWishlist(list) {
        localStorage.setItem(this.STORAGE_KEY, JSON.stringify(list));
        this.updateHeaderBadge();
        this.syncHeartIcons();
        if (window.location.pathname.includes('/wishlist')) {
            this.renderWishlistPage();
        }
    },

    isInWishlist(productId) {
        const list = this.getWishlist();
        return list.includes(parseInt(productId));
    },

    toggleWishlist(productId, buttonEl = null) {
        const id = parseInt(productId);
        let list = this.getWishlist();

        // Only adding requires login; removing a saved item is always allowed.
        if (list.indexOf(id) === -1 && window.ZyraApp && !window.ZyraApp.requireLogin('Please login to save items to your wishlist.')) {
            return false;
        }

        const product = window.ZyraDB ? window.ZyraDB.getProductById(id) : null;
        const productName = product ? product.name : 'Product';

        const index = list.indexOf(id);
        if (index > -1) {
            list.splice(index, 1);
            this.saveWishlist(list);
            if (window.ZyraApp) {
                window.ZyraApp.showToast(`Removed "${productName}" from wishlist`, 'info');
            }
        } else {
            list.push(id);
            this.saveWishlist(list);
            if (window.ZyraApp) {
                window.ZyraApp.showToast(`Added "${productName}" to wishlist!`, 'success');
            }
        }

        return this.isInWishlist(id);
    },

    removeFromWishlist(productId, silent = false) {
        const id = parseInt(productId);
        let list = this.getWishlist();
        const index = list.indexOf(id);
        if (index > -1) {
            list.splice(index, 1);
            this.saveWishlist(list);
            if (!silent && window.ZyraApp) {
                window.ZyraApp.showToast('Product removed from wishlist', 'info');
            }
        }
    },

    moveAllToBag() {
        const items = this._lastRendered || [];
        if (!items.length || !window.ZyraCart) return;
        const movedIds = [];
        items.forEach((p) => {
            if (window.ZyraCart.addToCart(p.id, 'M', null, 1, p)) movedIds.push(parseInt(p.id));
        });
        if (movedIds.length > 0) {
            this.saveWishlist(this.getWishlist().filter((id) => !movedIds.includes(parseInt(id))));
            if (window.ZyraApp) window.ZyraApp.showToast(`${movedIds.length} item${movedIds.length === 1 ? '' : 's'} moved to bag!`, 'success');
        }
    },

    esc(s) {
        return String(s ?? '').replace(/[&<>"']/g, (m) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[m]));
    },

    clearWishlist() {
        localStorage.removeItem(this.STORAGE_KEY);
        this.saveWishlist([]);
        if (window.ZyraApp) {
            window.ZyraApp.showToast('Wishlist cleared', 'info');
        }
    },

    moveToCart(productId, size = 'M') {
        const id = parseInt(productId);
        if (!window.ZyraCart || !(id > 0)) return;

        const local = window.ZyraDB ? window.ZyraDB.getProductById(id) : null;
        if (local) {
            if (window.ZyraCart.addToCart(id, size, null, 1, local)) {
                this.removeFromWishlist(id);
            }
            return;
        }

        // Wishlist page has no local catalog — fetch the product, then move.
        fetch(`/wishlist/items?ids=${id}`, {
            headers: { 'Accept': 'application/json' }
        })
            .then((res) => res.json())
            .then((data) => {
                const p = data && Array.isArray(data.products) ? data.products[0] : null;
                if (!p) {
                    if (window.ZyraApp) window.ZyraApp.showToast('Product not found', 'danger');
                    return;
                }
                if (window.ZyraCart.addToCart(id, size, null, 1, p)) {
                    this.removeFromWishlist(id);
                }
            })
            .catch(() => {
                if (window.ZyraApp) window.ZyraApp.showToast('Could not move item to bag. Please try again.', 'danger');
            });
    },

    updateHeaderBadge() {
        const count = this.getWishlist().length;
        document.querySelectorAll('.wishlist-count-badge').forEach(badge => {
            badge.textContent = count;
            badge.style.display = count > 0 ? 'flex' : 'none';
        });
    },

    syncHeartIcons() {
        const list = this.getWishlist();
        document.querySelectorAll('.zyra-wishlist-btn').forEach(btn => {
            const id = parseInt(btn.getAttribute('data-product-id'));
            const icon = btn.querySelector('i');
            if (list.includes(id)) {
                btn.classList.add('active');
                btn.title = 'Loved';
                btn.setAttribute('aria-label', 'Loved');
                if (icon) {
                    icon.classList.remove('bi-heart');
                    icon.classList.add('bi-heart-fill');
                }
            } else {
                btn.classList.remove('active');
                btn.title = 'Love it';
                btn.setAttribute('aria-label', 'Love it');
                if (icon) {
                    icon.classList.remove('bi-heart-fill');
                    icon.classList.add('bi-heart');
                }
            }
        });
    },

    renderWishlistPage() {
        const container = document.getElementById('wishlistGridContainer');
        const emptyState = document.getElementById('wishlistEmptyState');
        const countHeader = document.getElementById('wishlistItemsCount');

        if (!container) return;

        const list = this.getWishlist();
        if (countHeader) {
            countHeader.textContent = `${list.length} ${list.length === 1 ? 'Item' : 'Items'}`;
        }

        if (list.length === 0) {
            this.showEmptyWishlist();
            return;
        }

        if (emptyState) emptyState.style.display = 'none';

        // Best-effort render from the local catalog right away.
        this.renderWishlistCards(this.resolveFromCatalog(list));

        // Reconcile with the server so DB-only items render correctly.
        this.reconcileFromServer(list);
    },

    showEmptyWishlist() {
        const container = document.getElementById('wishlistGridContainer');
        const emptyState = document.getElementById('wishlistEmptyState');
        if (container) container.innerHTML = '';
        if (emptyState) emptyState.style.display = 'block';
    },

    resolveFromCatalog(list) {
        return list.map(id => {
            const product = window.ZyraDB ? window.ZyraDB.getProductById(id) : null;
            return product ? Object.assign({}, product, { id: parseInt(id) }) : null;
        }).filter(Boolean);
    },

    reconcileFromServer(list) {
        if (typeof fetch !== 'function' || list.length === 0) return;
        fetch(`/wishlist/items?ids=${list.join(',')}`, {
            headers: { 'Accept': 'application/json' }
        })
            .then(res => res.json())
            .then(data => {
                if (!data || !Array.isArray(data.products)) return;
                const server = new Map(data.products.map(p => [parseInt(p.id), p]));
                const resolved = list.map(id => {
                    if (server.has(parseInt(id))) return server.get(parseInt(id));
                    return window.ZyraDB ? window.ZyraDB.getProductById(id) : null;
                }).filter(Boolean);
                this.renderWishlistCards(resolved);
            })
            .catch(() => {});
    },

    renderWishlistCards(products) {
        const container = document.getElementById('wishlistGridContainer');
        if (!container) return;

        this._lastRendered = Array.isArray(products) ? products : [];
        this.renderWishlistSummary(this._lastRendered);

        if (products.length === 0) {
            container.innerHTML = '';
            const emptyState = document.getElementById('wishlistEmptyState');
            if (emptyState) emptyState.style.display = 'block';
            return;
        }

        const emptyState = document.getElementById('wishlistEmptyState');
        if (emptyState) emptyState.style.display = 'none';

        let html = '<div class="wl-grid">';
        products.forEach(p => {
            const price = parseFloat(p.price) || 0;
            const oldPrice = parseFloat(p.old_price) || 0;
            const discount = parseFloat(p.discount) || 0;
            const rating = Math.round(parseFloat(p.rating) || 0);
            const reviews = p.reviews ?? p.reviews_count ?? 0;
            const outOfStock = p.in_stock === false || parseInt(p.stock_units ?? 1) <= 0;
            const lowStock = !outOfStock && p.stock_units !== undefined && parseInt(p.stock_units) <= 5;
            html += `
                <div class="zyra-product-card">
                    <div class="zyra-product-thumb">
                        <a href="/product/${p.id}" class="d-block w-100 h-100"><img src="${p.image}" alt="${this.esc(p.name)}" loading="lazy"></a>
                        <div class="zyra-badge-stack">
                            ${p.badge ? `<span class="badge-zyra-${String(p.badge).toLowerCase().replace(/\s+/g, '')}">${this.esc(p.badge)}</span>` : ''}
                            ${discount > 0 ? `<span class="badge-zyra-discount">${discount}% OFF</span>` : ''}
                        </div>
                        <button type="button" class="zyra-wishlist-btn active" data-product-id="${p.id}" onclick="ZyraWishlist.toggleWishlist(${p.id}, this)" title="Loved" aria-label="Loved">
                            <i class="bi bi-heart-fill"></i>
                        </button>
                    </div>
                    <div class="zyra-product-body">
                        <span class="zyra-product-category">${this.esc(p.category || 'Apparel')}</span>
                        <h6 class="zyra-product-title"><a href="/product/${p.id}" title="${this.esc(p.name)}">${this.esc(p.name)}</a></h6>
                        ${rating > 0 ? `<div class="zyra-product-rating"><span>${'★'.repeat(rating)}${'☆'.repeat(5 - rating)}</span><span class="review-count">(${reviews})</span></div>` : ''}
                        <div class="zyra-product-price-box">
                            <span class="zyra-current-price">₹${price.toLocaleString('en-IN')}</span>
                            ${oldPrice > price ? `<span class="zyra-old-price">₹${oldPrice.toLocaleString('en-IN')}</span><span class="zyra-discount-tag">${discount > 0 ? discount + '% OFF' : 'Sale'}</span>` : ''}
                        </div>
                        ${outOfStock
                            ? '<div class="wl-stock text-danger">Out of stock</div>'
                            : (lowStock ? `<div class="wl-stock text-warning">Only ${p.stock_units} left!</div>` : '')}
                        <div class="wl-card-btns">
                            <button type="button" class="wl-move" ${outOfStock ? 'disabled' : ''} onclick="ZyraWishlist.moveToCart(${p.id})">
                                <i class="bi bi-bag-plus me-1"></i> Move to Bag
                            </button>
                            <button type="button" class="wl-remove" onclick="ZyraWishlist.removeFromWishlist(${p.id})">
                                <i class="bi bi-trash3 me-1"></i>Remove
                            </button>
                        </div>
                    </div>
                </div>
            `;
        });
        html += '</div>';

        container.innerHTML = html;
    },

    renderWishlistSummary(products) {
        const bar = document.getElementById('wishlistToolbar');
        if (bar) bar.style.display = (products && products.length) ? '' : 'none';
    },

    init() {
        this.updateHeaderBadge();
        this.syncHeartIcons();
        if (window.location.pathname.includes('/wishlist')) {
            this.renderWishlistPage();
        }
    }
};

document.addEventListener('DOMContentLoaded', () => {
    ZyraWishlist.init();
});

window.addEventListener('zyra:catalog-loaded', () => {
    if (!window.ZyraWishlist) return;
    window.ZyraWishlist.updateHeaderBadge();
    window.ZyraWishlist.syncHeartIcons();
    if (window.location.pathname.includes('/wishlist')) {
        window.ZyraWishlist.renderWishlistPage();
    }
});
