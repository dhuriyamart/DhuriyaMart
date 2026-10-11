@extends('layouts.admin')

@section('title', 'Product Moderation - DhuriyaMart Admin')
@section('page_title', 'Product Moderation & Catalogue Management')

@section('content')
<div class="container-fluid px-0">
    <!-- Filter Bar Card -->
    <div class="card border-0 shadow-sm rounded-4 p-3 bg-white mb-4">
        <form method="GET" action="{{ route('admin.products.index') }}" class="row g-2 align-items-center">
            <div class="col-md-3">
                <input type="text" name="q" class="form-control form-control-sm" placeholder="Search product name..." value="{{ request('q') }}">
            </div>
            <div class="col-md-3">
                <select name="status" class="form-select form-select-sm">
                    <option value="">All Moderation Statuses</option>
                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending Review</option>
                    <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Approved</option>
                    <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Rejected</option>
                </select>
            </div>
            <div class="col-md-3">
                <select name="shop_id" class="form-select form-select-sm">
                    <option value="">All Merchant Shops</option>
                    @foreach($shops as $s)
                        <option value="{{ $s->id }}" {{ request('shop_id') == $s->id ? 'selected' : '' }}>{{ $s->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-sm btn-primary rounded-pill px-3 flex-grow-1">Filter</button>
                <a href="{{ route('admin.products.index') }}" class="btn btn-sm btn-outline-secondary rounded-pill px-3">Reset</a>
            </div>
        </form>
    </div>

    <!-- Products Table -->
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-white">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light small text-uppercase">
                    <tr>
                        <th>Product Item</th>
                        <th>Seller / Shop</th>
                        <th>Category</th>
                        <th>Price</th>
                        <th>Stock</th>
                        <th>Featured</th>
                        <th>Status</th>
                        <th class="text-end">Moderation</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($products as $product)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="rounded-2 overflow-hidden bg-light" style="width: 44px; height: 44px; flex-shrink: 0;">
                                        <img src="{{ $product->primary_image_url }}" alt="" class="w-100 h-100 object-fit-cover">
                                    </div>
                                    <div>
                                        <div class="fw-bold text-dark text-truncate" style="max-width: 230px;">{{ $product->name }}</div>
                                        <small class="text-muted">{{ $product->variants->count() }} Variant(s)</small>
                                    </div>
                                </div>
                            </td>
                            <td class="small">
                                <span class="fw-semibold text-primary">{{ $product->shop->name }}</span>
                            </td>
                            <td class="small">{{ $product->category?->name ?? 'N/A' }}</td>
                            <td class="fw-bold text-dark">{{ $product->formatted_price }}</td>
                            <td>
                                <span class="badge {{ $product->total_stock <= 5 ? 'bg-danger' : 'bg-success' }}">
                                    {{ $product->total_stock }} Units
                                </span>
                            </td>
                            <td>
                                <form action="{{ route('admin.products.toggle-featured', $product->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-sm {{ $product->is_featured ? 'btn-warning text-dark' : 'btn-light border' }} rounded-pill p-1 px-2" style="font-size: 0.75rem;">
                                        <i class="bi {{ $product->is_featured ? 'bi-star-fill' : 'bi-star' }} me-1"></i>
                                        {{ $product->is_featured ? 'Featured' : 'Standard' }}
                                    </button>
                                </form>
                            </td>
                            <td>
                                <x-status-badge :status="$product->status" />
                                @if($product->status === 'rejected' && $product->rejection_reason)
                                    <div class="small text-danger mt-1" style="font-size: 0.7rem;">Reason: {{ $product->rejection_reason }}</div>
                                @endif
                            </td>
                            <td class="text-end">
                                <div class="btn-group btn-group-sm">
                                    @if($product->status !== 'approved')
                                        <form action="{{ route('admin.products.moderate', $product->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            <input type="hidden" name="status" value="approved">
                                            <button type="submit" class="btn btn-sm btn-success rounded-start-pill px-2" title="Approve Product">
                                                <i class="bi bi-check-lg"></i> Approve
                                            </button>
                                        </form>
                                    @endif

                                    @if($product->status !== 'rejected')
                                        <button type="button" class="btn btn-sm btn-outline-danger px-2" data-bs-toggle="modal" data-bs-target="#rejectModal_{{ $product->id }}" title="Reject Product">
                                            <i class="bi bi-x-lg"></i> Reject
                                        </button>
                                    @endif

                                    <form action="{{ route('admin.products.destroy', $product->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this product permanently?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-light border text-danger rounded-end-pill px-2">
                                            <i class="bi bi-trash3"></i>
                                        </button>
                                    </form>
                                </div>

                                <!-- Reject Modal -->
                                <div class="modal fade text-start" id="rejectModal_{{ $product->id }}" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <form action="{{ route('admin.products.moderate', $product->id) }}" method="POST">
                                            @csrf
                                            <input type="hidden" name="status" value="rejected">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title fw-bold">Reject Product #{{ $product->id }}</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="mb-3">
                                                        <label class="form-label small fw-semibold">Rejection Feedback for Merchant *</label>
                                                        <textarea name="rejection_reason" rows="3" class="form-control form-control-sm" placeholder="e.g. Unclear images, misleading specs, or prohibited item category" required></textarea>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-sm btn-light" data-bs-dismiss="modal">Cancel</button>
                                                    <button type="submit" class="btn btn-sm btn-danger rounded-pill px-3">Confirm Rejection</button>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-4 text-muted">No products found matching filters.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-3 border-top d-flex justify-content-center">
            {{ $products->links('pagination::bootstrap-5') }}
        </div>
    </div>
</div>
@endsection

