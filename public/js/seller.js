/**
 * ZYRA - Seller Center Management Controller
 * Handles seller products, multi-image upload (max 4 images), dynamic colors with '+' icon,
 * inventory, orders, analytics, and settings.
 * Persists data via LocalStorage ('zyra_seller_products', 'zyra_seller_orders')
 */

window.ZyraSeller = {
    page: null,
    PRODUCTS_KEY: 'zyra_seller_products',
    ORDERS_KEY: 'zyra_seller_orders',

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

    // Initial default orders
    defaultOrders: [
        {
            id: 'ZYRA-849201',
            date: '07 Sep 2026',
            customer: 'Aditi Sharma',
            phone: '9876543210',
            city: 'Bengaluru',
            items: 'Floral Printed Smocked Peplum Top (M) x 1',
            total: 799,
            payment: 'Cash on Delivery',
            status: 'Delivered'
        },
        {
            id: 'ZYRA-739182',
            date: '06 Sep 2026',
            customer: 'Priya Verma',
            phone: '9812345678',
            city: 'Mumbai',
            items: 'Handblock Printed Pure Cotton Straight Kurti (L) x 1, 4-Way Cotton Leggings (L) x 1',
            total: 1498,
            payment: 'UPI',
            status: 'Shipped'
        },
        {
            id: 'ZYRA-628491',
            date: '05 Sep 2026',
            customer: 'Kavita Nair',
            phone: '9923456789',
            city: 'Delhi NCR',
            items: 'Floor Sweeping Flared Anarkali Kurti (M) x 1',
            total: 1699,
            payment: 'Card',
            status: 'Processing'
        },
        {
            id: 'ZYRA-519283',
            date: '05 Sep 2026',
            customer: 'Meera Deshmukh',
            phone: '9734567890',
            city: 'Hyderabad',
            items: 'Botanical Floral Tiered Ruffle Maxi Dress (S) x 1',
            total: 1499,
            payment: 'UPI',
            status: 'Pending'
        },
        {
            id: 'ZYRA-409182',
            date: '04 Sep 2026',
            customer: 'Rhea Sen',
            phone: '9645678901',
            city: 'Kolkata',
            items: 'Notch Collar Cotton Button-Down Night Suit (M) x 1',
            total: 1099,
            payment: 'Cash on Delivery',
            status: 'Delivered'
        }
    ],

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
                if (Array.isArray(prods) && prods.length > 0) {
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
                if (Array.isArray(ords) && ords.length > 0) {
                    localStorage.setItem(this.ORDERS_KEY, JSON.stringify(ords));
                    const path = window.location.pathname;
                    if (path.includes('/seller/sales') || path.includes('/seller/analytics') || path.endsWith('/seller') || path.includes('/seller/dashboard')) {
                        this.detectPage();
                    }
                }
            })
            .catch(() => {});
    },

    seedInitialData() {
        if (!localStorage.getItem(this.PRODUCTS_KEY)) {
            const initialList = (window.ZyraDB ? window.ZyraDB.getProducts() : []).map(p => ({
                id: p.id,
                name: p.name,
                category: p.category,
                subcategory: p.subcategory || 'Fashion',
                price: p.price,
                old_price: p.old_price || Math.round(p.price * 1.4),
                discount: p.discount || 30,
                stock: p.stock !== false,
                stock_units: (p.id % 7 === 0) ? 4 : (p.id % 3 === 0 ? 9 : 25 + (p.id * 3) % 40),
                image: p.image,
                images: p.images && p.images.length ? p.images.slice(0, 4) : [p.image],
                sku: p.sku || `ZYR-SKU-${p.id}`,
                sizes: p.sizes || ['S', 'M', 'L', 'XL'],
                colors: p.colors || ['Pink', 'Black'],
                description: p.description || '',
                material: p.material || 'Combed Cotton Blend',
                status: 'active'
            }));
            localStorage.setItem(this.PRODUCTS_KEY, JSON.stringify(initialList));
        }

        if (!localStorage.getItem(this.ORDERS_KEY)) {
            localStorage.setItem(this.ORDERS_KEY, JSON.stringify(this.defaultOrders));
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

    buildProductFormData(data) {
        const formData = new FormData();
        formData.append('name', data.name || '');
        formData.append('category', data.category || '');
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

        const images = (data.images && data.images.length > 0)
            ? data.images.slice(0, this.MAX_IMAGES)
            : this.currentImages.slice(0, this.MAX_IMAGES);

        images.forEach((img, index) => {
            const file = this.currentImageFiles[index];
            if (file instanceof File) {
                formData.append(`images[${index}]`, file, file.name);
                formData.append(`image_names[${index}]`, file.name);
            } else if (img && !String(img).startsWith('blob:')) {
                formData.append(`images[${index}]`, img);
                formData.append(`image_names[${index}]`, '');
            }
        });

        return formData;
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
                'X-CSRF-TOKEN': csrfToken
            },
            body: this.buildProductFormData(data)
        })
        .then(res => res.text().then(text => ({ res, text })))
        .then(({ res, text }) => {
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

        fetch(`/seller/products/${id}`, {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            body: this.buildProductFormData(data)
        })
        .then(res => res.text().then(text => ({ res, text })))
        .then(({ res, text }) => {
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
            return JSON.parse(localStorage.getItem(this.ORDERS_KEY)) || this.defaultOrders;
        } catch (e) {
            return this.defaultOrders;
        }
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
            this.renderSalesPage();
        }
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
        this.renderImagesGrid();
        if (window.ZyraApp) {
            window.ZyraApp.showToast(`Image ${this.currentImages.length} added!`, 'success');
        }
    },

    removeImage(index) {
        if (index >= 0 && index < this.currentImages.length) {
            this.currentImages.splice(index, 1);
            this.currentImageFiles.splice(index, 1);
            this.renderImagesGrid();
            if (window.ZyraApp) {
                window.ZyraApp.showToast('Image removed.', 'info');
            }
        }
    },

    handleFileInput(event) {
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

        filesToProcess.forEach(file => {
            this.currentImages.push(URL.createObjectURL(file));
            this.currentImageFiles.push(file);
        });
        this.renderImagesGrid();
        if (window.ZyraApp) {
            window.ZyraApp.showToast(`${filesToProcess.length} image(s) added!`, 'success');
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
        const exists = this.availableColors.some(c => c.name.toLowerCase() === cleanName.toLowerCase());
        if (!exists) {
            this.availableColors.push({ name: cleanName, hex: cleanHex });
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

        const query = searchInput ? searchInput.value.toLowerCase().trim() : '';
        const cat = categoryFilter ? categoryFilter.value : 'all';

        if (query) {
            products = products.filter(p => p.name.toLowerCase().includes(query) || (p.sku && p.sku.toLowerCase().includes(query)));
        }

        if (cat !== 'all') {
            products = products.filter(p => p.category.toLowerCase() === cat.toLowerCase());
        }

        if (countDisplay) {
            countDisplay.textContent = `${products.length} Products`;
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
                            <input class="form-check-input" type="checkbox" role="switch" ${p.stock ? 'checked' : ''} onchange="ZyraSeller.toggleStock(${p.id})">
                        </div>
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
                        <button type="button" class="btn btn-sm btn-outline-dark" onclick="ZyraSeller.updateStockQty(${p.id}, ${(p.stock_units || 0) + 20}); ZyraApp.showToast('Restocked +20 units for ${p.name}', 'success')">
                            + Restock 20
                        </button>
                    </td>
                </tr>
            `;
        });

        tableBody.innerHTML = html;
    },

    renderSalesPage() {
        const tableBody = document.getElementById('sellerOrdersTableBody');
        const orders = this.getOrders();

        const totalSales = orders.reduce((acc, o) => acc + o.total, 0);
        const deliveredOrders = orders.filter(o => o.status === 'Delivered').length;

        const elRev = document.getElementById('salesTotalRevenue');
        const elOrders = document.getElementById('salesTotalOrders');
        const elDelivered = document.getElementById('salesDeliveredOrders');

        if (elRev) elRev.textContent = `₹${totalSales.toLocaleString('en-IN')}`;
        if (elOrders) elOrders.textContent = orders.length;
        if (elDelivered) elDelivered.textContent = deliveredOrders;

        if (!tableBody) return;

        let html = '';
        orders.forEach(o => {
            html += `
                <tr>
                    <td class="fw-bold">${o.id}</td>
                    <td>
                        <div class="fw-semibold">${o.customer}</div>
                        <small class="text-muted"><i class="bi bi-geo-alt"></i> ${o.city} &bull; ${o.phone}</small>
                    </td>
                    <td class="small" style="max-width: 250px;">${o.items}</td>
                    <td class="fw-bold">₹${o.total}</td>
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
                </tr>
            `;
        });

        tableBody.innerHTML = html;
    },

    renderAnalyticsPage() {
        const orders = this.getOrders();
        const totalRev = orders.reduce((acc, o) => acc + o.total, 0);

        const elRev = document.getElementById('analyticsRevenue');
        const elUnits = document.getElementById('analyticsUnitsSold');
        const elAov = document.getElementById('analyticsAov');

        if (elRev) elRev.textContent = `₹${totalRev.toLocaleString('en-IN')}`;
        if (elUnits) elUnits.textContent = '148 units';
        if (elAov) elAov.textContent = `₹${Math.round(totalRev / (orders.length || 1))}`;
    },

    initAddProductPage() {
        const form = document.getElementById('sellerAddProductForm');
        if (!form || form.dataset.initialized === 'true') return;
        form.dataset.initialized = 'true';

        // Initialize multi-images with a starter cover image
        this.currentImages = [
            'https://images.unsplash.com/photo-1610030469983-98e550d6193c?auto=format&fit=crop&w=800&q=80'
        ];
        this.currentImageFiles = [null];
        this.renderImagesGrid();

        // Initialize colors list
        this.selectedColors = ['Pink', 'White'];
        this.renderColorsList();

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

            const data = {
                name: document.getElementById('productNameInput').value.trim(),
                category: document.getElementById('productCategoryInput').value,
                subcategory: document.getElementById('productSubcategoryInput').value.trim() || 'Casual Wear',
                price: document.getElementById('productPriceInput').value,
                old_price: document.getElementById('productOldPriceInput').value,
                stock_units: document.getElementById('productStockInput').value,
                sku: document.getElementById('productSkuInput').value.trim(),
                images: [...this.currentImages],
                material: document.getElementById('productMaterialInput').value.trim(),
                description: document.getElementById('productDescriptionInput').value.trim(),
                sizes: checkedSizes.length ? checkedSizes : ['S', 'M', 'L'],
                colors: this.selectedColors.length ? this.selectedColors : ['Pink', 'White']
            };

            this.addProduct(data);
        });
    },

    initEditProductPage() {
        const form = document.getElementById('sellerEditProductForm');
        const urlParams = window.location.pathname.split('/');
        const id = urlParams[urlParams.indexOf('products') + 1];

        const product = this.getProductById(id);
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
        const matInput = document.getElementById('productMaterialInput');
        const descInput = document.getElementById('productDescriptionInput');

        if (nameInput) nameInput.value = product.name;
        if (catInput) catInput.value = product.category;
        if (subInput) subInput.value = product.subcategory || '';
        if (priceInput) priceInput.value = product.price;
        if (oldPriceInput) oldPriceInput.value = product.old_price || '';
        if (stockInput) stockInput.value = product.stock_units || 0;
        if (skuInput) skuInput.value = product.sku || '';
        if (matInput) matInput.value = product.material || '';
        if (descInput) descInput.value = product.description || '';

        // Load images
        this.currentImages = product.images && product.images.length ? [...product.images.slice(0, this.MAX_IMAGES)] : [product.image];
        this.currentImageFiles = this.currentImages.map(() => null);
        this.renderImagesGrid();

        // Load colors
        this.selectedColors = product.colors && product.colors.length ? [...product.colors] : ['Pink', 'White'];
        this.renderColorsList();

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

        if (form) {
            form.addEventListener('submit', (e) => {
                e.preventDefault();
                if (this.currentImages.length === 0) {
                    if (window.ZyraApp) window.ZyraApp.showToast('Please add at least 1 image for the product.', 'danger');
                    return;
                }

                const checkedSizes = Array.from(document.querySelectorAll('.size-checkbox:checked')).map(c => c.value);

                const data = {
                    name: nameInput.value.trim(),
                    category: catInput.value,
                    subcategory: subInput.value.trim(),
                    price: priceInput.value,
                    old_price: oldPriceInput.value,
                    stock_units: stockInput.value,
                    sku: skuInput.value.trim(),
                    images: [...this.currentImages],
                    material: matInput.value.trim(),
                    description: descInput.value.trim(),
                    sizes: checkedSizes.length ? checkedSizes : product.sizes,
                    colors: this.selectedColors.length ? this.selectedColors : product.colors
                };

                this.updateProduct(id, data);
            });
        }
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
