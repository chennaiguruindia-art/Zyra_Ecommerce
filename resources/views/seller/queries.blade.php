@extends('layouts.seller')

@section('title', 'Customer Queries | ZYRA Seller Hub')
@section('meta_description', 'Review and manage customer queries submitted through the ZYRA contact form.')

@section('content')

<div class="seller-page-header">
    <div>
        <h1 class="h3 fw-bold mb-1">Customer Queries</h1>
        <p class="text-muted mb-0">Review queries submitted from the contact page and mark them as resolved.</p>
    </div>
    <div>
        <span class="badge bg-primary-subtle text-primary fs-6">
            <i class="bi bi-envelope me-1"></i>{{ $queries->count() }} Total
        </span>
    </div>
</div>

@if (session('status_updated'))
    <div class="alert alert-success border-0 shadow-sm">
        <i class="bi bi-check-circle-fill me-2"></i>Query status updated.
    </div>
@endif

<div class="card border-0 shadow-sm rounded-4">
    <div class="card-body p-0">
        @if ($queries->isEmpty())
            <div class="text-center py-5">
                <i class="bi bi-inbox fs-1 text-muted"></i>
                <p class="text-muted mb-0 mt-2">No queries yet.</p>
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="px-4 py-3">#</th>
                            <th class="py-3">Order ID</th>
                            <th class="py-3">Name</th>
                            <th class="py-3">Phone</th>
                            <th class="py-3">Email</th>
                            <th class="py-3">Query Status</th>
                            <th class="py-3">Query Subject</th>
                            <th class="py-3">Message</th>
                            <th class="py-3">Received</th>
                            <th class="py-3">Mark</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($queries as $query)
                            <tr class="{{ $query->isNew() ? 'table-warning' : '' }}">
                                <td class="px-4 text-muted">{{ $loop->iteration }}</td>
                                <td>{{ $query->order_id ?: '—' }}</td>
                                <td>{{ $query->name }}</td>
                                <td>{{ $query->phone_number }}</td>
                                <td>{{ $query->email }}</td>
                                <td>
                                    <span class="text-capitalize">{{ $query->query_status }}</span>
                                </td>
                                <td>{{ $query->query_subject }}</td>
                                <td class="text-truncate" style="max-width: 220px;" title="{{ $query->message }}">
                                    {{ $query->message ?: '—' }}
                                </td>
                                <td class="text-nowrap small text-muted">{{ $query->created_at->format('d M Y') }}</td>
                                <td>
                                    <form method="POST" action="{{ route('queries.status', $query->id) }}" class="d-inline">
                                        @csrf
                                        <input type="hidden" name="status" value="{{ $query->isNew() ? 'resolved' : 'new' }}">
                                        <button type="submit" class="btn btn-sm {{ $query->isNew() ? 'btn-success' : 'btn-outline-dark' }}">
                                            {{ $query->isNew() ? 'Resolve' : 'Reopen' }}
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>

@endsection