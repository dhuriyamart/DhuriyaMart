@extends('layouts.seller')

@section('title', 'Add Product - ' . $shop->name)
@section('page_title', 'Add New Product Listing')

@section('content')
<div class="container-fluid px-0">
    <div class="row justify-content-center">
        <div class="col-lg-9">
            <div class="card border-0 shadow-sm rounded-4 p-4 p-md-5 bg-white">
                <form action="{{ route('seller.products.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    <h5 class="fw-bold text-dark border-bottom pb-2 mb-4">1. General Information</h5>
                    <div class="row g-3 mb-4">
                        <div class="col-md-8">
                            <label class="form-label small fw-semibold">Product Title *</label>
                            <input type="text" name="name" class="form-control" placeholder="e.g. Wireless Noise Cancelling Over-Ear Headphones" value="{{ old('name') }}" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Category *</label>
                            <select name="category_id" class="form-select" required>
                                <option value="">Select Category</option>
                                @foreach($categories as $parent)
                                    <optgroup label="{{ $parent->name }}">
                                        @foreach($parent->children as $child)
                                            <option value="{{ $child->id }}" {{ old('category_id') == $child->id ? 'selected' : '' }}>
                                                {{ $child->name }}
                                            </option>
                                        @endforeach
                                    </optgroup>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-semibold">Short Summary / Tagline *</label>
                            <input type="text" name="short_description" class="form-control" placeholder="Brief 1-2 line product highlight for card preview" value="{{ old('short_description') }}" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-semibold">Full Detailed Description *</label>
                            <textarea name="description" rows="5" class="form-control" placeholder="Describe materials, key features, package contents, and benefits..." required>{{ old('description') }}</textarea>
                        </div>
                    </div>

                    <h5 class="fw-bold text-dark border-bottom pb-2 mb-4">2. Pricing, SKU & Initial Inventory</h5>
                    <div class="row g-3 mb-4">
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Selling Price (₹ INR) *</label>
                            <input type="number" step="0.01" name="base_price" class="form-control" placeholder="e.g. 1499.00" value="{{ old('base_price') }}" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">MRP / Compare Price (₹ INR)</label>
                            <input type="number" step="0.01" name="compare_at_price" class="form-control" placeholder="e.g. 2999.00" value="{{ old('compare_at_price') }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Initial Stock Quantity *</label>
                            <input type="number" name="stock_quantity" class="form-control" placeholder="e.g. 50" value="{{ old('stock_quantity', 10) }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Product Variant Name *</label>
                            <input type="text" name="variant_name" class="form-control" placeholder="e.g. Standard Black / 500g / Large" value="{{ old('variant_name', 'Standard') }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Merchant Stock SKU Code *</label>
                            <input type="text" name="sku" class="form-control text-uppercase font-monospace" placeholder="e.g. PROD-BLK-001" value="{{ old('sku') }}" required>
                        </div>
                    </div>

                    <h5 class="fw-bold text-dark border-bottom pb-2 mb-4">3. Photography & Imagery</h5>
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Upload Image File (JPG, PNG)</label>
                            <input type="file" name="image_file" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Or Image URL</label>
                            <input type="url" name="image_url" class="form-control" placeholder="https://..." value="{{ old('image_url') }}">
                        </div>
                    </div>

                    <div class="d-flex gap-3 pt-3 border-top">
                        <button type="submit" class="btn btn-dm-primary btn-lg rounded-pill px-5 fw-bold shadow-sm">
                            Submit for Admin Review
                        </button>
                        <a href="{{ route('seller.products.index') }}" class="btn btn-light btn-lg rounded-pill px-4">
                            Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

