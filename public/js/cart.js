/**
 * ZYRA - Shopping Cart Management
 * Uses LocalStorage ('zyra_cart', 'zyra_coupon')
 */

const ZyraCart = {
    STORAGE_KEY: 'zyra_cart',
    COUPON_KEY: 'zyra_coupon',

    getCart() {
        try {
            const data = localStorage.getItem(this.STORAGE_KEY);
            return data ? JSON.parse(data) : [];
        } catch (e) {
            console.error('Error reading cart from localStorage', e);
            return [];
        }
    },

    saveCart(cart) {
        localStorage.setItem(this.STORAGE_KEY, JSON.stringify(cart));
        this.updateHeaderBadge();
        this.renderMiniCart();
        if (window.location.pathname.includes('/cart')) {
            this.renderCartPage();
        }
    },

    addToCart(productId, size = 'M', color = null, quantity = 1, productData = null) {
        let product = productData;
        if (!product && window.ZYRA_CURRENT_PRODUCT && parseInt(window.ZYRA_CURRENT_PRODUCT.id) === parseInt(productId)) {
            product = window.ZYRA_CURRENT_PRODUCT;
        }

        if (!product && window.ZyraDB) {
            product = window.ZyraDB.getProductById(productId);
        }

        if (!product && Array.isArray(window.ZYRA_SEARCH_PRODUCTS)) {
            product = window.ZYRA_SEARCH_PRODUCTS.find(p => parseInt(p.id) === parseInt(productId));
        }

        if (!product && typeof ZYRA_PRODUCTS !== 'undefined') {
            product = ZYRA_PRODUCTS.find(p => parseInt(p.id) === parseInt(productId));
        }

        if (!product) {
            window.ZyraApp && window.ZyraApp.showToast('Product not found', 'danger');
            return false;
        }

        const selectedColor = color || (product.colors && product.colors[0]) || 'Standard';
        const selectedSize = size || (product.sizes && product.sizes[0]) || 'M';
        const qty = parseInt(quantity) || 1;

        let cart = this.getCart();
        const existingIndex = cart.findIndex(item => 
            parseInt(item.id) === parseInt(product.id) && item.size === selectedSize && item.color === selectedColor
        );

        if (existingIndex > -1) {
            cart[existingIndex].quantity += qty;
        } else {
            cart.push({
                id: parseInt(product.id),
                name: product.name,
                price: parseFloat(product.price) || 0,
                old_price: product.old_price ? parseFloat(product.old_price) : null,
                image: product.image,
                category: product.category || 'Apparel',
                size: selectedSize,
                color: selectedColor,
                quantity: qty
            });
        }

        this.saveCart(cart);
        this.syncCartToServer();

        // Notify user
        if (window.ZyraApp) {
            window.ZyraApp.showToast(`"${product.name}" added to bag!`, 'success');
        }

        // Trigger offcanvas mini-cart open
        const miniCartEl = document.getElementById('zyraMiniCart');
        if (miniCartEl && window.bootstrap) {
            const bsOffcanvas = bootstrap.Offcanvas.getOrCreateInstance(miniCartEl);
            bsOffcanvas.show();
        }

        return true;
    },

    removeFromCart(index) {
        let cart = this.getCart();
        if (index >= 0 && index < cart.length) {
            const removedItem = cart.splice(index, 1)[0];
            this.saveCart(cart);
            this.syncCartToServer();
            if (window.ZyraApp) {
                window.ZyraApp.showToast(`Removed "${removedItem.name}" from cart`, 'info');
            }
        }
    },

    updateQuantity(index, newQty) {
        let cart = this.getCart();
        const qty = parseInt(newQty);
        if (index >= 0 && index < cart.length) {
            if (qty <= 0) {
                this.removeFromCart(index);
            } else {
                cart[index].quantity = qty;
                this.saveCart(cart);
                this.syncCartToServer();
            }
        }
    },

    clearCart() {
        localStorage.removeItem(this.STORAGE_KEY);
        this.saveCart([]);
    },

    csrfHeaders() {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
        return {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': csrfToken
        };
    },

    syncCartToServer() {
        const items = this.getCart();
        if (!items.length) {
            return Promise.resolve(null);
        }

        return fetch('/cart/sync', {
            method: 'POST',
            headers: this.csrfHeaders(),
            body: JSON.stringify({ items })
        })
            .then(res => res.json())
            .then(data => {
                if (data && Array.isArray(data.cart) && data.cart.length > 0) {
                    localStorage.setItem(this.STORAGE_KEY, JSON.stringify(data.cart));
                    this.updateHeaderBadge();
                    this.renderMiniCart();
                    if (window.location.pathname.includes('/cart')) {
                        this.renderCartPage();
                    }
                }
                return data;
            })
            .catch(() => null);
    },

    goToCheckout(href) {
        const target = href || '/checkout';
        if (window.ZyraApp && !window.ZyraApp.isLoggedIn()) {
            window.ZyraApp.requireLogin('Please login to proceed to checkout.');
            return;
        }

        this.syncCartToServer().finally(() => {
            window.location.href = target;
        });
    },

    bindCheckoutLinks() {
        document.querySelectorAll('a[href*="/checkout"]').forEach(link => {
            if (link.dataset.checkoutBound === '1') return;
            link.dataset.checkoutBound = '1';
            link.addEventListener('click', (e) => {
                e.preventDefault();
                this.goToCheckout(link.getAttribute('href'));
            });
        });
    },

    calculateSubtotal() {
        const cart = this.getCart();
        return cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
    },

    getAppliedCoupon() {
        try {
            const coupon = localStorage.getItem(this.COUPON_KEY);
            return coupon ? JSON.parse(coupon) : null;
        } catch (e) {
            return null;
        }
    },

    isCouponEntry() {
        return window.ZyraApp ? window.ZyraApp.requireLogin('Please login to apply coupon codes.') : true;
    },

    applyCoupon(code) {
        const cleanCode = code ? code.trim().toUpperCase() : '';
        if (!cleanCode) return false;
        if (!this.isCouponEntry()) return false;

        const subtotal = this.calculateSubtotal();
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

        fetch('/cart/apply-coupon', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify({ code: cleanCode, subtotal: subtotal })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success && data.coupon) {
                localStorage.setItem(this.COUPON_KEY, JSON.stringify(data.coupon));
                if (window.ZyraApp) {
                    window.ZyraApp.showToast(data.message || `Coupon "${cleanCode}" applied!`, 'success');
                }
                if (window.location.pathname.includes('/cart')) {
                    this.renderCartPage();
                }
            } else {
                if (window.ZyraApp) {
                    window.ZyraApp.showToast(data.message || 'Invalid coupon code.', 'danger');
                }
            }
        })
        .catch(() => {
            const fallbackCoupons = {
                'WELCOME10': { discount: 10, type: 'percent', label: '10% Welcome Discount' },
                'SAVE20': { discount: 20, type: 'percent', label: '20% Mega Fashion Savings' },
                'FASHION15': { discount: 15, type: 'percent', label: '15% Seasonal Discount' }
            };
            if (fallbackCoupons[cleanCode]) {
                localStorage.setItem(this.COUPON_KEY, JSON.stringify({
                    code: cleanCode,
                    ...fallbackCoupons[cleanCode]
                }));
                if (window.ZyraApp) window.ZyraApp.showToast(`Coupon "${cleanCode}" applied!`, 'success');
                if (window.location.pathname.includes('/cart')) this.renderCartPage();
            } else {
                if (window.ZyraApp) window.ZyraApp.showToast('Invalid coupon code.', 'danger');
            }
        });
        return true;
    },

    removeCoupon() {
        localStorage.removeItem(this.COUPON_KEY);
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
        fetch('/cart/remove-coupon', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken }
        }).catch(() => {});
        if (window.ZyraApp) {
            window.ZyraApp.showToast('Coupon removed', 'info');
        }
        if (window.location.pathname.includes('/cart')) {
            this.renderCartPage();
        }
    },

    calculateDiscount() {
        const subtotal = this.calculateSubtotal();
        const coupon = this.getAppliedCoupon();
        if (!coupon || subtotal === 0) return 0;
        return Math.round((subtotal * coupon.discount) / 100);
    },

    calculateShipping() {
        return 0;
    },

    calculateTotal() {
        const subtotal = this.calculateSubtotal();
        if (subtotal === 0) return 0;
        const discount = this.calculateDiscount();
        const shipping = this.calculateShipping();
        return Math.max(0, subtotal - discount + shipping);
    },

    updateHeaderBadge() {
        const cart = this.getCart();
        const totalCount = cart.reduce((total, item) => total + item.quantity, 0);
        document.querySelectorAll('.cart-count-badge').forEach(badge => {
            badge.textContent = totalCount;
            badge.style.display = totalCount > 0 ? 'flex' : 'none';
        });
    },

    renderMiniCart() {
        const container = document.getElementById('miniCartItemsContainer');
        const subtotalEl = document.getElementById('miniCartSubtotal');
        const checkoutBtn = document.getElementById('miniCartCheckoutBtn');
        const viewCartBtn = document.getElementById('miniCartViewCartBtn');

        if (!container) return;

        const cart = this.getCart();
        if (cart.length === 0) {
            container.innerHTML = `
                <div class="text-center py-5">
                    <div class="mb-3 text-muted" style="font-size: 3rem;">
                        <i class="bi bi-bag-x"></i>
                    </div>
                    <h6 class="fw-bold mb-2">Your Bag is Empty</h6>
                    <p class="text-muted small mb-4">Discover effortlessly chic styles to fill it up.</p>
                    <a href="/shop" class="btn btn-sm btn-zyra-primary">Explore Shop</a>
                </div>
            `;
            if (subtotalEl) subtotalEl.textContent = '₹0';
            if (checkoutBtn) checkoutBtn.classList.add('disabled');
            return;
        }

        if (checkoutBtn) checkoutBtn.classList.remove('disabled');

        let html = '';
        cart.forEach((item, index) => {
            html += `
                <div class="zyra-mini-cart-item">
                    <img src="${item.image}" alt="${item.name}" class="mini-cart-thumb">
                    <div class="mini-cart-info">
                        <h6 class="mini-cart-title">${item.name}</h6>
                        <div class="mini-cart-meta">Size: <strong>${item.size}</strong> | Color: <strong>${item.color}</strong></div>
                        <div class="d-flex justify-content-between align-items-center mt-2">
                            <span class="small text-muted">Qty: ${item.quantity}</span>
                            <span class="mini-cart-price">₹${item.price * item.quantity}</span>
                        </div>
                    </div>
                    <button class="mini-cart-remove" onclick="ZyraCart.removeFromCart(${index})" title="Remove item">
                        <i class="bi bi-trash3"></i>
                    </button>
                </div>
            `;
        });

        container.innerHTML = html;
        if (subtotalEl) {
            subtotalEl.textContent = `₹${this.calculateSubtotal()}`;
        }
    },

    renderCartPage() {
        const tableBody = document.getElementById('cartTableBody');
        const emptyState = document.getElementById('cartEmptyState');
        const contentSection = document.getElementById('cartContentSection');

        if (!tableBody && !emptyState) return;

        const cart = this.getCart();
        if (cart.length === 0) {
            if (contentSection) contentSection.style.display = 'none';
            if (emptyState) emptyState.style.display = 'block';
            return;
        }

        if (contentSection) contentSection.style.display = 'block';
        if (emptyState) emptyState.style.display = 'none';

        let rowsHtml = '';
        cart.forEach((item, index) => {
            rowsHtml += `
                <tr>
                    <td data-label="Product">
                        <div class="d-flex align-items-center gap-3">
                            <img src="${item.image}" alt="${item.name}" class="cart-item-thumb">
                            <div>
                                <a href="/product/${item.id}" class="fw-bold text-dark text-decoration-none d-block">${item.name}</a>
                                <small class="text-muted d-block mt-1">Size: <span class="badge bg-light text-dark border">${item.size}</span> | Color: ${item.color}</small>
                                <small class="text-muted">SKU: ZYR-ITEM-${item.id}</small>
                            </div>
                        </div>
                    </td>
                    <td data-label="Price" class="text-nowrap">
                        <span class="fw-semibold">₹${item.price}</span>
                        ${item.old_price ? `<br><small class="text-muted text-decoration-line-through">₹${item.old_price}</small>` : ''}
                    </td>
                    <td data-label="Quantity">
                        <div class="zyra-qty-stepper">
                            <button type="button" class="zyra-qty-btn" onclick="ZyraCart.updateQuantity(${index}, ${item.quantity - 1})">-</button>
                            <input type="text" class="zyra-qty-input" value="${item.quantity}" readonly>
                            <button type="button" class="zyra-qty-btn" onclick="ZyraCart.updateQuantity(${index}, ${item.quantity + 1})">+</button>
                        </div>
                    </td>
                    <td data-label="Subtotal" class="fw-bold text-nowrap">
                        ₹${item.price * item.quantity}
                    </td>
                    <td data-label="Action" class="text-center">
                        <button class="btn btn-sm btn-link text-danger p-0" onclick="ZyraCart.removeFromCart(${index})" title="Remove item">
                            <i class="bi bi-trash3 fs-5"></i>
                        </button>
                    </td>
                </tr>
            `;
        });

        if (tableBody) tableBody.innerHTML = rowsHtml;

        // Update Summary Elements
        const subtotal = this.calculateSubtotal();
        const discount = this.calculateDiscount();
        const shipping = this.calculateShipping();
        const total = this.calculateTotal();
        const coupon = this.getAppliedCoupon();

        const elSubtotal = document.getElementById('cartSummarySubtotal');
        const elDiscount = document.getElementById('cartSummaryDiscount');
        const elDiscountRow = document.getElementById('cartDiscountRow');
        const elShipping = document.getElementById('cartSummaryShipping');
        const elTotal = document.getElementById('cartSummaryTotal');
        const appliedCouponWrap = document.getElementById('appliedCouponWrap');

        if (elSubtotal) elSubtotal.textContent = `₹${subtotal}`;
        if (elShipping) elShipping.textContent = shipping === 0 ? 'FREE' : `₹${shipping}`;
        if (elTotal) elTotal.textContent = `₹${total}`;

        if (coupon && discount > 0) {
            if (elDiscountRow) elDiscountRow.style.display = 'flex';
            if (elDiscount) elDiscount.textContent = `-₹${discount}`;
            if (appliedCouponWrap) {
                appliedCouponWrap.innerHTML = `
                    <div class="alert alert-success d-flex justify-content-between align-items-center py-2 px-3 small mt-2 mb-0">
                        <span><i class="bi bi-tag-fill me-1"></i> <strong>${coupon.code}</strong> (${coupon.discount}% OFF)</span>
                        <button type="button" class="btn-close btn-close-sm" onclick="ZyraCart.removeCoupon()"></button>
                    </div>
                `;
            }
        } else {
            if (elDiscountRow) elDiscountRow.style.display = 'none';
            if (appliedCouponWrap) appliedCouponWrap.innerHTML = '';
        }
    },

    saveCartForReminder(email) {
        const feedback = document.getElementById('saveCartFeedback');
        const input = document.getElementById('saveCartEmailInput');
        const btn = document.getElementById('saveCartBtn');

        const setFeedback = (html, type) => {
            if (!feedback) return;
            feedback.innerHTML = `<span class="${type === 'success' ? 'text-success' : 'text-danger'}"><i class="bi ${type === 'success' ? 'bi-check-circle' : 'bi-exclamation-circle'} me-1"></i>${html}</span>`;
        };

        const cleanEmail = (email || '').trim();
        if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(cleanEmail)) {
            setFeedback('Please enter a valid email address.', 'error');
            if (input) input.focus();
            return false;
        }

        const cart = this.getCart();
        if (!cart.length) {
            setFeedback('Your bag is empty. Add items before saving.', 'error');
            return false;
        }

        if (btn) {
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Saving...';
        }

        fetch('/cart/save', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
            },
            body: JSON.stringify({ email: cleanEmail, items: cart }),
        })
            .then(res => res.json())
            .then(data => {
                if (data && data.success) {
                    setFeedback(data.message || 'Your bag is saved!', 'success');
                    if (input) input.disabled = true;
                } else {
                    setFeedback(data.message || 'Could not save your bag. Please try again.', 'error');
                }
            })
            .catch(() => setFeedback('Network error. Please try again.', 'error'))
            .finally(() => {
                if (btn) {
                    btn.disabled = false;
                    btn.innerHTML = 'Save My Bag';
                }
            });

        return true;
    },

    init() {
        this.updateHeaderBadge();
        this.renderMiniCart();
        if (window.location.pathname.includes('/cart')) {
            this.renderCartPage();
        }
        this.bindCheckoutLinks();
        this.syncCartToServer();
    }
};

window.ZyraCart = ZyraCart;

document.addEventListener('DOMContentLoaded', () => {
    ZyraCart.init();
});
