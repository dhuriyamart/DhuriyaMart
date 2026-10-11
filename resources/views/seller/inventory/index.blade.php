@extends('layouts.seller')

@section('title', 'Inventory Stock - ' . $shop->name)
@section('page_title', 'Stock Management & Audit')

@section('content')
<div class="container-fluid px-0">
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-white mb-4">
        <div class="card-header bg-light py-3 border-bottom">
            <h5 class="fw-bold mb-0 text-dark">Current Stock Levels</h5>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light small text-uppercase">
                    <tr>
                        <th>SKU</th>
                        <th>Product & Variant</th>
                        <th>Price</th>
                        <th>Current Units</th>
                        <th>Status</th>
                        <th class="text-end">Quick Stock Adjustment</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($variants as $variant)
                        <tr>
                            <td class="font-monospace fw-bold text-dark">{{ $variant->sku }}</td>
                            <td>
                                <div class="fw-bold">{{ $variant->product->name }}</div>
                                <small class="text-muted">{{ $variant->name }}</small>
                            </td>
                            <td>{{ $variant->formatted_price }}</td>
                            <td>
                                <span class="fs-6 fw-bold {{ $variant->isLowStock() ? 'text-danger' : 'text-success' }}">
                                    {{ $variant->stock_quantity }}
                                </span>
                            </td>
                            <td>
                                @if($variant->isOutOfStock())
                                    <span class="badge bg-danger">Out of Stock</span>
                                @elseif($variant->isLowStock())
                                    <span class="badge bg-warning text-dark">Low Stock (≤ {{ $variant->low_stock_threshold }})</span>
                                @else
                                    <span class="badge bg-success">Healthy Stock</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <form action="{{ route('seller.inventory.update', $variant->id) }}" method="POST" class="d-inline-flex gap-1 align-items-center justify-content-end">
                                    @csrf
                                    <input type="number" name="new_stock" value="{{ $variant->stock_quantity }}" min="0" class="form-control form-control-sm text-center" style="width: 80px;" required>
                                    <input type="hidden" name="reason" value="Seller physical inventory audit">
                                    <button type="submit" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                                        Update
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="p-3 border-top d-flex justify-content-center">
            {{ $variants->links('pagination::bootstrap-5') }}
        </div>
    </div>

    <!-- Recent Inventory Movements Audit Trail -->
    <div class="card border-0 shadow-sm rounded-4 p-4 bg-white">
        <h5 class="fw-bold mb-3 text-dark">Recent Stock Movements Log</h5>
        <div class="table-responsive">
            <table class="table table-sm table-striped align-middle mb-0">
                <thead class="table-light small">
                    <tr>
                        <th>Date</th>
                        <th>SKU</th>
                        <th>Type</th>
                        <th>Units Delta</th>
                        <th>Stock After</th>
                        <th>Notes</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentMovements as $mov)
                        <tr>
                            <td class="small text-muted">{{ $mov->created_at->format('d M Y, h:i A') }}</td>
                            <td class="font-monospace fw-semibold">{{ $mov->variant?->sku ?? 'N/A' }}</td>
                            <td><span class="badge bg-light text-dark border text-uppercase">{{ $mov->type }}</span></td>
                            <td class="fw-bold {{ $mov->quantity_change >= 0 ? 'text-success' : 'text-danger' }}">
                                {{ $mov->quantity_change > 0 ? '+' : '' }}{{ $mov->quantity_change }}
                            </td>
                            <td class="fw-semibold">{{ $mov->stock_after }}</td>
                            <td class="small text-muted">{{ $mov->notes }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted small py-3">No inventory movements recorded yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

