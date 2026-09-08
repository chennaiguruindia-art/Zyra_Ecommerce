@extends('layouts.app')

@section('title', 'Global Settings | ZYRA')

@push('styles')
<style>
    .gs-table { vertical-align: middle; }
    .gs-table thead th {
        text-transform: uppercase;
        font-size: .75rem;
        letter-spacing: .04em;
        color: #8a8a8a;
        border-bottom: 2px solid #000;
    }
    .gs-table td { border-color: #f0f0f0; }
    .gs-table img.gs-img {
        width: 56px; height: 56px; object-fit: cover; border-radius: 8px;
        background: #f6f6f6;
    }
    .gs-switch { display: inline-flex; align-items: center; gap: .5rem; }
</style>
@endpush

@section('content')
<div class="container py-5">
    <div class="d-flex flex-wrap align-items-center justify-content-between mb-4">
        <div>
            <h1 class="fw-bold mb-1">Global Settings</h1>
            <p class="text-muted mb-0">
                Enable a product's toggle to show it in the corresponding home page section (Best Sellers / Trending Collection). Toggles are saved to the database.
            </p>
        </div>
        <a href="{{ route('home') }}" class="btn btn-outline-dark btn-sm">View Home Page</a>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table gs-table mb-0">
                    <thead>
                        <tr>
                            <th class="ps-4" style="width:70px;">Image</th>
                            <th>Product Name</th>
                            <th>Category</th>
                            <th class="text-center" style="width:150px;">Best Seller</th>
                            <th class="text-center pe-4" style="width:180px;">Trending Collection</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($products as $product)
                            <tr>
                                <td class="ps-4">
                                    <img src="{{ $product->image_url }}" alt="{{ $product->name }}" class="gs-img">
                                </td>
                                <td>
                                    <div class="fw-semibold">{{ $product->name }}</div>
                                    <small class="text-muted">ID: {{ $product->id }}{{ $product->sku ? ' · SKU: '.$product->sku : '' }}</small>
                                </td>
                                <td>{{ $product->category?->name ?? '-' }}</td>
                                <td class="text-center">
                                    <div class="form-check form-switch gs-switch m-0">
                                        <input class="form-check-input gs-toggle" type="checkbox" role="switch"
                                               data-product-id="{{ $product->id }}" data-field="is_best_seller"
                                               {{ $product->is_best_seller ? 'checked' : '' }}>
                                    </div>
                                </td>
                                <td class="text-center pe-4">
                                    <div class="form-check form-switch gs-switch m-0">
                                        <input class="form-check-input gs-toggle" type="checkbox" role="switch"
                                               data-product-id="{{ $product->id }}" data-field="is_trending"
                                               {{ $product->is_trending ? 'checked' : '' }}>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-5 text-muted">No products found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

        document.querySelectorAll('.gs-toggle').forEach(toggle => {
            toggle.addEventListener('change', () => {
                const productId = toggle.getAttribute('data-product-id');
                const field = toggle.getAttribute('data-field');
                const value = toggle.checked;

                toggle.disabled = true;

                fetch('/global_setting/toggle', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrf
                    },
                    body: JSON.stringify({ product_id: productId, field: field, value: value })
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        if (window.ZyraApp) {
                            window.ZyraApp.showToast(data.product.name + ' updated.', 'success');
                        }
                    } else {
                        toggle.checked = !value;
                        if (window.ZyraApp) {
                            window.ZyraApp.showToast(data.message || 'Update failed.', 'danger');
                        }
                    }
                })
                .catch(() => {
                    toggle.checked = !value;
                    if (window.ZyraApp) {
                        window.ZyraApp.showToast('Could not reach server.', 'danger');
                    }
                })
                .finally(() => {
                    toggle.disabled = false;
                });
            });
        });
    });
</script>
@endpush