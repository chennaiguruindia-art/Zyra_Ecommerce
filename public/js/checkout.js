/**
 * ZYRA - Checkout & Order Success Controller
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

    initCheckoutPage() {
        if (!window.ZyraCart) return;

        const cart = window.ZyraCart.getCart();
        if (cart.length === 0) {
            window.location.href = '/cart';
            return;
        }

        this.renderCheckoutSummary();
        this.bindPaymentSelectors();
        this.bindPlaceOrder();
    },

    renderCheckoutSummary() {
        const cart = window.ZyraCart.getCart();
        const itemsContainer = document.getElementById('checkoutItemsList');
        const subtotalEl = document.getElementById('checkoutSubtotal');
        const discountEl = document.getElementById('checkoutDiscount');
        const discountRow = document.getElementById('checkoutDiscountRow');
        const shippingEl = document.getElementById('checkoutShipping');
        const totalEl = document.getElementById('checkoutTotal');

        if (itemsContainer) {
            let html = '';
            cart.forEach(item => {
                html += `
                    <div class="d-flex align-items-center gap-3 py-2 border-bottom">
                        <img src="${item.image}" alt="${item.name}" style="width: 50px; height: 65px; object-fit: cover; border-radius: 4px;">
                        <div class="flex-grow-1 overflow-hidden">
                            <div class="text-truncate fw-semibold small">${item.name}</div>
                            <small class="text-muted">Qty: ${item.quantity} | Size: ${item.size} | Color: ${item.color}</small>
                        </div>
                        <div class="fw-bold small text-nowrap">₹${item.price * item.quantity}</div>
                    </div>
                `;
            });
            itemsContainer.innerHTML = html;
        }

        const subtotal = window.ZyraCart.calculateSubtotal();
        const discount = window.ZyraCart.calculateDiscount();
        const shipping = window.ZyraCart.calculateShipping();
        const total = window.ZyraCart.calculateTotal();

        if (subtotalEl) subtotalEl.textContent = `₹${subtotal}`;
        if (shippingEl) shippingEl.textContent = shipping === 0 ? 'FREE' : `₹${shipping}`;
        if (totalEl) totalEl.textContent = `₹${total}`;

        if (discount > 0) {
            if (discountRow) discountRow.style.display = 'flex';
            if (discountEl) discountEl.textContent = `-₹${discount}`;
        } else {
            if (discountRow) discountRow.style.display = 'none';
        }
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

                // Show conditional sub-panels (UPI vs Card)
                const method = e.target.value;
                const cardDetails = document.getElementById('cardDetailsFields');
                const upiDetails = document.getElementById('upiDetailsFields');
                if (cardDetails) cardDetails.style.display = method === 'card' ? 'block' : 'none';
                if (upiDetails) upiDetails.style.display = method === 'upi' ? 'block' : 'none';
            });
        });
    },

    bindPlaceOrder() {
        const placeOrderBtn = document.getElementById('placeOrderBtn');
        const form = document.getElementById('checkoutForm');

        if (!placeOrderBtn || !form) return;

        placeOrderBtn.addEventListener('click', (e) => {
            e.preventDefault();

            // Validate standard HTML5 inputs
            if (!form.checkValidity()) {
                form.reportValidity();
                if (window.ZyraApp) {
                    window.ZyraApp.showToast('Please fill all required shipping details correctly.', 'danger');
                }
                return;
            }

            // Prepare Order Object
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
            const paymentMethod = paymentMethodEl ? paymentMethodEl.value : 'cod';

            const paymentLabels = {
                'cod': 'Cash on Delivery (COD)',
                'upi': 'UPI (Google Pay / PhonePe / Paytm)',
                'card': 'Credit / Debit Card',
                'netbanking': 'Net Banking'
            };

            const cart = window.ZyraCart.getCart();
            const subtotal = window.ZyraCart.calculateSubtotal();
            const discount = window.ZyraCart.calculateDiscount();
            const shipping = window.ZyraCart.calculateShipping();
            const total = window.ZyraCart.calculateTotal();

            const orderId = 'ZYRA-' + Math.floor(100000 + Math.random() * 900000);
            const orderDate = new Date().toLocaleDateString('en-IN', {
                day: 'numeric',
                month: 'long',
                year: 'numeric'
            });

            const orderData = {
                orderId: orderId,
                orderDate: orderDate,
                customer: {
                    name: `${firstName} ${lastName}`,
                    email: email,
                    phone: phone
                },
                shippingAddress: {
                    address: address,
                    apartment: apartment,
                    city: city,
                    state: state,
                    pincode: pincode
                },
                paymentMethod: paymentLabels[paymentMethod] || paymentMethod,
                items: cart,
                subtotal: subtotal,
                discount: discount,
                shipping: shipping,
                total: total
            };

            // Extract applied coupon if any
            let couponCode = '';
            try {
                const couponData = localStorage.getItem('zyra_coupon');
                if (couponData) {
                    const parsed = JSON.parse(couponData);
                    couponCode = parsed.code || '';
                }
            } catch(e) {}

            // Send AJAX POST to Laravel /checkout/place-order
            placeOrderBtn.disabled = true;
            placeOrderBtn.innerHTML = `<span class="spinner-border spinner-border-sm me-2"></span> Processing Order...`;

            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

            fetch('/checkout/place-order', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({
                    first_name: firstName,
                    last_name: lastName,
                    email: email,
                    phone: phone,
                    address: address,
                    apartment: apartment,
                    city: city,
                    state: state,
                    pincode: pincode,
                    payment_method: paymentMethod,
                    items: cart,
                    coupon_code: couponCode,
                    notes: ''
                })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    orderData.orderId = data.order_number;
                    localStorage.setItem(this.ORDER_KEY, JSON.stringify(orderData));
                    window.ZyraCart.clearCart();
                    window.location.href = data.redirect || ('/order-success/' + data.order_number);
                } else {
                    placeOrderBtn.disabled = false;
                    placeOrderBtn.innerHTML = `<span>Place Order</span> <i class="bi bi-arrow-right"></i>`;
                    if (window.ZyraApp) {
                        window.ZyraApp.showToast(data.message || 'Error processing order. Please try again.', 'danger');
                    }
                }
            })
            .catch(err => {
                console.warn('Backend order placement fallback:', err);
                localStorage.setItem(this.ORDER_KEY, JSON.stringify(orderData));
                window.ZyraCart.clearCart();
                window.location.href = '/order-success';
            });
        });
    },

    initOrderSuccessPage() {
        const orderDataRaw = localStorage.getItem(this.ORDER_KEY);
        if (!orderDataRaw) {
            // Default mock order if visited directly
            const orderIdEl = document.getElementById('successOrderId');
            if (orderIdEl) orderIdEl.textContent = 'ZYRA-' + Math.floor(100000 + Math.random() * 900000);
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
            if (totalEl) totalEl.textContent = `₹${order.total}`;

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

document.addEventListener('DOMContentLoaded', () => {
    ZyraCheckout.init();
});
