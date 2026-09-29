/* ZYRA anonymous shopping analytics beacon.
 * Reports ONLY explicit size/color selections on product pages
 * (base product views are recorded server-side). No personal data. */
(function () {
    function csrfToken() {
        return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    }

    function productId() {
        const el = document.querySelector('[data-product-id]');
        if (el) {
            const id = parseInt(el.getAttribute('data-product-id'), 10);
            if (id > 0) return id;
        }
        const m = window.location.pathname.match(/\/product\/(\d+)/);
        return m ? parseInt(m[1], 10) : 0;
    }

    let lastSent = 0;

    document.addEventListener('click', (e) => {
        if (!e.target.closest('.pd-size-btn, .pd-color-swatch')) return;

        const pid = productId();
        if (!pid) return;

        const now = Date.now();
        if (now - lastSent < 4000) return; // one beacon max per 4s per page
        lastSent = now;

        // Let the page's own handlers update .active first.
        setTimeout(() => {
            const size = document.querySelector('.pd-size-btn.active')?.getAttribute('data-size') || '';
            const color = document.querySelector('.pd-color-swatch.active')?.getAttribute('data-color') || '';
            if (!size && !color) return;

            fetch('/track', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken()
                },
                body: JSON.stringify({ action: 'product_viewed', product_id: pid, size, color })
            }).catch(() => {});
        }, 0);
    });
})();