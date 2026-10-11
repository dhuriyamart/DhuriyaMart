@extends('layouts.admin')

@section('title', 'Promotional Coupons - DhuriyaMart Admin')
@section('page_title', 'Marketing Coupons & Discounts')

@section('content')
<div class="container-fluid px-0">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1 text-dark">Promotional Coupons</h4>
            <p class="text-muted small mb-0">Manage customer discount codes, minimum order rules, and limits</p>
        </div>
        <button type="button" class="btn btn-sm btn-primary rounded-pill px-3 shadow-sm" data-bs-toggle="modal" data-bs-target="#createCouponModal">
            <i class="bi bi-plus-lg me-1"></i> Create Coupon
        </button>
    </div>

    <div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-white">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light small text-uppercase">
                    <tr>
                        <th>Code</th>
                        <th>Description</th>
                        <th>Discount Value</th>
                        <th>Min Order</th>
                        <th>Usage Limit</th>
                        <th>Redeemed</th>
                        <th>Active</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($coupons as $coupon)
                        <tr>
                            <td class="font-monospace fw-bold text-dark fs-6">{{ $coupon->code }}</td>
                            <td class="small">{{ $coupon->description }}</td>
                            <td>
                                <span class="badge bg-primary rounded-pill">{{ $coupon->formatted_discount_description }}</span>
                            </td>
                            <td>{{ \App\Support\Money::format($coupon->min_order_value_paise) }}</td>
                            <td>{{ $coupon->usage_limit ?? 'Unlimited' }}</td>
                            <td><strong>{{ $coupon->usage_count }}</strong> times</td>
                            <td>
                                <form action="{{ route('admin.coupons.toggle', $coupon->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-sm {{ $coupon->is_active ? 'btn-success' : 'btn-secondary' }} rounded-pill p-1 px-3" style="font-size: 0.75rem;">
                                        {{ $coupon->is_active ? 'Active' : 'Disabled' }}
                                    </button>
                                </form>
                            </td>
                            <td class="text-end">
                                <form action="{{ route('admin.coupons.destroy', $coupon->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this coupon?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-link text-danger p-0" title="Delete">
                                        <i class="bi bi-trash3"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-4 text-muted">No coupons configured yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Create Coupon Modal -->
<div class="modal fade" id="createCouponModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form action="{{ route('admin.coupons.store') }}" method="POST">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Create Coupon Code</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Coupon Code *</label>
                        <input type="text" name="code" class="form-control form-control-sm text-uppercase font-monospace" placeholder="e.g. DIWALI30" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Description</label>
                        <input type="text" name="description" class="form-control form-control-sm" placeholder="e.g. 30% off for festive shopping">
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label small fw-semibold">Discount Type *</label>
                            <select name="discount_type" class="form-select form-select-sm" required>
                                <option value="percentage">Percentage (%)</option>
                                <option value="fixed">Fixed Amount (₹)</option>
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-semibold">Discount Value *</label>
                            <input type="number" step="0.01" name="discount_value" class="form-control form-control-sm" placeholder="e.g. 20 or 150.00" required>
                        </div>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label small fw-semibold">Min Order Value (₹)</label>
                            <input type="number" step="0.01" name="min_order_value" class="form-control form-control-sm" placeholder="e.g. 499.00">
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-semibold">Max Discount Cap (₹)</label>
                            <input type="number" step="0.01" name="max_discount" class="form-control form-control-sm" placeholder="e.g. 500.00">
                        </div>
                    </div>
                    <div class="row g-2">
                        <div class="col-6">
                            <label class="form-label small fw-semibold">Max Redemptions</label>
                            <input type="number" name="usage_limit" class="form-control form-control-sm" placeholder="e.g. 500">
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-semibold">Expiry Date</label>
                            <input type="date" name="expires_at" class="form-control form-control-sm">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-sm btn-primary rounded-pill px-4">Create Coupon</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

