@extends('layouts.admin')

@section('title', 'Categories Management - DhuriyaMart Admin')
@section('page_title', 'Taxonomy & Marketplace Categories')

@section('content')
<div class="container-fluid px-0">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1 text-dark">Category Hierarchy</h4>
            <p class="text-muted small mb-0">Organize parent departments and nested subcategories</p>
        </div>
        <button type="button" class="btn btn-sm btn-primary rounded-pill px-3 shadow-sm" data-bs-toggle="modal" data-bs-target="#createCategoryModal">
            <i class="bi bi-plus-lg me-1"></i> Add Category
        </button>
    </div>

    <div class="row g-4">
        @foreach($categories as $parent)
            <div class="col-md-6 col-xl-4">
                <div class="card border-0 shadow-sm rounded-4 p-4 bg-white h-100">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <div class="d-flex align-items-center gap-2">
                            <i class="bi {{ $parent->icon ?? 'bi-folder' }} fs-4 text-primary"></i>
                            <h5 class="fw-bold mb-0 text-dark">{{ $parent->name }}</h5>
                        </div>
                        <span class="badge bg-light text-dark border">{{ $parent->products->count() }} Products</span>
                    </div>
                    <p class="small text-muted mb-3">{{ $parent->description ?? 'No description' }}</p>

                    <!-- Subcategories list -->
                    <div class="bg-light rounded-3 p-3 mb-3 flex-grow-1">
                        <h6 class="small fw-bold text-uppercase text-muted mb-2">Subcategories:</h6>
                        <ul class="list-unstyled mb-0 small d-flex flex-column gap-1">
                            @forelse($parent->children as $sub)
                                <li class="d-flex justify-content-between align-items-center py-1">
                                    <span><i class="bi bi-chevron-right text-muted me-1" style="font-size: 0.7rem;"></i>{{ $sub->name }}</span>
                                    <span class="badge bg-white text-muted border">{{ $sub->products->count() }} items</span>
                                </li>
                            @empty
                                <li class="text-muted fst-italic">No subcategories defined</li>
                            @endforelse
                        </ul>
                    </div>

                    <div class="border-top pt-2 d-flex justify-content-end gap-2">
                        <form action="{{ route('admin.categories.destroy', $parent->id) }}" method="POST" onsubmit="return confirm('Delete this parent category?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-link text-danger p-0 text-decoration-none">Delete</button>
                        </form>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>

<!-- Add Category Modal -->
<div class="modal fade" id="createCategoryModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form action="{{ route('admin.categories.store') }}" method="POST">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Create Marketplace Category</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Category Name *</label>
                        <input type="text" name="name" class="form-control" placeholder="e.g. Footwear & Shoes" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Parent Category (Leave blank if top-level department)</label>
                        <select name="parent_id" class="form-select">
                            <option value="">None (Top-Level Category)</option>
                            @foreach($categories as $c)
                                <option value="{{ $c->id }}">{{ $c->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Bootstrap Icon Class (Optional)</label>
                        <input type="text" name="icon" class="form-control" placeholder="bi-laptop, bi-bag, bi-watch">
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Description</label>
                        <textarea name="description" rows="2" class="form-control"></textarea>
                    </div>
                    <div class="row">
                        <div class="col-6">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="is_featured" value="1" id="catFeatured">
                                <label class="form-check-label small" for="catFeatured">Featured on Homepage</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-sm btn-primary rounded-pill px-4">Create Category</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

