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

    removeFromWishlist(productId) {
        const id = parseInt(productId);
        let list = this.getWishlist();
        const index = list.indexOf(id);
        if (index > -1) {
            list.splice(index, 1);
            this.saveWishlist(list);
            if (window.ZyraApp) {
                window.ZyraApp.showToast('Product removed from wishlist', 'info');
            }
        }
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
        if (window.ZyraCart) {
            window.ZyraCart.addToCart(id, size);
            this.removeFromWishlist(id);
        }
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
                if (icon) {
                    icon.classList.remove('bi-heart');
                    icon.classList.add('bi-heart-fill');
                }
            } else {
                btn.classList.remove('active');
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

        let html = '<div class="row g-4">';
        products.forEach(p => {
            html += `
                <div class="col-6 col-md-4 col-lg-3">
                    <div class="zyra-product-card">
                        <div class="zyra-product-thumb">
                            <img src="${p.image}" alt="${p.name}">
                            <div class="zyra-badge-stack">
                                ${p.discount ? `<span class="badge-zyra-discount">${p.discount}% OFF</span>` : ''}
                            </div>
                            <button type="button" class="zyra-wishlist-btn active" data-product-id="${p.id}" onclick="ZyraWishlist.toggleWishlist(${p.id}, this)" title="Remove from Wishlist">
                                <i class="bi bi-heart-fill"></i>
                            </button>
                        </div>
                        <div class="zyra-product-body">
                            <span class="zyra-product-category">${p.category}</span>
                            <h6 class="zyra-product-title">
                                <a href="/product/${p.id}">${p.name}</a>
                            </h6>
                            <div class="zyra-product-price-box mb-3">
                                <span class="zyra-current-price">₹${p.price}</span>
                                ${p.old_price ? `<span class="zyra-old-price">₹${p.old_price}</span>` : ''}
                            </div>
                            <div class="mt-auto d-grid gap-2">
                                <button type="button" class="btn btn-sm btn-zyra-primary" onclick="ZyraWishlist.moveToCart(${p.id})">
                                    <i class="bi bi-bag-plus me-1"></i> Move to Cart
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-danger border-0" onclick="ZyraWishlist.removeFromWishlist(${p.id})">
                                    <i class="bi bi-trash3 me-1"></i> Remove
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        });
        html += '</div>';

        const emptyState = document.getElementById('wishlistEmptyState');
        if (products.length === 0) {
            container.innerHTML = '';
            if (emptyState) emptyState.style.display = 'block';
        } else {
            container.innerHTML = html;
            if (emptyState) emptyState.style.display = 'none';
        }
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
