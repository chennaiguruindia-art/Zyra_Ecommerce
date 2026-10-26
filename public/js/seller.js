/**
 * ZYRA - Seller Center Management Controller
 * Handles seller products, multi-image upload (max 4 images), dynamic colors with '+' icon,
 * inventory, orders, analytics, and settings.
 * Persists seller-created data via LocalStorage.
 */

window.ZyraSeller = {
    page: null,
    PRODUCTS_KEY: 'zyra_seller_products_v2',
    ORDERS_KEY: 'zyra_seller_orders_v2',

    // Multi-Image state (Max 4)
    currentImages: [],
    currentImageFiles: [],
    MAX_IMAGES: 4,

    // Dynamic Colors state
    availableColors: [
        { name: 'Pink', hex: '#f4a7b9' },
        { name: 'White', hex: '#ffffff' },
        { name: 'Black', hex: '#1a1a1a' },
        { name: 'Blue', hex: '#3498db' },
        { name: 'Green', hex: '#27ae60' },
        { name: 'Red', hex: '#c0392b' },
        { name: 'Yellow', hex: '#f1c40f' },
        { name: 'Beige', hex: '#f5f0e6' },
        { name: 'Gold', hex: '#d4af37' }
    ],
    selectedColors: ['Pink', 'White'],
    currentOrderStage: 'pending',

    init() {
        this.seedInitialData();
        this.syncWithBackend();
        this.detectPage();
    },

    syncWithBackend() {
        if (typeof fetch !== 'function') return;

        fetch('/seller/api/products')
            .then(res => res.json())
            .then(prods => {
                if (Array.isArray(prods)) {
                    this.saveProducts(prods);
                    const path = window.location.pathname;
                    if (path.includes('/seller/products') || path.includes('/seller/inventory') || path.endsWith('/seller') || path.includes('/seller/dashboard')) {
                        this.detectPage();
                    }
                }
            })
            .catch(() => {});

        fetch('/seller/api/orders')
            .then(res => res.json())
            .then(ords => {
                if (Array.isArray(ords)) {
                    localStorage.setItem(this.ORDERS_KEY, JSON.stringify(ords));
                    this.syncSidebarBadges();
                    const path = window.location.pathname;
                    if (path.includes('/seller/sales') || path.includes('/seller/analytics') || path.endsWith('/seller') || path.includes('/seller/dashboard') || path.includes('/seller/neworder')) {
                        this.detectPage();
                    }
                }
            })
            .catch(() => {});
    },

    seedInitialData() {
        if (!localStorage.getItem(this.PRODUCTS_KEY)) {
            localStorage.setItem(this.PRODUCTS_KEY, JSON.stringify([]));
        }

        if (!localStorage.getItem(this.ORDERS_KEY)) {
            localStorage.setItem(this.ORDERS_KEY, JSON.stringify([]));
        }
    },

    getProducts() {
        try {
            return JSON.parse(localStorage.getItem(this.PRODUCTS_KEY)) || [];
        } catch (e) {
            return [];
        }
    },

    saveProducts(products) {
        localStorage.setItem(this.PRODUCTS_KEY, JSON.stringify(products));
    },

    getProductById(id) {
        const products = this.getProducts();
        return products.find(p => p.id === parseInt(id)) || null;
    },

    buildProductFormData(data, preserveExistingImages = false) {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
        const formData = new FormData();
        formData.append('_token', csrfToken);
        formData.append('name', data.name || '');
        formData.append('category', data.category || '');
        formData.append('subcategory', data.subcategory || '');
        formData.append('price', data.price ?? '');
        formData.append('old_price', data.old_price ?? '');
        formData.append('stock_units', data.stock_units ?? 0);
        formData.append('sku', data.sku || '');
        formData.append('material', data.material || '');
        formData.append('description', data.description || '');
        formData.append('subcategory', data.subcategory || '');
        formData.append('price', data.price ?? '');
        formData.append('old_price', data.old_price ?? '');
        formData.append('stock_units', data.stock_units ?? 0);
        formData.append('sku', data.sku || '');
        formData.append('material', data.material || '');
        formData.append('description', data.description || '');

        (data.sizes && data.sizes.length ? data.sizes : ['S', 'M', 'L']).forEach((size) => {
            formData.append('sizes[]', size);
        });
        (data.colors && data.colors.length ? data.colors : ['Pink', 'White']).forEach((color) => {
            formData.append('colors[]', color);
        });
        (data.color_codes || []).forEach((code) => {
            formData.append('color_codes[]', code || '');
        });
        Object.entries(data.size_stock || {}).forEach(([size, stock]) => {
            formData.append(`size_stock[${size}]`, stock);
        });

        const images = (data.images && data.images.length > 0)
            ? data.images.slice(0, this.MAX_IMAGES)
            : this.currentImages.slice(0, this.MAX_IMAGES);

        images.forEach((img, index) => {
            const file = this.currentImageFiles[index];
            if (file instanceof File) {
                formData.append(`images[${index}]`, file, file.name);
                formData.append(`image_names[${index}]`, file.name);
            } else if (preserveExistingImages && data.image_paths?.[index]) {
                formData.append(`existing_images[${index}]`, data.image_paths[index]);
            } else if (img && !String(img).startsWith('blob:')) {
                formData.append(`images[${index}]`, img);
                formData.append(`image_names[${index}]`, '');
            }
        });

        return formData;
    },

    inputValue(id, fallback = '') {
        return document.getElementById(id)?.value ?? fallback;
    },

    addProduct(data) {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
        const submitBtn = document.querySelector('button[type="submit"]');
        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Publishing to Store...';
        }

        fetch('/seller/products', {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: this.buildProductFormData(data, true)
        })
        .then(res => res.text().then(text => ({ res, text })))
        .then(({ res, text }) => {
            if (res.status === 413 || (text && text.includes('too large'))) {
                throw new Error('The uploaded images are too large for the server. Images have been automatically compressed, but please try selecting fewer or smaller images.');
            }

            let resData = null;
            try {
                resData = text ? JSON.parse(text) : null;
            } catch (e) {
                throw new Error(`Server returned an invalid response (HTTP ${res.status}).`);
            }

            if (!res.ok || !resData || resData.success !== true) {
                throw new Error(resData && resData.message
                    ? resData.message
                    : `Failed to save product to database (HTTP ${res.status}).`);
            }

            const savedProduct = resData.product;
            let products = this.getProducts();
            products = products.filter(p => p.id !== savedProduct.id);
            products.unshift(savedProduct);
            this.saveProducts(products);

            if (window.ZyraApp) {
                window.ZyraApp.showToast(`Product "${savedProduct.name}" added to database successfully!`, 'success');
            }
            setTimeout(() => {
                window.location.href = '/seller/products';
            }, 600);
        })
        .catch(err => {
            console.error('Error saving product to DB:', err);
            if (submitBtn) {
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="bi bi-cloud-upload me-1"></i> Publish Product';
            }
            if (window.ZyraApp) {
                window.ZyraApp.showToast(err.message || 'Could not save the product. Please check your details and try again.', 'danger');
            }
        });
    },

    updateProduct(id, data) {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
        const submitBtn = document.querySelector('button[type="submit"]');
        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Updating in Database...';
        }

        fetch(`/seller/products/${id}/update`, {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: this.buildProductFormData(data)
        })
        .then(res => res.text().then(text => ({ res, text })))
        .then(({ res, text }) => {
            if (res.status === 413 || (text && text.includes('too large'))) {
                throw new Error('The uploaded images are too large for the server. Images have been automatically compressed, but please try selecting fewer or smaller images.');
            }

            let resData = null;
            try {
                resData = text ? JSON.parse(text) : null;
            } catch (e) {
                throw new Error(`Server returned an invalid response (HTTP ${res.status}).`);
            }

            if (!res.ok || !resData || resData.success !== true) {
                throw new Error(resData && resData.message
                    ? resData.message
                    : `Failed to update product in database (HTTP ${res.status}).`);
            }

            const updated = resData.product;
            let products = this.getProducts();
            const idx = products.findIndex(p => p.id === parseInt(id));
            if (idx > -1) {
                products[idx] = updated;
            } else {
                products.unshift(updated);
            }
            this.saveProducts(products);

            if (window.ZyraApp) {
                window.ZyraApp.showToast('Product updated in database successfully!', 'success');
            }
            setTimeout(() => {
                window.location.href = '/seller/products';
            }, 600);
        })
        .catch(err => {
            console.error('Update error:', err);
            if (submitBtn) {
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="bi bi-check-circle me-1"></i> Save Changes';
            }
            if (window.ZyraApp) {
                window.ZyraApp.showToast(err.message || 'Could not update the product. Please try again.', 'danger');
            }
        });
    },

    deleteProduct(id) {
        if (!confirm('Are you sure you want to remove this product from your store and database?')) return;

        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
        fetch(`/seller/products/${id}/delete`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            }
        }).catch(() => {});

        let products = this.getProducts();
        const index = products.findIndex(p => p.id === parseInt(id));
        if (index > -1) {
            const removed = products.splice(index, 1)[0];
            this.saveProducts(products);
            if (window.ZyraApp) {
                window.ZyraApp.showToast(`Product "${removed.name}" removed from store and database.`, 'info');
            }
            this.detectPage();
        }
    },

    toggleStock(id) {
        let products = this.getProducts();
        const product = products.find(p => p.id === parseInt(id));
        if (product) {
            const newStockState = !product.stock;
            product.stock = newStockState;
            product.stock_units = newStockState ? (product.stock_units || 15) : 0;
            this.saveProducts(products);

            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
            fetch(`/seller/products/${id}/inventory`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({ in_stock: newStockState, stock_units: product.stock_units })
            }).catch(() => {});

            if (window.ZyraApp) {
                window.ZyraApp.showToast(`Stock status updated for ${product.name}`, 'info');
            }
            this.detectPage();
        }
    },

    /**
     * Buyer-side visibility toggle. The product stays in the DB and in this
     * panel; hidden products are excluded from every storefront surface.
     */
    toggleVisibility(id) {
        let products = this.getProducts();
        const product = products.find(p => p.id === parseInt(id));
        if (!product) return;

        const newState = !(product.is_visible !== false);
        product.is_visible = newState;
        this.saveProducts(products);

        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
        fetch(`/seller/products/${id}/visibility`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify({ is_visible: newState })
        })
            .then(res => res.json())
            .then(data => {
                if (window.ZyraApp) {
                    window.ZyraApp.showToast(
                        data.message || (newState ? 'Product is now visible to buyers.' : 'Product is now hidden from buyers.'),
                        newState ? 'success' : 'info'
                    );
                }
            })
            .catch(() => {
                // Roll back the optimistic flip if the server call failed.
                product.is_visible = !newState;
                this.saveProducts(products);
                this.detectPage();
                if (window.ZyraApp) {
                    window.ZyraApp.showToast('Could not update visibility. Please try again.', 'danger');
                }
            });

        this.detectPage();
    },

    updateStockQty(id, newQty) {
        let products = this.getProducts();
        const product = products.find(p => p.id === parseInt(id));
        if (product) {
            const qty = Math.max(0, parseInt(newQty) || 0);
            product.stock_units = qty;
            product.stock = qty > 0;
            this.saveProducts(products);

            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
            fetch(`/seller/products/${id}/inventory`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({ stock_units: qty, in_stock: qty > 0 })
            }).catch(() => {});

            this.detectPage();
        }
    },

    getOrders() {
        try {
            return JSON.parse(localStorage.getItem(this.ORDERS_KEY)) || [];
        } catch (e) {
            return [];
        }
    },

    syncSidebarBadges() {
        const orders = this.getOrders();
        const pendingCount = orders.filter(o => (o.status || '').toLowerCase() === 'pending').length;
        const packingCount = orders.filter(o => (o.status || '').toLowerCase() === 'processing').length;
        const dispatchedCount = orders.filter(o => (o.status || '').toLowerCase() === 'shipped').length;

        const bPend = document.getElementById('sideBadgePending');
        const bPack = document.getElementById('sideBadgePacking');
        const bDisp = document.getElementById('sideBadgeDispatched');

        if (bPend) {
            bPend.textContent = pendingCount;
            bPend.classList.toggle('d-none', pendingCount === 0);
        }
        if (bPack) {
            bPack.textContent = packingCount;
            bPack.classList.toggle('d-none', packingCount === 0);
        }
        if (bDisp) {
            bDisp.textContent = dispatchedCount;
            bDisp.classList.toggle('d-none', dispatchedCount === 0);
        }
    },

    setOrderStage(stage) {
        this.currentOrderStage = (stage || 'pending').toLowerCase();

        // Update stage pills active state
        document.querySelectorAll('#sellerOrderStagePills .nav-link').forEach(btn => {
            const btnStage = btn.getAttribute('data-stage');
            btn.classList.toggle('active', btnStage === this.currentOrderStage);
        });

        // Update sidebar links active state if on new-orders page
        const sideNew = document.getElementById('sideNavNewOrders');
        const sidePack = document.getElementById('sideNavPacking');
        const sideDisp = document.getElementById('sideNavDispatched');
        if (sideNew) sideNew.classList.toggle('active', this.currentOrderStage === 'pending');
        if (sidePack) sidePack.classList.toggle('active', this.currentOrderStage === 'processing');
        if (sideDisp) sideDisp.classList.toggle('active', this.currentOrderStage === 'shipped');

        // Update URL query string without reloading page
        if (window.history && window.history.replaceState) {
            const newUrl = `${window.location.pathname}?stage=${this.currentOrderStage}`;
            window.history.replaceState(null, '', newUrl);
        }

        this.renderNewOrdersPage();
    },

    startPackingOrder(orderId) {
        this.updateOrderStatus(orderId, 'Processing');
        if (window.ZyraApp) {
            window.ZyraApp.showToast(`Order ${orderId} moved to In Packing! Click "In Packing" to view.`, 'info');
        }
    },

    quickDispatchPrompt(dbId, orderDisplayId, currentAwb = '', currentCourier = '') {
        const awb = prompt(`Enter Courier Tracking / AWB number for order ${orderDisplayId} (Optional, press OK to continue):`, currentAwb);
        if (awb === null) return; // User cancelled prompt

        let courier = '';
        if (awb.trim()) {
            courier = prompt(`Enter Courier Partner Name (e.g. Delhivery, Blue Dart, Shiprocket):`, currentCourier || 'Delhivery') || 'Express Courier';
        }

        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
        fetch(`/seller/orders/${dbId}/tracking`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify({ awb_code: awb.trim(), courier_name: (courier || '').trim() })
        })
            .then(res => res.json())
            .then(result => {
                if (result.success) {
                    let orders = this.getOrders();
                    const order = orders.find(o => (o.db_id || o.id) == dbId || o.id === orderDisplayId);
                    if (order) {
                        order.status = result.order_status || 'Shipped';
                        if (awb) order.awb_code = awb.trim();
                        if (courier) order.courier_name = courier.trim();
                        localStorage.setItem(this.ORDERS_KEY, JSON.stringify(orders));
                    }
                    if (window.ZyraApp) {
                        window.ZyraApp.showToast(`Order ${orderDisplayId} marked as Dispatched! Customer notified via email.`, 'success');
                    }
                    this.syncSidebarBadges();
                    this.renderNewOrdersPage();
                    this.renderSalesPage();
                } else {
                    this.updateOrderStatus(orderDisplayId, 'Shipped');
                }
            })
            .catch(() => {
                this.updateOrderStatus(orderDisplayId, 'Shipped');
            });
    },

    updateOrderStatus(orderId, newStatus) {
        let orders = this.getOrders();
        const order = orders.find(o => o.id === orderId);
        if (order) {
            order.status = newStatus;
            localStorage.setItem(this.ORDERS_KEY, JSON.stringify(orders));

            const dbId = order.db_id || order.id;
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
            fetch(`/seller/orders/${dbId}/status`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({ status: newStatus })
            }).catch(() => {});

            if (window.ZyraApp) {
                window.ZyraApp.showToast(`Order ${orderId} marked as ${newStatus}`, 'success');
            }
            this.syncSidebarBadges();
            if (window.ZyraSeller.page === 'neworders' || window.location.pathname.includes('/seller/neworder')) {
                this.renderNewOrdersPage();
            }
            this.renderSalesPage();
        }
    },

    updateOrderTracking(dbId, orderDisplayId) {
        const awb = (document.querySelector(`.trk-awb-${dbId}`)?.value || '').trim();
        const courier = (document.querySelector(`.trk-courier-${dbId}`)?.value || '').trim();

        if (!awb && !courier) {
            if (window.ZyraApp) window.ZyraApp.showToast('Enter at least an AWB number.', 'danger');
            return;
        }

        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
        fetch(`/seller/orders/${dbId}/tracking`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify({ awb_code: awb, courier_name: courier })
        })
            .then(res => res.json())
            .then(result => {
                if (result.success) {
                    let orders = this.getOrders();
                    const order = orders.find(o => (o.db_id || o.id) == dbId || o.id === orderDisplayId);
                    if (order) {
                        if (awb) order.awb_code = awb;
                        if (courier) order.courier_name = courier;
                        if (result.order_status) order.status = result.order_status;
                        localStorage.setItem(this.ORDERS_KEY, JSON.stringify(orders));
                    }
                    if (window.ZyraApp) window.ZyraApp.showToast('Tracking updated — customer notified via email.', 'success');
                    this.syncSidebarBadges();
                    if (window.ZyraSeller.page === 'neworders' || window.location.pathname.includes('/seller/neworder')) {
                        this.renderNewOrdersPage();
                    }
                    this.renderSalesPage();
                } else {
                    if (window.ZyraApp) window.ZyraApp.showToast(result.message || 'Could not save tracking.', 'danger');
                }
            })
            .catch(() => {
                if (window.ZyraApp) window.ZyraApp.showToast('Could not save tracking.', 'danger');
            });
    },

    // =========================================================================
    // DYNAMIC MULTI-IMAGE MANAGEMENT (MAX 4)
    // =========================================================================
    renderImagesGrid() {
        const grid = document.getElementById('sellerImagesGrid');
        const counter = document.getElementById('imagesCountDisplay');
        if (!grid) return;

        if (counter) {
            counter.textContent = `${this.currentImages.length} / ${this.MAX_IMAGES} uploaded`;
        }

        let html = '';
        for (let i = 0; i < this.MAX_IMAGES; i++) {
            const img = this.currentImages[i];
            if (img) {
                html += `
                    <div class="seller-image-slot">
                        <img src="${img}" alt="Product Image ${i + 1}">
                        ${i === 0 ? '<span class="slot-badge-cover"><i class="bi bi-star-fill me-1"></i>Cover</span>' : `<span class="slot-badge-cover bg-secondary">Image ${i + 1}</span>`}
                        <button type="button" class="slot-remove-btn" onclick="ZyraSeller.removeImage(${i})" title="Remove image">
                            <i class="bi bi-x-lg"></i>
                        </button>
                    </div>
                `;
            } else {
                html += `
                    <div class="seller-image-slot slot-empty" onclick="document.getElementById('productFileInput').click()">
                        <i class="bi bi-cloud-arrow-up fs-4"></i>
                        <span>${i === 0 ? 'Upload Cover' : 'Add Image ' + (i + 1)}</span>
                    </div>
                `;
            }
        }
        grid.innerHTML = html;
    },

    addImage(urlOrData) {
        if (!urlOrData || !urlOrData.trim()) return;
        if (this.currentImages.length >= this.MAX_IMAGES) {
            if (window.ZyraApp) {
                window.ZyraApp.showToast(`Maximum ${this.MAX_IMAGES} images allowed.`, 'warning');
            }
            return;
        }

        this.currentImages.push(urlOrData.trim());
        this.currentImageFiles.push(null);
        if (Array.isArray(this.currentImagePaths)) this.currentImagePaths.push(null);
        this.renderImagesGrid();
        if (window.ZyraApp) {
            window.ZyraApp.showToast(`Image ${this.currentImages.length} added!`, 'success');
        }
    },

    removeImage(index) {
        if (index >= 0 && index < this.currentImages.length) {
            this.currentImages.splice(index, 1);
            this.currentImageFiles.splice(index, 1);
            if (Array.isArray(this.currentImagePaths)) this.currentImagePaths.splice(index, 1);
            this.renderImagesGrid();
            if (window.ZyraApp) {
                window.ZyraApp.showToast('Image removed.', 'info');
            }
        }
    },

    compressImage(file, maxWidth = 1200, maxHeight = 1200, quality = 0.72) {
        return new Promise((resolve) => {
            if (!file || !file.type || !file.type.startsWith('image/')) {
                return resolve(file);
            }
            // Keep uploaded files below common hosting limits.
            const targetBytes = 700 * 1024;
            if (file.size <= targetBytes) {
                return resolve(file);
            }

            const reader = new FileReader();
            reader.onload = (e) => {
                const img = new Image();
                img.onload = () => {
                    let width = img.width;
                    let height = img.height;

                    if (width > maxWidth || height > maxHeight) {
                        if (width > height) {
                            height = Math.round((height * maxWidth) / width);
                            width = maxWidth;
                        } else {
                            width = Math.round((width * maxHeight) / height);
                            height = maxHeight;
                        }
                    }

                    const canvas = document.createElement('canvas');
                    canvas.width = width;
                    canvas.height = height;
                    const ctx = canvas.getContext('2d');
                    ctx.drawImage(img, 0, 0, width, height);

                    const newName = file.name.replace(/\.[^/.]+$/, '') + '.jpg';
                    const encode = (width, height, encodeQuality) => {
                        canvas.width = width;
                        canvas.height = height;
                        ctx.drawImage(img, 0, 0, width, height);
                        canvas.toBlob((blob) => {
                            if (!blob) return resolve(file);

                            if (blob.size > targetBytes && encodeQuality > 0.5) {
                                return encode(Math.round(width * 0.75), Math.round(height * 0.75), 0.5);
                            }

                            const compressedFile = new File([blob], newName, {
                                type: 'image/jpeg',
                                lastModified: Date.now(),
                            });
                            resolve(compressedFile.size < file.size ? compressedFile : file);
                        }, 'image/jpeg', encodeQuality);
                    };

                    encode(width, height, quality);
                };
                img.onerror = () => resolve(file);
                img.src = e.target.result;
            };
            reader.onerror = () => resolve(file);
            reader.readAsDataURL(file);
        });
    },

    async handleFileInput(event) {
        const files = Array.from(event.target.files);
        if (!files.length) return;

        const remainingSlots = this.MAX_IMAGES - this.currentImages.length;
        if (remainingSlots <= 0) {
            if (window.ZyraApp) {
                window.ZyraApp.showToast(`Maximum ${this.MAX_IMAGES} images reached.`, 'warning');
            }
            return;
        }

        const filesToProcess = files.slice(0, remainingSlots);
        if (window.ZyraApp) {
            window.ZyraApp.showToast('Optimizing images for fast upload...', 'info');
        }

        for (const file of filesToProcess) {
            const optimized = await this.compressImage(file);
            this.currentImages.push(URL.createObjectURL(optimized));
            this.currentImageFiles.push(optimized);
            if (Array.isArray(this.currentImagePaths)) this.currentImagePaths.push(null);
        }

        this.renderImagesGrid();
        if (window.ZyraApp) {
            window.ZyraApp.showToast(`${filesToProcess.length} image(s) ready!`, 'success');
        }

        // Reset input
        event.target.value = '';
    },

    // =========================================================================
    // DYNAMIC COLOR SELECTION WITH '+' ICON
    // =========================================================================
    renderColorsList() {
        const container = document.getElementById('sellerColorsContainer');
        if (!container) return;

        let html = '';
        this.availableColors.forEach(c => {
            const isSelected = this.selectedColors.includes(c.name);
            html += `
                <div class="seller-color-chip ${isSelected ? 'active' : ''}" onclick="ZyraSeller.toggleColor('${c.name}')">
                    <span class="color-dot" style="background-color: ${c.hex};"></span>
                    <span>${c.name}</span>
                    ${isSelected ? '<i class="bi bi-check2 ms-1"></i>' : ''}
                </div>
            `;
        });

        // Add the '+' Add Color trigger button
        html += `
            <button type="button" class="btn-add-color-trigger" data-bs-toggle="modal" data-bs-target="#addColorModal">
                <i class="bi bi-plus-lg"></i> Add Color
            </button>
        `;

        container.innerHTML = html;
    },

    toggleColor(colorName) {
        const idx = this.selectedColors.indexOf(colorName);
        if (idx > -1) {
            this.selectedColors.splice(idx, 1);
        } else {
            this.selectedColors.push(colorName);
        }
        this.renderColorsList();
    },

    addNewColor(name, hex) {
        const cleanName = name ? name.trim() : '';
        const cleanHex = hex ? hex.trim() : '#a57cbb';

        if (!cleanName) {
            if (window.ZyraApp) window.ZyraApp.showToast('Please enter a color name.', 'danger');
            return false;
        }

        // Check if color already exists
        const existingColor = this.availableColors.find(c => c.name.toLowerCase() === cleanName.toLowerCase());
        if (!existingColor) {
            this.availableColors.push({ name: cleanName, hex: cleanHex });
        } else {
            existingColor.hex = cleanHex;
        }

        // Auto select newly created color
        if (!this.selectedColors.includes(cleanName)) {
            this.selectedColors.push(cleanName);
        }

        this.renderColorsList();

        if (window.ZyraApp) {
            window.ZyraApp.showToast(`Color "${cleanName}" added and selected!`, 'success');
        }

        // Close modal if open
        const modalEl = document.getElementById('addColorModal');
        if (modalEl && window.bootstrap) {
            const bsModal = bootstrap.Modal.getInstance(modalEl);
            if (bsModal) bsModal.hide();
        }

        return true;
    },

    selectPresetColor(name, hex) {
        const nameInput = document.getElementById('newColorName');
        const hexInput = document.getElementById('newColorHex');
        if (nameInput) nameInput.value = name;
        if (hexInput) hexInput.value = hex;
        this.addNewColor(name, hex);
    },

    // =========================================================================
    // PAGE INITIALIZERS & RENDERERS
    // =========================================================================
    detectPage() {
        const path = window.location.pathname;
        if (path.endsWith('/seller') || path.includes('/seller/dashboard')) {
            this.renderDashboard();
        } else if (path.includes('/seller/products/add')) {
            this.initAddProductPage();
        } else if (path.includes('/edit')) {
            this.initEditProductPage();
        } else if (path.includes('/seller/products')) {
            this.renderProductsPage();
        } else if (path.includes('/seller/inventory')) {
            this.renderInventoryPage();
        } else if (path.includes('/seller/sales')) {
            this.renderSalesPage();
        } else if (path.includes('/seller/neworder')) {
            const urlStage = new URLSearchParams(window.location.search).get('stage');
            if (urlStage) {
                this.currentOrderStage = urlStage.toLowerCase();
            }
            this.renderNewOrdersPage();
        } else if (path.includes('/seller/analytics')) {
            this.renderAnalyticsPage();
        } else if (path.includes('/seller/settings')) {
            this.initSettingsPage();
        }
    },

    renderDashboard() {
        const products = this.getProducts();
        const orders = this.getOrders();

        const totalStock = products.reduce((acc, p) => acc + (p.stock_units || 0), 0);
        const lowStockCount = products.filter(p => (p.stock_units || 0) < 10).length;
        const totalRevenue = orders.reduce((acc, o) => acc + (o.total || 0), 0);

        const elProd = document.getElementById('sellerStatProducts');
        const elStock = document.getElementById('sellerStatStockUnits');
        const elLow = document.getElementById('sellerStatLowStock');
        const elSales = document.getElementById('sellerStatSales');
        const elRev = document.getElementById('sellerStatRevenue');

        if (elProd) elProd.textContent = products.length;
        if (elStock) elStock.textContent = totalStock;
        if (elLow) elLow.textContent = lowStockCount;
        if (elSales) elSales.textContent = orders.length;
        if (elRev) elRev.textContent = `₹${totalRevenue.toLocaleString('en-IN')}`;

        // Render Recent Products Table
        const recentTable = document.getElementById('sellerDashboardRecentTable');
        if (recentTable) {
            const recent = products.slice(0, 5);
            let html = '';
            recent.forEach(p => {
                const isLow = (p.stock_units || 0) < 10;
                const isOut = (p.stock_units || 0) === 0;
                const statusBadge = isOut ? '<span class="badge badge-status-outstock">Out of Stock</span>'
                    : (isLow ? '<span class="badge badge-status-lowstock">Low Stock</span>' : '<span class="badge badge-status-active">Active</span>');

                html += `
                    <tr>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <img src="${p.image}" alt="${p.name}" class="seller-product-img">
                                <div>
                                    <div class="fw-semibold text-dark small text-truncate" style="max-width: 260px;">${p.name}</div>
                                    <small class="text-muted">SKU: ${p.sku} | ${p.category}</small>
                                </div>
                            </div>
                        </td>
                        <td class="fw-bold">₹${p.price}</td>
                        <td>${p.stock_units || 0} units</td>
                        <td>${statusBadge}</td>
                    </tr>
                `;
            });
            recentTable.innerHTML = html;
        }

        // Render Recent Orders Table
        const recentOrdersTable = document.getElementById('sellerDashboardOrdersTable');
        if (recentOrdersTable) {
            let oHtml = '';
            orders.slice(0, 5).forEach(o => {
                let badgeClass = 'bg-secondary';
                if (o.status === 'Delivered') badgeClass = 'bg-success';
                else if (o.status === 'Shipped') badgeClass = 'bg-primary';
                else if (o.status === 'Processing') badgeClass = 'bg-warning text-dark';

                oHtml += `
                    <tr>
                        <td class="fw-bold small">${o.id}</td>
                        <td>
                            <div class="fw-semibold small">${o.customer}</div>
                            <small class="text-muted">${o.city}</small>
                        </td>
                        <td class="fw-bold small">₹${o.total}</td>
                        <td><span class="badge ${badgeClass}">${o.status}</span></td>
                    </tr>
                `;
            });
            recentOrdersTable.innerHTML = oHtml;
        }
    },

    renderProductsPage() {
        const tableBody = document.getElementById('sellerProductsTableBody');
        const countDisplay = document.getElementById('sellerProductsTotalCount');
        const searchInput = document.getElementById('sellerProductSearch');
        const categoryFilter = document.getElementById('sellerProductCategoryFilter');

        if (!tableBody) return;

        let products = this.getProducts();
        const totalCount = products.length;

        const query = searchInput ? searchInput.value.toLowerCase().trim() : '';
        const cat = categoryFilter ? categoryFilter.value : 'all';

        if (query) {
            products = products.filter(p => p.name.toLowerCase().includes(query) || (p.sku && p.sku.toLowerCase().includes(query)));
        }

        if (cat !== 'all') {
            products = products.filter(p => p.category.toLowerCase() === cat.toLowerCase());
        }

        // Grand total next to the page title — always the full catalog size,
        // never changed by the search box or category filter.
        const headerCount = document.getElementById('sellerProductsTotalHeader');
        if (headerCount) {
            headerCount.innerHTML = '<i class="bi bi-box-seam me-1"></i>Total: ' + totalCount + ' Product' + (totalCount === 1 ? '' : 's');
        }

        // Toolbar badge shows what is currently visible in the table.
        if (countDisplay) {
            const isFiltered = products.length !== totalCount;
            countDisplay.textContent = isFiltered
                ? `${products.length} of ${totalCount} products`
                : `${totalCount} product${totalCount === 1 ? '' : 's'}`;
        }

        if (products.length === 0) {
            tableBody.innerHTML = `<tr><td colspan="7" class="text-center py-5 text-muted">No products found matching your search.</td></tr>`;
            return;
        }

        let html = '';
        products.forEach(p => {
            const isLow = (p.stock_units || 0) < 10 && (p.stock_units || 0) > 0;
            const isOut = (p.stock_units || 0) === 0;
            const statusBadge = isOut ? '<span class="badge badge-status-outstock">Out of Stock</span>'
                : (isLow ? '<span class="badge badge-status-lowstock">Low Stock</span>' : '<span class="badge badge-status-active">Active</span>');
            const imagesCount = p.images ? p.images.length : 1;

            html += `
                <tr>
                    <td>
                        <div class="d-flex align-items-center gap-3">
                            <div class="position-relative">
                                <img src="${p.image}" alt="${p.name}" class="seller-product-img">
                                ${imagesCount > 1 ? `<span class="badge bg-dark position-absolute bottom-0 end-0 py-0 px-1" style="font-size:0.65rem;"><i class="bi bi-images me-1"></i>${imagesCount}</span>` : ''}
                            </div>
                            <div>
                                <div class="fw-bold text-dark mb-1" style="max-width: 320px;">${p.name}</div>
                                <div class="small text-muted">SKU: <strong>${p.sku}</strong> | ${p.category} &rsaquo; ${p.subcategory}</div>
                                <div class="small text-muted mt-1">Colors: <span class="fw-semibold text-dark">${p.colors ? p.colors.join(', ') : 'Standard'}</span></div>
                            </div>
                        </div>
                    </td>
                    <td>
                        <span class="fw-bold">₹${p.price}</span>
                        ${p.old_price ? `<br><small class="text-muted text-decoration-line-through">₹${p.old_price}</small>` : ''}
                    </td>
                    <td>
                        <strong>${p.stock_units || 0}</strong>
                        ${isLow ? '<span class="text-danger small ms-1"><i class="bi bi-exclamation-circle" title="Low stock"></i></span>' : ''}
                    </td>
                    <td>${statusBadge}</td>
                    <td>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" role="switch" ${p.is_visible !== false ? 'checked' : ''} onchange="ZyraSeller.toggleVisibility(${p.id})" title="${p.is_visible !== false ? 'Visible to buyers – click to hide' : 'Hidden from buyers – click to show'}">
                        </div>
                        <div class="small ${p.is_visible !== false ? 'text-muted' : 'text-danger'} mt-1">${p.is_visible !== false ? 'Visible' : 'Hidden'}</div>
                    </td>
                    <td class="text-end">
                        <div class="btn-group btn-group-sm">
                            <a href="/seller/products/${p.id}/edit" class="btn btn-outline-dark" title="Edit">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <a href="/product/${p.id}" target="_blank" class="btn btn-outline-secondary" title="View Storefront">
                                <i class="bi bi-box-arrow-up-right"></i>
                            </a>
                            <button type="button" class="btn btn-outline-danger" onclick="ZyraSeller.deleteProduct(${p.id})" title="Delete">
                                <i class="bi bi-trash3"></i>
                            </button>
                        </div>
                    </td>
                </tr>
            `;
        });

        tableBody.innerHTML = html;
    },

    renderInventoryPage() {
        const tableBody = document.getElementById('sellerInventoryTableBody');
        const lowStockNotice = document.getElementById('inventoryLowStockAlert');
        if (!tableBody) return;

        const products = this.getProducts();
        const lowStock = products.filter(p => (p.stock_units || 0) < 10);

        if (lowStockNotice) {
            lowStockNotice.style.display = lowStock.length > 0 ? 'block' : 'none';
            const countSpan = lowStockNotice.querySelector('.low-count');
            if (countSpan) countSpan.textContent = lowStock.length;
        }

        let html = '';
        products.forEach(p => {
            const isLow = (p.stock_units || 0) < 10;
            const isOut = (p.stock_units || 0) === 0;

            html += `
                <tr class="${isLow ? 'table-warning-subtle' : ''}">
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <img src="${p.image}" alt="${p.name}" class="seller-product-img">
                            <div>
                                <div class="fw-semibold small">${p.name}</div>
                                <small class="text-muted">SKU: ${p.sku}</small>
                            </div>
                        </div>
                    </td>
                    <td>${p.category}</td>
                    <td>₹${p.price}</td>
                    <td>
                        <div class="stock-stepper">
                            <button type="button" onclick="ZyraSeller.updateStockQty(${p.id}, ${(p.stock_units || 0) - 1})">-</button>
                            <input type="text" value="${p.stock_units || 0}" onchange="ZyraSeller.updateStockQty(${p.id}, this.value)">
                            <button type="button" onclick="ZyraSeller.updateStockQty(${p.id}, ${(p.stock_units || 0) + 1})">+</button>
                        </div>
                    </td>
                    <td>
                        ${isOut ? '<span class="badge bg-danger">Out of Stock</span>' : (isLow ? '<span class="badge bg-warning text-dark">Low Stock (' + p.stock_units + ')</span>' : '<span class="badge bg-success">Healthy Stock</span>')}
                    </td>
                    <td>
                        <button type="button" class="btn btn-sm btn-dark" onclick="ZyraSeller.openRestockModal(${p.id})">
                            <i class="bi bi-box-arrow-in-down me-1"></i> Restock
                        </button>
                    </td>
                </tr>
            `;
        });

        tableBody.innerHTML = html;
    },

    // =========================================================================
    // PER-SIZE RESTOCK MODAL
    // =========================================================================
    restockProductId: null,

    openRestockModal(id) {
        const product = this.getProducts().find(p => p.id === parseInt(id));
        if (!product) return;

        this.restockProductId = product.id;

        const title = document.getElementById('restockModalTitle');
        const subtitle = document.getElementById('restockModalSubtitle');
        const body = document.getElementById('restockModalBody');
        const noSizes = document.getElementById('restockModalNoSizes');

        if (title) title.textContent = product.name || 'Restock Product';
        if (subtitle) subtitle.textContent = `SKU: ${product.sku || '-'} · ${product.category || ''}`;

        const sizes = Array.isArray(product.sizes) ? product.sizes : [];
        const sizeStock = product.size_stock || {};

        if (!sizes.length) {
            if (body) body.innerHTML = '';
            if (noSizes) noSizes.classList.remove('d-none');
            const totalInput = document.getElementById('restockTotalUnits');
            if (totalInput) {
                totalInput.value = product.stock_units || 0;
                totalInput.onchange = () => this.updateRestockTotals();
                totalInput.oninput = () => this.updateRestockTotals();
            }
        } else {
            if (noSizes) noSizes.classList.add('d-none');
            let html = `
                <div class="row fw-semibold text-muted text-uppercase border-bottom pb-2 mb-2 d-none d-sm-flex" style="font-size: .7rem; letter-spacing: .04em;">
                    <div class="col-3">Size</div>
                    <div class="col-3 text-center">In Stock</div>
                    <div class="col-3 text-center">Add Qty</div>
                    <div class="col-3 text-center">New Stock</div>
                </div>
            `;

            sizes.forEach(size => {
                // Size with explicit per-size stock shows it; otherwise it inherits the product total.
                const current = sizeStock.hasOwnProperty(size) ? sizeStock[size] : (product.stock_units || 0);
                html += `
                    <div class="row g-2 align-items-center border-bottom py-2 restock-size-row" data-size="${size}" data-current="${current}">
                        <div class="col-3">
                            <span class="fw-bold badge bg-dark-subtle text-dark border px-2 py-1" style="font-size: .8rem;">${size}</span>
                        </div>
                        <div class="col-3 text-center small fw-semibold">${current}</div>
                        <div class="col-3">
                            <div class="stock-stepper mx-auto" style="max-width: 130px;">
                                <button type="button" onclick="ZyraSeller.stepRestockQty(this, -1)">-</button>
                                <input type="number" min="0" step="1" value="0" oninput="ZyraSeller.updateRestockTotals()">
                                <button type="button" onclick="ZyraSeller.stepRestockQty(this, 1)">+</button>
                            </div>
                        </div>
                        <div class="col-3 text-center">
                            <span class="fw-bold new-stock text-success">${current}</span>
                        </div>
                    </div>
                `;
            });

            // Quick "+20 all sizes" shortcut
            html += `
                <div class="d-flex flex-wrap gap-2 mt-3">
                    <button type="button" class="btn btn-sm btn-outline-dark" onclick="ZyraSeller.applyRestockAll(5)">+5 all sizes</button>
                    <button type="button" class="btn btn-sm btn-outline-dark" onclick="ZyraSeller.applyRestockAll(10)">+10 all sizes</button>
                    <button type="button" class="btn btn-sm btn-outline-dark" onclick="ZyraSeller.applyRestockAll(20)">+20 all sizes</button>
                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="ZyraSeller.applyRestockAll(0)">Clear</button>
                </div>
            `;

            if (body) body.innerHTML = html;
        }

        this.updateRestockTotals();

        const modalEl = document.getElementById('restockModal');
        if (modalEl && window.bootstrap) {
            bootstrap.Modal.getOrCreateInstance(modalEl).show();
        }
    },

    stepRestockQty(button, delta) {
        const input = button.parentElement.querySelector('input');
        if (!input) return;
        input.value = Math.max(0, (parseInt(input.value) || 0) + delta);
        this.updateRestockTotals();
    },

    applyRestockAll(amount) {
        document.querySelectorAll('#restockModalBody .restock-size-row input').forEach(input => {
            input.value = Math.max(0, amount);
        });
        this.updateRestockTotals();
    },

    updateRestockTotals() {
        let total = 0;
        document.querySelectorAll('#restockModalBody .restock-size-row').forEach(row => {
            const input = row.querySelector('input');
            const current = parseInt(row.getAttribute('data-current')) || 0;
            const add = Math.max(0, parseInt(input.value) || 0);
            total += add;
            const newStockEl = row.querySelector('.new-stock');
            if (newStockEl) {
                newStockEl.textContent = current + add;
                newStockEl.classList.toggle('text-success', add > 0);
                newStockEl.classList.toggle('text-muted', add === 0);
            }
        });

        // No-sizes fallback: value replaces the total
        const totalUnitsInput = document.getElementById('restockTotalUnits');
        if (totalUnitsInput && !document.getElementById('restockModalNoSizes')?.classList.contains('d-none')) {
            total = 0;
        }

        const el = document.getElementById('restockGrandTotal');
        if (el) el.textContent = total;
    },

    saveRestock() {
        const id = this.restockProductId;
        if (!id) return;

        const noSizesVisible = document.getElementById('restockModalNoSizes')
            && !document.getElementById('restockModalNoSizes').classList.contains('d-none');

        let payload = null;
        let toastQty = 0;

        if (noSizesVisible) {
            const total = Math.max(0, parseInt(document.getElementById('restockTotalUnits')?.value) || 0);
            if (total <= 0) {
                ZyraApp.showToast('Enter a stock quantity greater than 0.', 'warning');
                return;
            }
            payload = { stock_units: total, in_stock: total > 0 };
            toastQty = total;
        } else {
            const quantities = {};
            document.querySelectorAll('#restockModalBody .restock-size-row').forEach(row => {
                const size = row.getAttribute('data-size');
                const add = Math.max(0, parseInt(row.querySelector('input')?.value) || 0);
                if (add > 0) quantities[size] = add;
            });

            if (!Object.keys(quantities).length) {
                ZyraApp.showToast('Enter a restock quantity for at least one size.', 'warning');
                return;
            }
            payload = { quantities };
            toastQty = Object.values(quantities).reduce((a, b) => a + b, 0);
        }

        const btn = document.getElementById('restockSaveBtn');
        if (btn) { btn.disabled = true; btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Saving…'; }

        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
        const url = noSizesVisible
            ? `/seller/products/${id}/inventory`
            : `/seller/products/${id}/restock`;

        fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify(payload)
        })
        .then(res => res.json().then(data => ({ status: res.status, data })))
        .then(({ status, data }) => {
            if (status >= 400) {
                const firstError = data.errors ? Object.values(data.errors)[0] : null;
                ZyraApp.showToast(firstError ? firstError[0] : (data.message || 'Restock failed.'), 'danger');
                return;
            }

            // Sync local cache with the server truth
            let products = this.getProducts();
            const index = products.findIndex(p => p.id === parseInt(id));
            if (index > -1 && data.product) {
                products[index] = { ...products[index], ...data.product };
                this.saveProducts(products);
            }

            const modalEl = document.getElementById('restockModal');
            if (modalEl && window.bootstrap) {
                bootstrap.Modal.getOrCreateInstance(modalEl).hide();
            }

            ZyraApp.showToast(data.message || `Restocked ${toastQty} unit(s)!`, 'success');
            this.detectPage();
        })
        .catch(() => {
            ZyraApp.showToast('Could not reach the server. Please try again.', 'danger');
        })
        .finally(() => {
            if (btn) { btn.disabled = false; btn.innerHTML = '<i class="bi bi-check2-circle me-1"></i> Save Restock'; }
        });
    },

    salesViewMode: 'cards',
    newOrdersViewMode: 'cards',
    salesStatusFilter: 'all',

    switchSalesView(mode) {
        this.salesViewMode = mode;
        const btnCards = document.getElementById('btnViewCards');
        const btnTable = document.getElementById('btnViewTable');
        const cardsCont = document.getElementById('sellerOrdersCardsContainer');
        const tableCont = document.getElementById('sellerOrdersTableContainer');

        if (btnCards) btnCards.classList.toggle('active', mode === 'cards');
        if (btnTable) btnTable.classList.toggle('active', mode === 'table');

        if (cardsCont) cardsCont.classList.toggle('d-none', mode !== 'cards');
        if (tableCont) tableCont.classList.toggle('d-none', mode !== 'table');

        this.renderSalesPage();
    },

    switchNewOrdersView(mode) {
        this.newOrdersViewMode = mode;
        const btnCards = document.getElementById('btnNewViewCards');
        const btnTable = document.getElementById('btnNewViewTable');
        const cardsCont = document.getElementById('sellerNewOrdersCardsContainer');
        const tableCont = document.getElementById('sellerNewOrdersTableContainer');

        if (btnCards) btnCards.classList.toggle('active', mode === 'cards');
        if (btnTable) btnTable.classList.toggle('active', mode === 'table');

        if (cardsCont) cardsCont.classList.toggle('d-none', mode !== 'cards');
        if (tableCont) tableCont.classList.toggle('d-none', mode !== 'table');

        this.renderNewOrdersPage();
    },

    filterSalesStatus(status) {
        this.salesStatusFilter = status;
        document.querySelectorAll('#salesStatusPills .nav-link').forEach(btn => {
            const s = btn.getAttribute('data-status');
            btn.classList.toggle('active', s.toLowerCase() === status.toLowerCase());
        });
        this.renderSalesPage();
    },

    buildOrderCardHtml(o, isNewOrdersPage = false) {
        const rawStatus = (o.status || 'Pending');
        const statusLower = rawStatus.toLowerCase();

        // Status Badge HTML
        let statusBadge = '<span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1"><i class="bi bi-inbox me-1"></i>New Order</span>';
        if (statusLower === 'processing') {
            statusBadge = '<span class="badge bg-warning-subtle text-dark border border-warning-subtle px-2 py-1"><i class="bi bi-box-seam me-1"></i>In Packing</span>';
        } else if (statusLower === 'shipped') {
            statusBadge = '<span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1"><i class="bi bi-truck me-1"></i>Dispatched</span>';
        } else if (statusLower === 'delivered') {
            statusBadge = '<span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1"><i class="bi bi-check-circle me-1"></i>Delivered</span>';
        }

        const streetAddr = o.shipping_address || '';
        const locationPart = [o.city, o.state, o.pincode].filter(Boolean).join(', ');
        const fullAddressText = [streetAddr, locationPart].filter(Boolean).join(', ') || o.full_address || `${o.city || ''}`;
        const escapedAddressForCopy = fullAddressText.replace(/"/g, '&quot;').replace(/'/g, "\\'");
        const cleanPhoneDigits = (o.phone || '').replace(/[^0-9]/g, '');

        let actionsBlock = '';
        if (isNewOrdersPage) {
            if (statusLower === 'pending') {
                actionsBlock = `
                    <div class="d-flex flex-column gap-2">
                        <button type="button" class="btn btn-warning text-dark fw-bold d-flex align-items-center justify-content-center gap-2 shadow-sm py-2" onclick="ZyraSeller.startPackingOrder('${o.id}')">
                            <i class="bi bi-box-seam fs-6"></i> Start Packing
                        </button>
                        <button type="button" class="btn btn-outline-success d-flex align-items-center justify-content-center gap-2 py-2" onclick="ZyraSeller.quickDispatchPrompt('${o.db_id || o.id}', '${o.id}')">
                            <i class="bi bi-truck fs-6"></i> Packed &amp; Dispatch
                        </button>
                    </div>
                `;
            } else if (statusLower === 'processing') {
                actionsBlock = `
                    <div class="d-flex flex-column gap-2">
                        <button type="button" class="btn btn-success fw-bold d-flex align-items-center justify-content-center gap-2 shadow-sm py-2" onclick="ZyraSeller.quickDispatchPrompt('${o.db_id || o.id}', '${o.id}')">
                            <i class="bi bi-truck fs-6"></i> Packed &amp; Dispatch
                        </button>
                        <button type="button" class="btn btn-sm btn-light border text-muted d-flex align-items-center justify-content-center gap-1" onclick="ZyraSeller.updateOrderStatus('${o.id}', 'Pending')">
                            <i class="bi bi-arrow-counterclockwise"></i> Move back to New Orders
                        </button>
                    </div>
                `;
            } else if (statusLower === 'shipped') {
                actionsBlock = `
                    <div class="d-flex flex-column gap-2">
                        ${o.awb_code ? `
                            <div class="p-2 rounded bg-light border text-center">
                                <div class="small text-muted text-uppercase" style="font-size: 0.68rem;">Courier &amp; AWB</div>
                                <div class="fw-bold font-monospace text-primary">${o.awb_code}</div>
                                <div class="small text-muted">${o.courier_name || 'Express Courier'}</div>
                            </div>
                        ` : ''}
                        <button type="button" class="btn btn-sm btn-outline-primary d-flex align-items-center justify-content-center gap-1" onclick="ZyraSeller.quickDispatchPrompt('${o.db_id || o.id}', '${o.id}', '${o.awb_code || ''}', '${o.courier_name || ''}')">
                            <i class="bi bi-pencil-square"></i> ${o.awb_code ? 'Edit Tracking' : 'Add AWB Number'}
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-success d-flex align-items-center justify-content-center gap-1" onclick="ZyraSeller.updateOrderStatus('${o.id}', 'Delivered')">
                            <i class="bi bi-check2-circle"></i> Mark Delivered
                        </button>
                    </div>
                `;
            } else {
                actionsBlock = `
                    <div class="text-center py-2">
                        <span class="badge bg-success-subtle text-success border border-success-subtle p-2">
                            <i class="bi bi-check-all me-1"></i>Delivered Successfully
                        </span>
                    </div>
                `;
            }
        } else {
            // Sales Hub Actions
            actionsBlock = `
                <div class="mb-2">
                    <label class="form-label small text-muted mb-1 fw-semibold">Status:</label>
                    <select class="form-select form-select-sm fw-bold border-secondary-subtle" onchange="ZyraSeller.updateOrderStatus('${o.id}', this.value)">
                        <option value="Pending" ${o.status === 'Pending' ? 'selected' : ''}>⏳ Pending (New Order)</option>
                        <option value="Processing" ${o.status === 'Processing' ? 'selected' : ''}>📦 In Packing (Processing)</option>
                        <option value="Shipped" ${o.status === 'Shipped' ? 'selected' : ''}>🚚 Dispatched (Shipped)</option>
                        <option value="Delivered" ${o.status === 'Delivered' ? 'selected' : ''}>✅ Delivered</option>
                    </select>
                </div>
                <div class="tracking-box p-2 bg-light rounded border border-light-subtle">
                    <div class="row g-1 mb-2">
                        <div class="col-7">
                            <input type="text" class="form-control form-control-sm trk-awb-${o.db_id || o.id}" placeholder="AWB / Waybill #" value="${o.awb_code || ''}">
                        </div>
                        <div class="col-5">
                            <input type="text" class="form-control form-control-sm trk-courier-${o.db_id || o.id}" placeholder="Courier" value="${o.courier_name || ''}">
                        </div>
                    </div>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-sm btn-dark flex-grow-1" onclick="ZyraSeller.updateOrderTracking('${o.db_id || o.id}', '${o.id}')">
                            <i class="bi bi-save me-1"></i> Save AWB
                        </button>
                        ${o.awb_code ? `
                            <a href="https://shiprocket.co/tracking/${encodeURIComponent(o.awb_code)}" target="_blank" class="btn btn-sm btn-outline-primary" title="Track package online">
                                <i class="bi bi-box-arrow-up-right"></i>
                            </a>
                        ` : ''}
                    </div>
                </div>
            `;
        }

        return `
            <div class="seller-order-card mb-4" id="order-card-${o.id}">
                <!-- Header -->
                <div class="seller-order-card-header d-flex flex-wrap align-items-center justify-content-between p-3 border-bottom bg-light">
                    <div class="d-flex align-items-center flex-wrap gap-2">
                        <span class="order-id-badge fw-bold font-monospace px-2 py-1 rounded bg-white border text-dark">
                            ${o.id}
                        </span>
                        <span class="badge bg-light text-muted border">ID #${o.db_id || o.id}</span>
                        <span class="text-muted small ms-1"><i class="bi bi-calendar3 me-1"></i>${o.date || 'Today'}</span>
                        <span class="badge bg-white text-dark border px-2 py-1">
                            <i class="bi bi-credit-card me-1 text-primary"></i>${o.payment || 'Prepaid'}
                        </span>
                    </div>
                    <div class="d-flex align-items-center gap-3 mt-2 mt-md-0">
                        <div class="fw-bold fs-5 text-dark">₹${(o.total || 0).toLocaleString('en-IN')}</div>
                        <div>${statusBadge}</div>
                    </div>
                </div>

                <!-- Body -->
                <div class="seller-order-card-body p-3 p-md-4">
                    <div class="row g-3 g-lg-4">
                        <!-- Col 1: Delivery Address & Customer -->
                        <div class="col-lg-4 col-md-6 border-end-lg">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <span class="text-uppercase small fw-bold text-danger" style="letter-spacing: 0.5px; font-size: 0.72rem;">
                                    <i class="bi bi-geo-alt-fill me-1"></i>Delivery Address
                                </span>
                                <button type="button" class="btn btn-sm btn-outline-secondary py-0 px-2" style="font-size: 0.72rem;" onclick="navigator.clipboard.writeText('${escapedAddressForCopy}').then(() => window.ZyraApp && window.ZyraApp.showToast('Delivery address copied!', 'info'))">
                                    <i class="bi bi-clipboard me-1"></i>Copy
                                </button>
                            </div>
                            <div class="fw-bold text-dark fs-6 mb-1 d-flex align-items-center gap-2">
                                <i class="bi bi-person-circle text-secondary"></i>
                                <span>${o.customer || 'Customer'}</span>
                            </div>
                            <div class="text-dark small lh-base mb-2 p-2 bg-light rounded border border-light-subtle">
                                <div class="fw-medium text-dark">${streetAddr ? streetAddr : (o.full_address || '<span class="text-muted fst-italic">Street address not provided</span>')}</div>
                                <div class="text-muted mt-1 small">${locationPart || 'Location details pending'}</div>
                            </div>
                            ${o.phone ? `
                                <div class="small text-dark fw-semibold d-flex align-items-center gap-2 flex-wrap">
                                    <span class="text-muted"><i class="bi bi-telephone-fill text-success me-1"></i>Phone:</span>
                                    <a href="tel:${o.phone}" class="text-decoration-none text-dark fw-bold">${o.phone}</a>
                                    ${cleanPhoneDigits ? `
                                        <a href="https://wa.me/91${cleanPhoneDigits}" target="_blank" class="badge bg-success-subtle text-success border border-success-subtle text-decoration-none py-1" title="Message on WhatsApp">
                                            <i class="bi bi-whatsapp"></i> WhatsApp
                                        </a>
                                    ` : ''}
                                </div>
                            ` : ''}
                        </div>

                        <!-- Col 2: Products Ordered -->
                        <div class="col-lg-4 col-md-6 border-end-lg">
                            <span class="text-uppercase small fw-bold text-primary d-block mb-2" style="letter-spacing: 0.5px; font-size: 0.72rem;">
                                <i class="bi bi-bag-check-fill me-1"></i>Items Ordered
                            </span>
                            <div class="p-3 bg-light rounded border border-light-subtle h-100 d-flex flex-column justify-content-between">
                                <div>
                                    <div class="fw-semibold text-dark fs-6 mb-2 lh-base">${o.items}</div>
                                </div>
                                <div class="d-flex align-items-center justify-content-between text-muted small pt-2 border-top mt-2">
                                    <span>Payment Method</span>
                                    <span class="fw-bold text-dark">${o.payment || 'Prepaid'}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Col 3: Logistics & Fulfillment Actions -->
                        <div class="col-lg-4 col-md-12">
                            <span class="text-uppercase small fw-bold text-success d-block mb-2" style="letter-spacing: 0.5px; font-size: 0.72rem;">
                                <i class="bi bi-truck me-1"></i>Fulfillment &amp; Logistics
                            </span>
                            ${actionsBlock}
                        </div>
                    </div>
                </div>
            </div>
        `;
    },

    renderSalesPage() {
        const cardsContainer = document.getElementById('sellerOrdersCardsContainer');
        const tableBody = document.getElementById('sellerOrdersTableBody');
        const allOrders = this.getOrders();

        const searchInput = document.getElementById('sellerOrderSearch');
        const query = searchInput ? searchInput.value.toLowerCase().trim() : '';
        const currentFilter = (this.salesStatusFilter || 'all').toLowerCase();

        // Count per status
        const countPending = allOrders.filter(o => (o.status || '').toLowerCase() === 'pending').length;
        const countProcessing = allOrders.filter(o => (o.status || '').toLowerCase() === 'processing').length;
        const countShipped = allOrders.filter(o => (o.status || '').toLowerCase() === 'shipped').length;
        const countDelivered = allOrders.filter(o => (o.status || '').toLowerCase() === 'delivered').length;

        const fAll = document.getElementById('filterCountAll');
        const fPend = document.getElementById('filterCountPending');
        const fProc = document.getElementById('filterCountProcessing');
        const fShip = document.getElementById('filterCountShipped');
        const fDel = document.getElementById('filterCountDelivered');

        if (fAll) fAll.textContent = allOrders.length;
        if (fPend) fPend.textContent = countPending;
        if (fProc) fProc.textContent = countProcessing;
        if (fShip) fShip.textContent = countShipped;
        if (fDel) fDel.textContent = countDelivered;

        let filteredOrders = allOrders;
        if (currentFilter !== 'all') {
            filteredOrders = filteredOrders.filter(o => (o.status || '').toLowerCase() === currentFilter);
        }

        if (query) {
            filteredOrders = filteredOrders.filter(o =>
                String(o.id || '').toLowerCase().includes(query) ||
                String(o.customer || '').toLowerCase().includes(query) ||
                String(o.phone || '').toLowerCase().includes(query) ||
                String(o.city || '').toLowerCase().includes(query) ||
                String(o.state || '').toLowerCase().includes(query) ||
                String(o.pincode || '').toLowerCase().includes(query) ||
                String(o.shipping_address || '').toLowerCase().includes(query) ||
                String(o.full_address || '').toLowerCase().includes(query) ||
                String(o.awb_code || '').toLowerCase().includes(query) ||
                String(o.courier_name || '').toLowerCase().includes(query) ||
                String(o.items || '').toLowerCase().includes(query) ||
                String(o.db_id || '').includes(query)
            );
        }

        const totalSales = allOrders.reduce((acc, o) => acc + (o.total || 0), 0);
        const elRev = document.getElementById('salesTotalRevenue');
        const elOrders = document.getElementById('salesTotalOrders');
        const elDelivered = document.getElementById('salesDeliveredOrders');

        if (elRev) elRev.textContent = `₹${totalSales.toLocaleString('en-IN')}`;
        if (elOrders) elOrders.textContent = allOrders.length;
        if (elDelivered) elDelivered.textContent = countDelivered;

        const resultCount = document.getElementById('sellerOrdersResultCount');
        if (resultCount) {
            resultCount.textContent = `Showing ${filteredOrders.length} of ${allOrders.length} customer orders`;
        }

        // Render Cards View
        if (cardsContainer) {
            if (filteredOrders.length === 0) {
                cardsContainer.innerHTML = `
                    <div class="card border-0 shadow-sm p-5 text-center text-muted" style="border-radius: 12px; background: #fff;">
                        <i class="bi bi-inbox fs-1 d-block mb-3 text-secondary opacity-75"></i>
                        <h5 class="fw-bold text-dark">No orders found</h5>
                        <p class="small text-muted mb-0">Try clearing your search query or selecting a different status filter.</p>
                    </div>
                `;
            } else {
                let cardsHtml = '';
                filteredOrders.forEach(o => {
                    cardsHtml += this.buildOrderCardHtml(o, false);
                });
                cardsContainer.innerHTML = cardsHtml;
            }
        }

        // Render Table View (for toggle)
        if (tableBody) {
            if (filteredOrders.length === 0) {
                tableBody.innerHTML = `<tr><td colspan="8" class="text-center py-5 text-muted">No orders found matching your search.</td></tr>`;
            } else {
                let tableHtml = '';
                filteredOrders.forEach(o => {
                    const streetAddr = o.shipping_address || '';
                    const locationPart = [o.city, o.state, o.pincode].filter(Boolean).join(', ');
                    const fullAddressText = [streetAddr, locationPart].filter(Boolean).join(', ') || o.full_address || `${o.city || ''}`;
                    const escapedAddressForCopy = fullAddressText.replace(/"/g, '&quot;').replace(/'/g, "\\'");

                    tableHtml += `
                        <tr>
                            <td class="fw-bold">
                                <span class="text-primary font-monospace">${o.id}</span>
                                <div class="small text-muted">ID #${o.db_id || o.id}</div>
                            </td>
                            <td>
                                <div class="fw-bold text-dark mb-1 d-flex align-items-center gap-1">
                                    <i class="bi bi-person-fill text-secondary"></i>
                                    <span>${o.customer || 'Customer'}</span>
                                </div>
                                <div class="p-2 rounded border bg-light shadow-sm" style="font-size: 0.8rem; line-height: 1.35; max-width: 260px;">
                                    <div class="d-flex justify-content-between align-items-center mb-1 pb-1 border-bottom">
                                        <span class="text-uppercase fw-bold text-danger" style="font-size: 0.68rem; letter-spacing: 0.5px;">
                                            <i class="bi bi-geo-alt-fill me-1"></i>Delivery Address
                                        </span>
                                        <button type="button" class="btn btn-link p-0 text-muted" style="font-size: 0.72rem; text-decoration: none;" onclick="navigator.clipboard.writeText('${escapedAddressForCopy}').then(() => window.ZyraApp && window.ZyraApp.showToast('Address copied!', 'info'))" title="Copy Delivery Address">
                                            <i class="bi bi-clipboard"></i> Copy
                                        </button>
                                    </div>
                                    <div class="text-dark fw-medium mb-1">
                                        ${streetAddr ? streetAddr : (o.full_address || '<span class="text-muted fst-italic">Street address not specified</span>')}
                                    </div>
                                    <div class="text-muted small">
                                        ${locationPart || 'Location details pending'}
                                    </div>
                                    ${o.phone ? `<div class="mt-1 pt-1 border-top text-dark fw-bold small"><i class="bi bi-telephone-fill me-1 text-success"></i>${o.phone}</div>` : ''}
                                </div>
                            </td>
                            <td class="small" style="max-width: 200px;">${o.items}</td>
                            <td class="fw-bold">₹${(o.total || 0).toLocaleString('en-IN')}</td>
                            <td><small class="text-muted">${o.payment}</small></td>
                            <td><small class="text-muted">${o.date}</small></td>
                            <td>
                                <select class="form-select form-select-sm" onchange="ZyraSeller.updateOrderStatus('${o.id}', this.value)">
                                    <option value="Pending" ${o.status === 'Pending' ? 'selected' : ''}>Pending</option>
                                    <option value="Processing" ${o.status === 'Processing' ? 'selected' : ''}>Processing</option>
                                    <option value="Shipped" ${o.status === 'Shipped' ? 'selected' : ''}>Shipped</option>
                                    <option value="Delivered" ${o.status === 'Delivered' ? 'selected' : ''}>Delivered</option>
                                </select>
                            </td>
                            <td style="min-width: 220px;">
                                <div class="d-flex flex-column gap-1">
                                    <input type="text" class="form-control form-control-sm trk-awb-${o.db_id || o.id}" placeholder="AWB number" value="${o.awb_code || ''}">
                                    <input type="text" class="form-control form-control-sm trk-courier-${o.db_id || o.id}" placeholder="Courier name" value="${o.courier_name || ''}">
                                    <button type="button" class="btn btn-sm btn-outline-dark" onclick="ZyraSeller.updateOrderTracking('${o.db_id || o.id}', '${o.id}')">
                                        <i class="bi bi-truck me-1"></i> Save AWB
                                    </button>
                                </div>
                            </td>
                        </tr>
                    `;
                });
                tableBody.innerHTML = tableHtml;
            }
        }
    },

    renderNewOrdersPage() {
        const cardsContainer = document.getElementById('sellerNewOrdersCardsContainer');
        const tableBody = document.getElementById('sellerNewOrdersTableBody');

        // Check URL if stage is not set yet
        const urlParams = new URLSearchParams(window.location.search);
        const stageParam = urlParams.get('stage');
        if (stageParam && ['pending', 'processing', 'shipped', 'all'].includes(stageParam.toLowerCase())) {
            this.currentOrderStage = stageParam.toLowerCase();
        } else if (!this.currentOrderStage) {
            this.currentOrderStage = 'pending';
        }

        // Sync stage pills active class
        document.querySelectorAll('#sellerOrderStagePills .nav-link').forEach(btn => {
            const btnStage = btn.getAttribute('data-stage');
            btn.classList.toggle('active', btnStage === this.currentOrderStage);
        });

        // Sync sidebar active class
        const sideNew = document.getElementById('sideNavNewOrders');
        const sidePack = document.getElementById('sideNavPacking');
        const sideDisp = document.getElementById('sideNavDispatched');
        if (sideNew) sideNew.classList.toggle('active', this.currentOrderStage === 'pending');
        if (sidePack) sidePack.classList.toggle('active', this.currentOrderStage === 'processing');
        if (sideDisp) sideDisp.classList.toggle('active', this.currentOrderStage === 'shipped');

        const allOrders = this.getOrders();

        const pendingOrders = allOrders.filter(o => (o.status || '').toLowerCase() === 'pending');
        const processingOrders = allOrders.filter(o => (o.status || '').toLowerCase() === 'processing');
        const shippedOrders = allOrders.filter(o => (o.status || '').toLowerCase() === 'shipped');

        // Update Stat Cards and Pill Badges
        const elPendingCount = document.getElementById('newOrdersCount');
        const elPackingCount = document.getElementById('packingOrdersCount');
        const elDispatchedCount = document.getElementById('dispatchedOrdersCount');

        const badgePending = document.getElementById('stageBadgePending');
        const badgeProcessing = document.getElementById('stageBadgeProcessing');
        const badgeShipped = document.getElementById('stageBadgeShipped');
        const badgeAll = document.getElementById('stageBadgeAll');

        if (elPendingCount) elPendingCount.textContent = pendingOrders.length;
        if (elPackingCount) elPackingCount.textContent = processingOrders.length;
        if (elDispatchedCount) elDispatchedCount.textContent = shippedOrders.length;

        if (badgePending) badgePending.textContent = pendingOrders.length;
        if (badgeProcessing) badgeProcessing.textContent = processingOrders.length;
        if (badgeShipped) badgeShipped.textContent = shippedOrders.length;
        if (badgeAll) badgeAll.textContent = allOrders.length;

        this.syncSidebarBadges();

        // Filter orders based on active stage
        let filteredOrders = allOrders;
        if (this.currentOrderStage === 'pending') {
            filteredOrders = pendingOrders;
        } else if (this.currentOrderStage === 'processing') {
            filteredOrders = processingOrders;
        } else if (this.currentOrderStage === 'shipped') {
            filteredOrders = shippedOrders;
        }

        // Update card headings according to stage
        const cardHeading = document.getElementById('orderQueueCardHeading');
        const cardSub = document.getElementById('orderQueueCardSub');
        if (cardHeading && cardSub) {
            if (this.currentOrderStage === 'pending') {
                cardHeading.innerHTML = '<i class="bi bi-inbox me-1 text-danger"></i>New Orders';
                cardSub.textContent = 'Verify customer delivery address, then click "Start Packing" to initiate preparation.';
            } else if (this.currentOrderStage === 'processing') {
                cardHeading.innerHTML = '<i class="bi bi-box-seam me-1 text-warning"></i>In Packing';
                cardSub.textContent = 'Orders currently being prepared. Click "Packed & Dispatch" when ready for courier.';
            } else if (this.currentOrderStage === 'shipped') {
                cardHeading.innerHTML = '<i class="bi bi-truck me-1 text-success"></i>Dispatched';
                cardSub.textContent = 'Shipments dispatched with couriers. Track AWB or mark delivered.';
            } else {
                cardHeading.innerHTML = '<i class="bi bi-layers me-1 text-primary"></i>All Orders';
                cardSub.textContent = 'Complete view of all active customer orders across every fulfillment stage.';
            }
        }

        // Apply Search Filter
        const searchInput = document.getElementById('newOrderSearch');
        const query = searchInput ? searchInput.value.toLowerCase().trim() : '';
        if (query) {
            filteredOrders = filteredOrders.filter(o =>
                String(o.id || '').toLowerCase().includes(query) ||
                String(o.customer || '').toLowerCase().includes(query) ||
                String(o.phone || '').toLowerCase().includes(query) ||
                String(o.city || '').toLowerCase().includes(query) ||
                String(o.state || '').toLowerCase().includes(query) ||
                String(o.pincode || '').toLowerCase().includes(query) ||
                String(o.shipping_address || '').toLowerCase().includes(query) ||
                String(o.full_address || '').toLowerCase().includes(query) ||
                String(o.items || '').toLowerCase().includes(query) ||
                String(o.db_id || '').includes(query)
            );
        }

        const resultCount = document.getElementById('newOrdersResultCount');
        if (resultCount) {
            resultCount.textContent = `Showing ${filteredOrders.length} order${filteredOrders.length === 1 ? '' : 's'}`;
        }

        // Render Cards View
        if (cardsContainer) {
            if (filteredOrders.length === 0) {
                let emptyMsg = 'No new orders right now. New orders will appear here as soon as customers place them.';
                let emptyIcon = 'bi-inbox';
                if (this.currentOrderStage === 'processing') {
                    emptyMsg = 'No orders currently in packing. Click "Start Packing" on any new order to move it here.';
                    emptyIcon = 'bi-box-seam';
                } else if (this.currentOrderStage === 'shipped') {
                    emptyMsg = 'No dispatched orders at the moment. Packed orders will appear here once dispatched.';
                    emptyIcon = 'bi-truck';
                }

                cardsContainer.innerHTML = `
                    <div class="card border-0 shadow-sm p-5 text-center text-muted" style="border-radius: 12px; background: #fff;">
                        <i class="bi ${emptyIcon} fs-1 d-block mb-3 text-secondary opacity-75"></i>
                        <h5 class="fw-bold text-dark">${emptyMsg}</h5>
                        ${this.currentOrderStage !== 'pending' ? `<button type="button" class="btn btn-sm btn-outline-danger mt-3 mx-auto" style="width: fit-content;" onclick="ZyraSeller.setOrderStage('pending')"><i class="bi bi-arrow-left me-1"></i> Check New Orders</button>` : ''}
                    </div>
                `;
            } else {
                let cardsHtml = '';
                filteredOrders.forEach(o => {
                    cardsHtml += this.buildOrderCardHtml(o, true);
                });
                cardsContainer.innerHTML = cardsHtml;
            }
        }

        // Render Table View (for toggle)
        if (tableBody) {
            if (filteredOrders.length === 0) {
                tableBody.innerHTML = `<tr><td colspan="8" class="text-center py-5 text-muted">No orders found matching your search.</td></tr>`;
            } else {
                let tableHtml = '';
                filteredOrders.forEach(o => {
                    const rawStatus = (o.status || 'Pending');
                    const statusLower = rawStatus.toLowerCase();

                    let statusBadge = '<span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1"><i class="bi bi-inbox me-1"></i>New Order</span>';
                    if (statusLower === 'processing') {
                        statusBadge = '<span class="badge bg-warning-subtle text-dark border border-warning-subtle px-2 py-1"><i class="bi bi-box-seam me-1"></i>In Packing</span>';
                    } else if (statusLower === 'shipped') {
                        statusBadge = '<span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1"><i class="bi bi-truck me-1"></i>Dispatched</span>';
                    } else if (statusLower === 'delivered') {
                        statusBadge = '<span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1"><i class="bi bi-check-circle me-1"></i>Delivered</span>';
                    }

                    const streetAddr = o.shipping_address || '';
                    const locationPart = [o.city, o.state, o.pincode].filter(Boolean).join(', ');
                    const fullAddressText = [streetAddr, locationPart].filter(Boolean).join(', ') || o.full_address || `${o.city || ''}`;
                    const escapedAddressForCopy = fullAddressText.replace(/"/g, '&quot;').replace(/'/g, "\\'");

                    let actionsHtml = '';
                    if (statusLower === 'pending') {
                        actionsHtml = `
                            <button type="button" class="btn btn-sm btn-warning text-dark fw-bold me-1" onclick="ZyraSeller.startPackingOrder('${o.id}')">
                                <i class="bi bi-box-seam"></i> Start Packing
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-success" onclick="ZyraSeller.quickDispatchPrompt('${o.db_id || o.id}', '${o.id}')">
                                <i class="bi bi-truck"></i> Dispatch
                            </button>
                        `;
                    } else if (statusLower === 'processing') {
                        actionsHtml = `
                            <button type="button" class="btn btn-sm btn-success fw-bold me-1" onclick="ZyraSeller.quickDispatchPrompt('${o.db_id || o.id}', '${o.id}')">
                                <i class="bi bi-truck"></i> Packed &amp; Dispatch
                            </button>
                        `;
                    } else {
                        actionsHtml = `
                            <button type="button" class="btn btn-sm btn-outline-success" onclick="ZyraSeller.updateOrderStatus('${o.id}', 'Delivered')">
                                <i class="bi bi-check2-circle"></i> Mark Delivered
                            </button>
                        `;
                    }

                    tableHtml += `
                        <tr>
                            <td class="fw-bold font-monospace text-primary">${o.id}</td>
                            <td>
                                <div class="fw-bold text-dark">${o.customer || 'Customer'}</div>
                                <small class="text-muted">${fullAddressText}</small>
                                ${o.phone ? `<div><small class="fw-bold text-success">${o.phone}</small></div>` : ''}
                            </td>
                            <td class="small">${o.items}</td>
                            <td class="fw-bold">₹${(o.total || 0).toLocaleString('en-IN')}</td>
                            <td><span class="badge bg-light text-dark border">${o.payment || 'Prepaid'}</span></td>
                            <td>${statusBadge}</td>
                            <td><small class="text-muted">${o.date || ''}</small></td>
                            <td>${actionsHtml}</td>
                        </tr>
                    `;
                });
                tableBody.innerHTML = tableHtml;
            }
        }
    },

    renderAnalyticsPage() {
        const orders = this.getOrders();
        const totalRev = orders.reduce((acc, o) => acc + (o.total || 0), 0);

        const elRev = document.getElementById('analyticsRevenue');
        const elUnits = document.getElementById('analyticsUnitsSold');
        const elAov = document.getElementById('analyticsAov');

        if (elRev) elRev.textContent = `₹${totalRev.toLocaleString('en-IN')}`;

        // Calculate dispatched / delivered units
        const dispatchedOrders = orders.filter(o => o.status === 'Shipped' || o.status === 'Delivered');
        const units = dispatchedOrders.reduce((acc, o) => {
            if (o.items_detail && Array.isArray(o.items_detail) && o.items_detail.length) {
                return acc + o.items_detail.reduce((sum, item) => sum + (item.quantity || 1), 0);
            }
            return acc + 1;
        }, 0);

        if (elUnits) elUnits.textContent = `${units} units`;
        if (elAov) elAov.textContent = `₹${Math.round(totalRev / (orders.length || 1)).toLocaleString('en-IN')}`;

        // Populate Recent 5 Transactions table
        const tbody = document.getElementById('analyticsRecentTransactionsBody');
        if (tbody && orders.length > 0) {
            const last5 = orders.slice(0, 5);
            tbody.innerHTML = last5.map(o => {
                let badgeClass = 'bg-secondary-subtle text-secondary border border-secondary-subtle';
                let label = o.status || 'Pending';

                if (o.status === 'Pending') {
                    badgeClass = 'bg-danger-subtle text-danger border border-danger-subtle';
                } else if (o.status === 'Processing') {
                    badgeClass = 'bg-warning-subtle text-dark border border-warning-subtle';
                    label = 'In Packing';
                } else if (o.status === 'Shipped') {
                    badgeClass = 'bg-info-subtle text-info-emphasis border border-info-subtle';
                    label = 'Dispatched';
                } else if (o.status === 'Delivered') {
                    badgeClass = 'bg-success-subtle text-success border border-success-subtle';
                }

                return `
                    <tr>
                        <td>
                            <a href="/seller/sales?search=${encodeURIComponent(o.id)}" class="text-decoration-none">
                                <span class="badge bg-white text-dark border font-monospace fw-bold px-2 py-1 shadow-sm">
                                    #${o.id}
                                </span>
                            </a>
                        </td>
                        <td>
                            <div class="fw-semibold text-dark small">${o.customer || 'Customer'}</div>
                            <div class="text-muted smaller">${o.date || ''}</div>
                        </td>
                        <td>
                            <span class="fw-bold text-dark">₹${(o.total || 0).toLocaleString('en-IN')}</span>
                        </td>
                        <td>
                            <span class="small text-muted text-uppercase">${o.payment || 'COD'}</span>
                        </td>
                        <td>
                            <span class="badge ${badgeClass} rounded-pill px-2 py-1 small">
                                ${label}
                            </span>
                        </td>
                        <td class="text-end pe-3">
                            <a href="/seller/sales?search=${encodeURIComponent(o.id)}" class="btn btn-sm btn-outline-dark py-1 px-2" title="View details in Orders & Sales">
                                <i class="bi bi-arrow-right"></i>
                            </a>
                        </td>
                    </tr>
                `;
            }).join('');
        }
    },

    collectSizeStock() {
        const stockMap = {};
        document.querySelectorAll('.size-checkbox:checked').forEach(cb => {
            const label = cb.closest('label');
            const input = label ? label.querySelector('.size-stock-input') : null;
            if (input) {
                const val = parseInt(input.value, 10);
                stockMap[cb.value] = Number.isFinite(val) && val >= 0 ? val : 0;
            }
        });
        return stockMap;
    },

    bindSizeStockTotal() {
        const stockInput = document.getElementById('productStockInput');
        if (!stockInput) return;
        const update = () => {
            const total = Object.values(this.collectSizeStock()).reduce((a, b) => a + b, 0);
            stockInput.value = total;
        };
        document.querySelectorAll('.size-checkbox, .size-stock-input').forEach(el => {
            el.addEventListener('change', update);
            el.addEventListener('input', update);
        });
        document.querySelectorAll('.size-stock-input').forEach(input => {
            input.addEventListener('input', () => {
                if (input.value === '') return;

                const label = input.closest('label');
                const checkbox = label?.querySelector('.size-checkbox');
                if (checkbox && !checkbox.checked) {
                    checkbox.checked = true;
                    update();
                }
            });
        });
        update();
    },

    bindSubcategoryDropdown(selectedValue = null) {
        const categoryInput = document.getElementById('productCategoryInput');
        const subcategoryInput = document.getElementById('productSubcategoryInput');
        if (!categoryInput || !subcategoryInput) return;

        const map = window.ZyraSubcategoryMap || {};

        const populate = () => {
            const catName = categoryInput.value;
            const subs = map[catName] || [];
            subcategoryInput.innerHTML = '<option value="" selected disabled>' +
                (subs.length ? 'Select Subcategory' : 'No subcategories available') +
                '</option>';
            subs.forEach(s => {
                const opt = document.createElement('option');
                opt.value = s;
                opt.textContent = s;
                subcategoryInput.appendChild(opt);
            });
            if (selectedValue && subs.includes(selectedValue)) {
                subcategoryInput.value = selectedValue;
            }
        };

        categoryInput.addEventListener('change', populate);
        populate();
    },

    initAddProductPage() {
        const form = document.getElementById('sellerAddProductForm');
        if (!form || form.dataset.initialized === 'true') return;
        form.dataset.initialized = 'true';

        // Start with empty slots so the first uploaded image becomes the cover.
        this.currentImages = [];
        this.currentImageFiles = [];
        this.renderImagesGrid();

        // Initialize colors list
        this.selectedColors = ['Pink', 'White'];
        this.renderColorsList();
        this.bindSizeStockTotal();
        this.bindSubcategoryDropdown();

        // Bind image file input
        const fileInput = document.getElementById('productFileInput');
        if (fileInput) {
            fileInput.addEventListener('change', (e) => this.handleFileInput(e));
        }

        // Bind image URL input
        const addUrlBtn = document.getElementById('addImageUrlBtn');
        const urlInput = document.getElementById('productImageUrlInput');
        if (addUrlBtn && urlInput) {
            addUrlBtn.addEventListener('click', () => {
                const val = urlInput.value.trim();
                if (val) {
                    this.addImage(val);
                    urlInput.value = '';
                }
            });
            urlInput.addEventListener('keypress', (e) => {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    addUrlBtn.click();
                }
            });
        }

        // Bind form submission
        form.addEventListener('submit', (e) => {
            e.preventDefault();
            if (!form.checkValidity()) {
                form.reportValidity();
                return;
            }

            if (this.currentImages.length === 0) {
                if (window.ZyraApp) window.ZyraApp.showToast('Please add at least 1 image for the product.', 'danger');
                return;
            }

            const checkedSizes = Array.from(document.querySelectorAll('.size-checkbox:checked')).map(c => c.value);
            const sizeStock = this.collectSizeStock();
            const totalStock = Object.values(sizeStock).reduce((a, b) => a + b, 0);
            const stockInput = document.getElementById('productStockInput');
            if (stockInput && Object.keys(sizeStock).length) stockInput.value = totalStock;

            const data = {
                name: this.inputValue('productNameInput').trim(),
                category: this.inputValue('productCategoryInput'),
                subcategory: this.inputValue('productSubcategoryInput').trim() || '',
                price: this.inputValue('productPriceInput'),
                old_price: this.inputValue('productOldPriceInput'),
                stock_units: this.inputValue('productStockInput'),
                weight: document.getElementById('productWeightInput')?.value || '',
                sku: this.inputValue('productSkuInput').trim(),
                images: [...this.currentImages],
                material: this.inputValue('productMaterialInput').trim(),
                description: this.inputValue('productDescriptionInput').trim(),
                sizes: checkedSizes.length ? checkedSizes : ['S', 'M', 'L'],
                size_stock: sizeStock,
                colors: this.selectedColors.length ? this.selectedColors : ['Pink', 'White'],
                color_codes: this.selectedColors.map(color => this.availableColors.find(c => c.name === color)?.hex || '')
            };

            this.addProduct(data);
        });
    },

    initEditProductPage() {
        const form = document.getElementById('sellerEditProductForm');
        if (!form || form.dataset.initialized === 'true') return;
        form.dataset.initialized = 'true';

        const urlParams = window.location.pathname.split('/');
        const id = urlParams[urlParams.indexOf('products') + 1];

        const product = window.ZYRA_EDIT_PRODUCT || this.getProductById(id);
        if (!product) {
            if (window.ZyraApp) window.ZyraApp.showToast('Product not found', 'danger');
            return;
        }

        // Fill inputs
        const nameInput = document.getElementById('productNameInput');
        const catInput = document.getElementById('productCategoryInput');
        const subInput = document.getElementById('productSubcategoryInput');
        const priceInput = document.getElementById('productPriceInput');
        const oldPriceInput = document.getElementById('productOldPriceInput');
        const stockInput = document.getElementById('productStockInput');
        const skuInput = document.getElementById('productSkuInput');
        const weightInput = document.getElementById('productWeightInput');
        const matInput = document.getElementById('productMaterialInput');
        const descInput = document.getElementById('productDescriptionInput');

        if (nameInput) nameInput.value = product.name;
        if (catInput) catInput.value = product.category;
        this.bindSubcategoryDropdown(product.subcategory);
        if (subInput && product.subcategory && !Array.from(subInput.options).some(o => o.value === product.subcategory)) {
            const opt = document.createElement('option');
            opt.value = product.subcategory;
            opt.textContent = product.subcategory;
            subInput.appendChild(opt);
            subInput.value = product.subcategory;
        }
        if (priceInput) priceInput.value = product.price;
        if (oldPriceInput) oldPriceInput.value = product.old_price || '';
        if (stockInput) stockInput.value = product.stock_units || 0;
        if (skuInput) skuInput.value = product.sku || '';
        if (weightInput) weightInput.value = product.weight || 0.5;
        if (matInput) matInput.value = product.material || '';
        if (descInput) descInput.value = product.description || '';

        // Load images
        this.currentImages = product.images && product.images.length ? [...product.images.slice(0, this.MAX_IMAGES)] : [product.image];
        this.currentImageFiles = this.currentImages.map(() => null);
        this.currentImagePaths = product.image_paths && product.image_paths.length
            ? [...product.image_paths.slice(0, this.MAX_IMAGES)]
            : this.currentImages.map(() => null);
        this.renderImagesGrid();

        // Load colors
        this.selectedColors = product.colors && product.colors.length ? [...product.colors] : ['Pink', 'White'];
        (product.colors || []).forEach((name, index) => {
            const hex = product.color_codes?.[index];
            if (!hex) return;
            const existingColor = this.availableColors.find(c => c.name.toLowerCase() === name.toLowerCase());
            if (existingColor) {
                existingColor.hex = hex;
            } else {
                this.availableColors.push({ name, hex });
            }
        });
        this.renderColorsList();

        // Load sizes + per-size stock
        const productSizes = Array.isArray(product.sizes) ? product.sizes : [];
        const sizeStockPrefill = product.size_stock || {};
        document.querySelectorAll('.size-checkbox').forEach(cb => {
            cb.checked = productSizes.includes(cb.value);
            const label = cb.closest('label');
            const input = label ? label.querySelector('.size-stock-input') : null;
            if (input) {
                input.value = sizeStockPrefill.hasOwnProperty(cb.value) ? sizeStockPrefill[cb.value] : '';
            }
        });
        this.bindSizeStockTotal();

        // Bind file input
        const fileInput = document.getElementById('productFileInput');
        if (fileInput) {
            fileInput.addEventListener('change', (e) => this.handleFileInput(e));
        }

        // Bind image URL input
        const addUrlBtn = document.getElementById('addImageUrlBtn');
        const urlInput = document.getElementById('productImageUrlInput');
        if (addUrlBtn && urlInput) {
            addUrlBtn.addEventListener('click', () => {
                const val = urlInput.value.trim();
                if (val) {
                    this.addImage(val);
                    urlInput.value = '';
                }
            });
        }

        form.addEventListener('submit', (e) => {
            e.preventDefault();
            if (this.currentImages.length === 0) {
                if (window.ZyraApp) window.ZyraApp.showToast('Please add at least 1 image for the product.', 'danger');
                return;
            }

                const checkedSizes = Array.from(document.querySelectorAll('.size-checkbox:checked')).map(c => c.value);
                const sizeStock = this.collectSizeStock();
                const totalStock = Object.values(sizeStock).reduce((a, b) => a + b, 0);
                if (stockInput && Object.keys(sizeStock).length) stockInput.value = totalStock;

                const data = {
                    name: nameInput.value.trim(),
                    category: catInput.value,
                    subcategory: subInput.value.trim(),
                    price: priceInput.value,
                    old_price: oldPriceInput.value,
                    stock_units: stockInput.value,
                    weight: document.getElementById('productWeightInput')?.value || '',
                    sku: skuInput.value.trim(),
                    images: [...this.currentImages],
                    image_paths: [...this.currentImagePaths],
                    material: matInput.value.trim(),
                    description: descInput.value.trim(),
                    sizes: checkedSizes.length ? checkedSizes : product.sizes,
                    size_stock: sizeStock,
                    colors: this.selectedColors.length ? this.selectedColors : product.colors,
                    color_codes: this.selectedColors.map(color => this.availableColors.find(c => c.name === color)?.hex || '')
                };

            this.updateProduct(id, data);
        });
    },

    initSettingsPage() {
        const form = document.getElementById('sellerSettingsForm');
        if (form) {
            form.addEventListener('submit', (e) => {
                e.preventDefault();
                if (window.ZyraApp) {
                    window.ZyraApp.showToast('Store settings and payout preferences updated successfully!', 'success');
                }
            });
        }
    }
};

document.addEventListener('DOMContentLoaded', () => {
    window.ZyraSeller.init();
});
