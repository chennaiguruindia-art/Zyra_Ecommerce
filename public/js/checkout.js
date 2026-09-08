/**
 * ZYRA Checkout
 * Order review is rendered by Laravel from the server cart.
 * This file only syncs the bag into the session, then handles payment + place order.
 */

const ZyraCheckout = {
    ORDER_KEY: 'zyra_last_order',

    init() {
        if (window.location.pathname.includes('/checkout')) {
            this.initCheckoutPage();
        } else if (window.location.pathname.includes('/order-success')) {
            this.initOrderSuccessPage();
        }
    },

    csrfHeaders() {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
        return {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': csrfToken
        };
    },

    readBrowserCart() {
        try {
            const raw = localStorage.getItem('zyra_cart');
            return raw ? JSON.parse(raw) : [];
        } catch (e) {
            return [];
        }
    },

    initCheckoutPage() {
        const serverCart = Array.isArray(window.ZYRA_SERVER_CART) ? window.ZYRA_SERVER_CART : [];
        const browserCart = this.readBrowserCart();
        const alreadyTried = new URLSearchParams(window.location.search).get('cart_sync') === '1';

        // Copy shopping-bag items into the Laravel session, then reload checkout from the server.
        if (serverCart.length === 0 && browserCart.length > 0 && !alreadyTried) {
            fetch('/cart/sync', {
                method: 'POST',
                headers: this.csrfHeaders(),
                body: JSON.stringify({ items: browserCart })
            })
                .then(() => {
                    const url = new URL(window.location.href);
                    url.searchParams.set('cart_sync', '1');
                    window.location.replace(url.toString());
                })
                .catch(() => {
                    this.fillOrderReview(browserCart);
                    this.bindPaymentSelectors();
                    this.bindPlaceOrder();
                });
            return;
        }

        if (serverCart.length === 0 && browserCart.length > 0) {
            this.fillOrderReview(browserCart);
        }

        this.bindPaymentSelectors();
        this.bindPlaceOrder();
    },

    fillOrderReview(cart) {
        const itemsContainer = document.getElementById('checkoutItemsList');
        const subtotalEl = document.getElementById('checkoutSubtotal');
        const shippingEl = document.getElementById('checkoutShipping');
        const totalEl = document.getElementById('checkoutTotal');
        if (!itemsContainer || !Array.isArray(cart) || cart.length === 0) return;

        let subtotal = 0;
        let html = '';
        cart.forEach(item => {
            const price = parseFloat(item.price) || 0;
            const qty = parseInt(item.quantity, 10) || 1;
            const itemTotal = price * qty;
            subtotal += itemTotal;
            html += `
                <div class="d-flex align-items-center gap-3 py-2 border-bottom">
                    <img src="${item.image || ''}" alt="${item.name || 'Item'}" style="width: 50px; height: 65px; object-fit: cover; border-radius: 4px;">
                    <div class="flex-grow-1 overflow-hidden">
                        <div class="text-truncate fw-semibold small">${item.name || 'Item'}</div>
                        <small class="text-muted d-block" style="font-size: 0.75rem;">Qty: ${qty} | Size: ${item.size || 'M'} | Color: ${item.color || 'Standard'}</small>
                    </div>
                    <div class="fw-bold small text-nowrap">₹${itemTotal}</div>
                </div>
            `;
        });
        itemsContainer.innerHTML = html;

        const shipping = 0;
        const total = subtotal + shipping;
        if (subtotalEl) subtotalEl.textContent = `₹${subtotal}`;
        if (shippingEl) shippingEl.textContent = shipping === 0 ? 'FREE' : `₹${shipping}`;
        if (totalEl) totalEl.textContent = `₹${total}`;
    },

    bindPaymentSelectors() {
        const paymentRadios = document.querySelectorAll('input[name="paymentMethod"]');
        const paymentBoxes = document.querySelectorAll('.payment-method-box');

        paymentRadios.forEach(radio => {
            radio.addEventListener('change', (e) => {
                paymentBoxes.forEach(box => box.classList.remove('border-primary', 'bg-light'));
                const parent = e.target.closest('.payment-method-box');
                if (parent) {
                    parent.classList.add('border-primary', 'bg-light');
                }
            });
        });
    },

    startRazorpay(config) {
        return new Promise((resolve, reject) => {
            if (typeof window.Razorpay === 'undefined') {
                reject(new Error('Razorpay checkout is unavailable. Please refresh and try again.'));
                return;
            }

            const options = {
                key: config.key_id,
                amount: config.amount,
                currency: config.currency || 'INR',
                name: config.name || 'ZYRA Fashion',
                description: config.description || '',
                order_id: config.order_id,
                prefill: config.prefill || {},
                theme: config.theme || { color: '#18181b' },
                handler: (response) => resolve(response),
                modal: {
                    ondismiss: () => reject(new Error('dismissed'))
                }
            };

            const rzp = new window.Razorpay(options);
            rzp.on('payment.failed', (response) => {
                reject(new Error(response?.error?.description || 'Payment failed. Please try again.'));
            });
            rzp.open();
        });
    },

    bindPlaceOrder() {
        const placeOrderBtn = document.getElementById('placeOrderBtn');
        const form = document.getElementById('checkoutForm');

        if (!placeOrderBtn || !form) return;

        placeOrderBtn.addEventListener('click', (e) => {
            e.preventDefault();

            if (!form.checkValidity()) {
                form.reportValidity();
                if (window.ZyraApp) {
                    window.ZyraApp.showToast('Please fill all required shipping details correctly.', 'danger');
                }
                return;
            }

            const firstName = document.getElementById('firstName')?.value.trim();
            const lastName = document.getElementById('lastName')?.value.trim();
            const email = document.getElementById('email')?.value.trim();
            const phone = document.getElementById('phone')?.value.trim();
            const address = document.getElementById('address')?.value.trim();
            const apartment = document.getElementById('apartment')?.value.trim() || '';
            const city = document.getElementById('city')?.value.trim();
            const state = document.getElementById('state')?.value.trim();
            const pincode = document.getElementById('pincode')?.value.trim();
            const paymentMethodEl = document.querySelector('input[name="paymentMethod"]:checked');
            const paymentMethod = paymentMethodEl ? paymentMethodEl.value : 'upi';

            const paymentLabels = {
                'upi': 'UPI (Google Pay / PhonePe / Paytm)',
                'card': 'Credit / Debit Card',
                'netbanking': 'Net Banking'
            };

            const items = this.readBrowserCart();
            let couponCode = '';
            try {
                const couponData = localStorage.getItem('zyra_coupon');
                if (couponData) {
                    couponCode = JSON.parse(couponData).code || '';
                }
            } catch (err) {}

            const orderData = {
                orderId: '',
                orderDate: new Date().toLocaleDateString('en-IN', {
                    day: 'numeric',
                    month: 'long',
                    year: 'numeric'
                }),
                customer: { name: `${firstName} ${lastName}`, email, phone },
                shippingAddress: { address, apartment, city, state, pincode },
                paymentMethod: paymentLabels[paymentMethod] || paymentMethod,
                items,
                total: document.getElementById('checkoutTotal')?.textContent || ''
            };

            placeOrderBtn.disabled = true;
            placeOrderBtn.innerHTML = `<span class="spinner-border spinner-border-sm me-2"></span> Processing Order...`;

            fetch('/checkout/place-order', {
                method: 'POST',
                headers: this.csrfHeaders(),
                body: JSON.stringify({
                    first_name: firstName,
                    last_name: lastName,
                    email,
                    phone,
                    address,
                    apartment,
                    city,
                    state,
                    pincode,
                    payment_method: paymentMethod,
                    items,
                    coupon_code: couponCode,
                    notes: ''
                })
            })
                .then(res => {
                    if (res.status === 401 || res.status === 403) {
                        placeOrderBtn.disabled = false;
                        placeOrderBtn.innerHTML = `Place Order <i class="bi bi-check-lg ms-1"></i>`;
                        if (window.ZyraApp) {
                            window.ZyraApp.requireLogin('Please login to complete your purchase.');
                        }
                        return null;
                    }
                    return res.json();
                })
                .then(data => {
                    if (!data) return;
                    if (!data.success) {
                        placeOrderBtn.disabled = false;
                        placeOrderBtn.innerHTML = `Place Order <i class="bi bi-check-lg ms-1"></i>`;
                        if (window.ZyraApp) {
                            window.ZyraApp.showToast(data.message || 'Error processing order. Please try again.', 'danger');
                        }
                        return;
                    }

                    if (data.requires_payment) {
                        return this.completeOnlinePayment(data, placeOrderBtn, orderData);
                    }

                    orderData.orderId = data.order_number;
                    localStorage.setItem(this.ORDER_KEY, JSON.stringify(orderData));
                    localStorage.removeItem('zyra_cart');
                    window.location.href = data.redirect || ('/order-success/' + data.order_number);
                })
                .catch(() => {
                    placeOrderBtn.disabled = false;
                    placeOrderBtn.innerHTML = `Place Order <i class="bi bi-check-lg ms-1"></i>`;
                    if (window.ZyraApp) {
                        window.ZyraApp.showToast('Could not place the order. Please try again.', 'danger');
                    }
                });
        });
    },

    async completeOnlinePayment(data, placeOrderBtn, orderData) {
        let result;
        try {
            result = await this.startRazorpay(data.razorpay);
        } catch (err) {
            placeOrderBtn.disabled = false;
            placeOrderBtn.innerHTML = `Place Order <i class="bi bi-check-lg ms-1"></i>`;
            if (window.ZyraApp) {
                const msg = err?.message === 'dismissed'
                    ? 'Payment was cancelled. You can try again.'
                    : (err.message || 'Payment could not be completed. Please try again.');
                window.ZyraApp.showToast(msg, 'danger');
            }
            window.location.reload();
            return;
        }

        placeOrderBtn.disabled = true;
        placeOrderBtn.innerHTML = `<span class="spinner-border spinner-border-sm me-2"></span> Verifying Payment...`;

        try {
            const verifyRes = await fetch('/checkout/payment/verify', {
                method: 'POST',
                headers: this.csrfHeaders(),
                body: JSON.stringify(result)
            });

            let data = { success: false };
            try { data = await verifyRes.json(); } catch (err) {}

            if (!verifyRes.ok || !data.success) {
                placeOrderBtn.disabled = false;
                placeOrderBtn.innerHTML = `Place Order <i class="bi bi-check-lg ms-1"></i>`;
                if (window.ZyraApp) {
                    window.ZyraApp.showToast(data.message || 'Payment verification failed. Please contact support.', 'danger');
                }
                if (data.requires_payment) {
                    window.location.reload();
                }
                return;
            }

            orderData.orderId = data.order_number;
            localStorage.setItem(this.ORDER_KEY, JSON.stringify(orderData));
            localStorage.removeItem('zyra_cart');
            window.location.href = data.redirect || ('/order-success/' + data.order_number);
        } catch (err) {
            placeOrderBtn.disabled = false;
            placeOrderBtn.innerHTML = `Place Order <i class="bi bi-check-lg ms-1"></i>`;
            if (window.ZyraApp) {
                window.ZyraApp.showToast('Payment received but could not be verified. Please contact support.', 'danger');
            }
        }
    },

    initOrderSuccessPage() {
        const orderDataRaw = localStorage.getItem(this.ORDER_KEY);
        if (!orderDataRaw) {
            return;
        }

        try {
            const order = JSON.parse(orderDataRaw);
            const orderIdEl = document.getElementById('successOrderId');
            const orderDateEl = document.getElementById('successOrderDate');
            const paymentEl = document.getElementById('successPaymentMethod');
            const addressEl = document.getElementById('successAddress');
            const totalEl = document.getElementById('successTotal');
            const itemsListEl = document.getElementById('successItemsList');

            if (orderIdEl) orderIdEl.textContent = order.orderId;
            if (orderDateEl) orderDateEl.textContent = order.orderDate;
            if (paymentEl) paymentEl.textContent = order.paymentMethod;
            if (totalEl && order.total) totalEl.textContent = order.total;

            if (addressEl && order.shippingAddress) {
                const a = order.shippingAddress;
                addressEl.innerHTML = `
                    <strong>${order.customer.name}</strong><br>
                    ${a.address} ${a.apartment ? ', ' + a.apartment : ''}<br>
                    ${a.city}, ${a.state} - ${a.pincode}<br>
                    Phone: ${order.customer.phone}
                `;
            }

            if (itemsListEl && order.items) {
                let html = '';
                order.items.forEach(item => {
                    html += `
                        <div class="d-flex align-items-center justify-content-between py-2 border-bottom">
                            <div class="d-flex align-items-center gap-3">
                                <img src="${item.image}" alt="${item.name}" style="width: 45px; height: 60px; object-fit: cover; border-radius: 4px;">
                                <div>
                                    <div class="fw-semibold small">${item.name}</div>
                                    <small class="text-muted">Size: ${item.size} | Color: ${item.color} | Qty: ${item.quantity}</small>
                                </div>
                            </div>
                            <span class="fw-bold small">₹${item.price * item.quantity}</span>
                        </div>
                    `;
                });
                itemsListEl.innerHTML = html;
            }
        } catch (e) {
            console.error('Error parsing order data', e);
        }
    }
};

window.ZyraCheckout = ZyraCheckout;

document.addEventListener('DOMContentLoaded', () => {
    ZyraCheckout.init();
});
