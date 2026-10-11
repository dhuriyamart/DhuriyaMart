@extends('layouts.seller')

@section('title', 'Edit ' . $product->name . ' - ' . $shop->name)
@section('page_title', 'Edit Product Listing')

@section('content')
<div class="container-fluid px-0">
    <div class="row justify-content-center">
        <div class="col-lg-9">
            <div class="card border-0 shadow-sm rounded-4 p-4 p-md-5 bg-white">
                <form action="{{ route('seller.products.update', $product->id) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <h5 class="fw-bold text-dark border-bottom pb-2 mb-4">Edit Product: {{ $product->name }}</h5>
                    
                    <div class="row g-3 mb-4">
                        <div class="col-md-8">
                            <label class="form-label small fw-semibold">Product Title *</label>
                            <input type="text" name="name" class="form-control" value="{{ old('name', $product->name) }}" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Category *</label>
                            <select name="category_id" class="form-select" required>
                                @foreach($categories as $parent)
                                    <optgroup label="{{ $parent->name }}">
                                        @foreach($parent->children as $child)
                                            <option value="{{ $child->id }}" {{ $product->category_id == $child->id ? 'selected' : '' }}>
                                                {{ $child->name }}
                                            </option>
                                        @endforeach
                                    </optgroup>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Base Price (₹ INR) *</label>
                            <input type="number" step="0.01" name="base_price" class="form-control" value="{{ old('base_price', $product->base_price_paise / 100) }}" required>
                        </div>
                        <div class="col-md-6 d-flex align-items-center mt-4">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="is_active" value="1" id="activeSwitch" {{ $product->is_active ? 'checked' : '' }}>
                                <label class="form-check-label fw-semibold" for="activeSwitch">Product is Active for Purchasing</label>
                            </div>
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-semibold">Short Description *</label>
                            <input type="text" name="short_description" class="form-control" value="{{ old('short_description', $product->short_description) }}" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-semibold">Full Description *</label>
                            <textarea name="description" rows="5" class="form-control" required>{{ old('description', $product->description) }}</textarea>
                        </div>
                    </div>

                    <div class="d-flex gap-3 pt-3 border-top">
                        <button type="submit" class="btn btn-dm-primary rounded-pill px-5 fw-bold shadow-sm">
                            Save Changes
                        </button>
                        <a href="{{ route('seller.products.index') }}" class="btn btn-light rounded-pill px-4">
                            Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

