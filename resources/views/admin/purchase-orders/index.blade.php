@extends('layouts.admin')

@section('title', 'Automated Purchase Orders & Stock Replenishment')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1"><i class="bi bi-cart-check-fill text-primary me-2"></i>स्टॉक खरीद आदेश एवं पुनःआपूर्ति (Purchase Orders &amp; Auto Replenishment)</h4>
        <span class="text-muted small">
            Automated restocking orders generated via minimum stock threshold triggers &amp; supplier pricing.
        </span>
    </div>
    <div class="d-flex gap-2">
        <form action="{{ route('admin.purchase-orders.auto-generate') }}" method="POST">
            @csrf
            <button type="submit" class="btn btn-primary fw-bold">
                <i class="bi bi-magic me-1"></i> स्वतः खरीद आदेश बनाएं (Auto-Generate POs)
            </button>
        </form>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif
@if(session('info'))
    <div class="alert alert-info alert-dismissible fade show" role="alert">
        <i class="bi bi-info-circle-fill me-2"></i>{{ session('info') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<!-- Low Stock Warning Banner -->
@if($lowStockVariants->isNotEmpty())
    <div class="card border-0 shadow-sm border-start border-4 border-warning mb-4 bg-white">
        <div class="card-body p-3">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <h6 class="fw-bold mb-0 text-dark">
                    <i class="bi bi-exclamation-triangle-fill text-warning me-1"></i> 
                    न्यूनतम स्टॉक चेतावनी (Low Stock Alert): <span class="badge bg-danger">{{ $lowStockVariants->count() }} items</span>
                </h6>
                <span class="small text-muted">Stock quantity &le; threshold</span>
            </div>
            <div class="d-flex flex-wrap gap-2">
                @foreach($lowStockVariants->take(12) as $variant)
                    <div class="badge bg-light text-dark border p-2">
                        <strong class="text-danger">{{ $variant->stock_quantity }} units left</strong> : 
                        {{ $variant->product->name ?? '' }} ({{ $variant->name }})
                        <code class="text-muted ms-1">{{ $variant->sku }}</code>
                    </div>
                @endforeach
                @if($lowStockVariants->count() > 12)
                    <span class="badge bg-secondary p-2">+{{ $lowStockVariants->count() - 12 }} more items</span>
                @endif
            </div>
        </div>
    </div>
@endif

<!-- Purchase Orders List Table -->
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
        <h6 class="fw-bold mb-0 text-dark">
            <i class="bi bi-file-earmark-spreadsheet-fill text-success me-1"></i> खरीद आदेश सूची (Purchase Orders)
        </h6>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.purchase-orders.index') }}" class="btn btn-sm {{ !$selectedStatus ? 'btn-dark' : 'btn-outline-secondary' }}">All</a>
            <a href="{{ route('admin.purchase-orders.index', ['status' => 'draft']) }}" class="btn btn-sm {{ $selectedStatus === 'draft' ? 'btn-warning text-dark' : 'btn-outline-secondary' }}">Draft</a>
            <a href="{{ route('admin.purchase-orders.index', ['status' => 'approved']) }}" class="btn btn-sm {{ $selectedStatus === 'approved' ? 'btn-primary' : 'btn-outline-secondary' }}">Approved</a>
            <a href="{{ route('admin.purchase-orders.index', ['status' => 'received']) }}" class="btn btn-sm {{ $selectedStatus === 'received' ? 'btn-success' : 'btn-outline-secondary' }}">Received</a>
        </div>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light small">
                <tr>
                    <th>PO Number</th>
                    <th>Supplier / Wholesaler</th>
                    <th>Store Branch</th>
                    <th>Items</th>
                    <th class="text-end">Total Buy Cost</th>
                    <th class="text-center">Status</th>
                    <th>Created</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($purchaseOrders as $po)
                    <tr>
                        <td>
                            <a href="{{ route('admin.purchase-orders.show', $po->id) }}" class="fw-bold text-decoration-none">
                                {{ $po->po_number }}
                            </a>
                        </td>
                        <td>
                            <div class="fw-bold text-dark small">{{ $po->supplier->name }}</div>
                            <span class="text-muted" style="font-size: 0.75rem;">{{ $po->supplier->city }} | Ph: {{ $po->supplier->phone }}</span>
                        </td>
                        <td class="small">{{ $po->store->name }}</td>
                        <td>
                            <span class="badge bg-secondary">{{ $po->items->count() }} line items</span>
                            <span class="small text-muted ms-1">({{ $po->items->sum('quantity_ordered') }} units)</span>
                        </td>
                        <td class="text-end fw-bold text-dark">
                            ₹{{ number_format($po->total_cost_rupees, 2) }}
                        </td>
                        <td class="text-center">
                            @if($po->status === 'draft')
                                <span class="badge bg-warning text-dark">Draft Pending</span>
                            @elseif($po->status === 'approved')
                                <span class="badge bg-primary">Approved / Ordered</span>
                            @elseif($po->status === 'received')
                                <span class="badge bg-success">Received (GRN)</span>
                            @elseif($po->status === 'partially_received')
                                <span class="badge bg-info">Partially Received</span>
                            @else
                                <span class="badge bg-secondary">{{ strtoupper($po->status) }}</span>
                            @endif
                        </td>
                        <td class="small text-muted">{{ $po->created_at->format('d M Y') }}</td>
                        <td class="text-end">
                            <a href="{{ route('admin.purchase-orders.show', $po->id) }}" class="btn btn-sm btn-outline-primary">
                                View / Receive
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center py-4 text-muted small">No purchase orders found. Click "Auto-Generate POs" to create replenishment orders.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($purchaseOrders->hasPages())
        <div class="card-footer bg-white">
            {{ $purchaseOrders->links() }}
        </div>
    @endif
</div>
@endsection

