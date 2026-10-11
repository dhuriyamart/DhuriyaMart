@extends('layouts.admin')

@section('title', 'Sellers Management - DhuriyaMart Admin')
@section('page_title', 'Merchant Shops & Seller KYC Verification')

@section('content')
<div class="container-fluid px-0">
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-white mb-4">
        <!-- Header & Filters -->
        <div class="card-header bg-light py-3 border-bottom d-flex flex-wrap justify-content-between align-items-center gap-2">
            <div class="d-flex gap-2">
                <a href="{{ route('admin.sellers.index') }}" class="btn btn-sm {{ !request('status') ? 'btn-dark' : 'btn-outline-secondary' }} rounded-pill px-3">
                    All Merchants
                </a>
                <a href="{{ route('admin.sellers.index', ['status' => 'pending']) }}" class="btn btn-sm {{ request('status') === 'pending' ? 'btn-warning text-dark fw-bold' : 'btn-outline-secondary' }} rounded-pill px-3">
                    Pending KYC
                </a>
                <a href="{{ route('admin.sellers.index', ['status' => 'approved']) }}" class="btn btn-sm {{ request('status') === 'approved' ? 'btn-success text-white' : 'btn-outline-secondary' }} rounded-pill px-3">
                    Approved
                </a>
                <a href="{{ route('admin.sellers.index', ['status' => 'suspended']) }}" class="btn btn-sm {{ request('status') === 'suspended' ? 'btn-danger text-white' : 'btn-outline-secondary' }} rounded-pill px-3">
                    Suspended
                </a>
            </div>
            <span class="small text-muted">{{ $shops->total() }} merchant(s) registered</span>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light small text-uppercase">
                    <tr>
                        <th>Shop Name</th>
                        <th>Owner / Email</th>
                        <th>PAN / GSTIN</th>
                        <th>Products</th>
                        <th>Commission %</th>
                        <th>Status</th>
                        <th class="text-end">Verification & Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($shops as $shop)
                        <tr>
                            <td>
                                <div class="fw-bold text-dark">{{ $shop->name }}</div>
                                <small class="text-muted">{{ $shop->legal_name ?? $shop->name }}</small>
                            </td>
                            <td>
                                <div>{{ $shop->user->name }}</div>
                                <small class="text-muted">{{ $shop->user->email }}</small>
                            </td>
                            <td>
                                <div class="font-monospace small">PAN: <strong>{{ $shop->profile?->pan_number ?? 'N/A' }}</strong></div>
                                <small class="text-muted font-monospace">GST: {{ $shop->profile?->gstin ?? 'N/A' }}</small>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border">{{ $shop->products->count() }} items</span>
                            </td>
                            <td class="fw-bold text-primary">{{ $shop->commission_rate }}%</td>
                            <td>
                                <x-status-badge :status="$shop->status" />
                            </td>
                            <td class="text-end">
                                <a href="{{ route('admin.sellers.show', $shop->id) }}" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                                    Review & KYC
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-4 text-muted">No seller shops found matching filters.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-3 border-top d-flex justify-content-center">
            {{ $shops->links('pagination::bootstrap-5') }}
        </div>
    </div>
</div>
@endsection

