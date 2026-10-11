@extends('layouts.seller')

@section('title', 'Manage Products - ' . $shop->name)
@section('page_title', 'My Product Catalogue')

@section('content')
<div class="container-fluid px-0">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1 text-dark">Products & Inventory</h4>
            <p class="text-muted small mb-0">List and manage product variants and pricing</p>
        </div>
        <a href="{{ route('seller.products.create') }}" class="btn btn-dm-primary btn-sm rounded-pill px-3 shadow-sm">
            <i class="bi bi-plus-lg me-1"></i> Add New Product
        </a>
    </div>

    <div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-white">
        @if($products->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light small text-uppercase">
                        <tr>
                            <th>Product</th>
                            <th>Category</th>
                            <th>Base Price</th>
                            <th>Total Stock</th>
                            <th>Moderation</th>
                            <th>Active</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($products as $product)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="rounded-2 overflow-hidden bg-light" style="width: 48px; height: 48px; flex-shrink: 0;">
                                            <img src="{{ $product->primary_image_url }}" alt="" class="w-100 h-100 object-fit-cover">
                                        </div>
                                        <div>
                                            <div class="fw-bold text-dark text-truncate" style="max-width: 250px;">{{ $product->name }}</div>
                                            <small class="text-muted">{{ $product->variants->count() }} Variant(s)</small>
                                        </div>
                                    </div>
                                </td>
                                <td class="small">{{ $product->category?->name ?? 'Uncategorized' }}</td>
                                <td class="fw-bold text-dark">{{ $product->formatted_price }}</td>
                                <td>
                                    <span class="badge {{ $product->total_stock <= 5 ? 'bg-danger' : 'bg-success' }}">
                                        {{ $product->total_stock }} Units
                                    </span>
                                </td>
                                <td>
                                    <x-status-badge :status="$product->status" />
                                    @if($product->status === 'rejected' && $product->rejection_reason)
                                        <div class="small text-danger mt-1" style="font-size: 0.75rem;">Reason: {{ $product->rejection_reason }}</div>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge {{ $product->is_active ? 'bg-success' : 'bg-secondary' }}">
                                        {{ $product->is_active ? 'Enabled' : 'Disabled' }}
                                    </span>
                                </td>
                                <td class="text-end">
                                    <a href="{{ route('seller.products.edit', $product->id) }}" class="btn btn-sm btn-outline-secondary rounded-pill px-2" title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <form action="{{ route('seller.products.destroy', $product->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this product permanently?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-link text-danger p-1" title="Delete">
                                            <i class="bi bi-trash3"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="p-3 border-top d-flex justify-content-center">
                {{ $products->links('pagination::bootstrap-5') }}
            </div>
        @else
            <div class="text-center py-5 text-muted">
                <i class="bi bi-tags fs-1 text-muted d-block mb-3"></i>
                <h5>No products listed yet</h5>
                <p class="small text-muted mb-3">Create your first product listing to start selling across India.</p>
                <a href="{{ route('seller.products.create') }}" class="btn btn-sm btn-dm-primary rounded-pill px-4">
                    Add Product Listing
                </a>
            </div>
        @endif
    </div>
</div>
@endsection

