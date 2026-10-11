@extends('layouts.admin')

@section('title', 'Purchase Order #' . $po->po_number)

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <a href="{{ route('admin.purchase-orders.index') }}" class="text-decoration-none text-muted small">
            <i class="bi bi-arrow-left me-1"></i> Back to Purchase Orders
        </a>
        <h4 class="fw-bold mb-1 mt-1">
            <i class="bi bi-receipt-cutoff text-primary me-2"></i>Purchase Order #{{ $po->po_number }}
        </h4>
        <span class="text-muted small">
            Store: <strong>{{ $po->store->name }}</strong> | Created by: {{ $po->createdBy->name ?? 'System' }} | Status: 
            <span class="badge {{ $po->status === 'received' ? 'bg-success' : ($po->status === 'approved' ? 'bg-primary' : 'bg-warning text-dark') }}">
                {{ strtoupper($po->status) }}
            </span>
        </span>
    </div>
    <div class="d-flex gap-2">
        @if($po->status === 'draft')
            <form action="{{ route('admin.purchase-orders.approve', $po->id) }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-success fw-bold">
                    <i class="bi bi-check-circle-fill me-1"></i> खरीद आदेश स्वीकृत करें (Approve PO)
                </button>
            </form>
        @endif
        <button onclick="window.print()" class="btn btn-outline-secondary fw-bold">
            <i class="bi bi-printer me-1"></i> Print PO
        </button>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="row g-4">
    <!-- PO Line Items Table & GRN Receiving Form -->
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h6 class="fw-bold mb-0 text-dark">
                    <i class="bi bi-list-check text-primary me-1"></i> ऑर्डर किए गए सामान (Ordered Line Items)
                </h6>
                <span class="badge bg-light text-muted border">{{ $po->items->count() }} items</span>
            </div>

            @if($po->status === 'approved' || $po->status === 'partially_received')
                <!-- GRN Receiving Form for Live Stock Intake -->
                <form action="{{ route('admin.purchase-orders.receive', $po->id) }}" method="POST">
                    @csrf
                    <div class="table-responsive">
                        <table class="table table-bordered align-middle mb-0">
                            <thead class="table-light small">
                                <tr>
                                    <th>Item &amp; SKU</th>
                                    <th class="text-center">Ordered</th>
                                    <th class="text-center" style="width: 140px;">Received Qty (GRN)</th>
                                    <th class="text-center" style="width: 130px;">Damaged / Return</th>
                                    <th class="text-end">Unit Buy Cost</th>
                                    <th class="text-end">Total Cost</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($po->items as $item)
                                    <tr>
                                        <td>
                                            <div class="fw-bold text-dark">{{ $item->product_name }}</div>
                                            <div class="text-muted small">{{ $item->variant_name }} | <code>{{ $item->sku }}</code></div>
                                        </td>
                                        <td class="text-center fw-bold">{{ $item->quantity_ordered }}</td>
                                        <td>
                                            <input type="number" name="received_quantities[{{ $item->id }}]" 
                                                   class="form-control text-center fw-bold" 
                                                   value="{{ $item->quantity_ordered - $item->quantity_received }}" 
                                                   min="0" max="{{ $item->quantity_ordered }}">
                                        </td>
                                        <td>
                                            <input type="number" name="damaged_quantities[{{ $item->id }}]" 
                                                   class="form-control text-center text-danger fw-bold" 
                                                   value="0" min="0">
                                        </td>
                                        <td class="text-end small">₹{{ number_format($item->unit_cost_rupees, 2) }}</td>
                                        <td class="text-end fw-bold">₹{{ number_format($item->total_cost_rupees, 2) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="card-footer bg-white p-3 d-flex justify-content-between align-items-center">
                        <span class="small text-muted">
                            <i class="bi bi-info-circle text-primary me-1"></i>
                            Submitting GRN immediately updates live product stock quantity.
                        </span>
                        <button type="submit" class="btn btn-success fw-bold px-4">
                            <i class="bi bi-box-arrow-in-down me-1"></i> सामान आवक दर्ज करें (Process GRN Stock Intake)
                        </button>
                    </div>
                </form>
            @else
                <!-- Read-only Table -->
                <div class="table-responsive">
                    <table class="table table-bordered align-middle mb-0">
                        <thead class="table-light small">
                            <tr>
                                <th>Item &amp; SKU</th>
                                <th class="text-center">Ordered</th>
                                <th class="text-center">Received</th>
                                <th class="text-end">Unit Buy Cost</th>
                                <th class="text-end">Line Total</th>
                                <th class="text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($po->items as $item)
                                <tr>
                                    <td>
                                        <div class="fw-bold text-dark">{{ $item->product_name }}</div>
                                        <div class="text-muted small">{{ $item->variant_name }} | <code>{{ $item->sku }}</code></div>
                                    </td>
                                    <td class="text-center fw-bold">{{ $item->quantity_ordered }}</td>
                                    <td class="text-center fw-bold text-success">{{ $item->quantity_received }}</td>
                                    <td class="text-end small">₹{{ number_format($item->unit_cost_rupees, 2) }}</td>
                                    <td class="text-end fw-bold">₹{{ number_format($item->total_cost_rupees, 2) }}</td>
                                    <td class="text-center">
                                        <span class="badge {{ $item->status === 'received' ? 'bg-success' : 'bg-secondary' }}">
                                            {{ strtoupper($item->status) }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>

    <!-- Right: Supplier Info & Cost Summary -->
    <div class="col-lg-4">
        <!-- Total Cost Card -->
        <div class="card border-0 shadow-sm p-4 bg-white mb-4">
            <h6 class="fw-bold text-muted small text-uppercase mb-2">कुल खरीद लागत (Total Procurement Cost)</h6>
            <div class="fs-2 fw-extrabold text-primary mb-2">₹{{ number_format($po->total_cost_rupees, 2) }}</div>
            <div class="small text-muted mb-3">Total Units Ordered: <strong>{{ $po->items->sum('quantity_ordered') }}</strong></div>
            <div class="border-top pt-2 small text-muted">
                <div>Expected Delivery: <strong>{{ $po->expected_delivery_date ? $po->expected_delivery_date->format('d M Y') : 'Standard 3 days' }}</strong></div>
                @if($po->received_at)
                    <div>Received Date: <strong>{{ $po->received_at->format('d M Y, h:i A') }}</strong></div>
                @endif
            </div>
        </div>

        <!-- Supplier Details Card -->
        <div class="card border-0 shadow-sm p-4 bg-white mb-4">
            <h6 class="fw-bold text-dark mb-3"><i class="bi bi-building me-2 text-primary"></i>आपूर्तिकर्ता विवरण (Supplier Details)</h6>
            <h5 class="fw-bold text-dark mb-1">{{ $po->supplier->name }}</h5>
            <span class="badge bg-light text-dark border align-self-start mb-2">{{ ucfirst($po->supplier->category) }} Wholesaler</span>
            <div class="small text-muted mb-1"><i class="bi bi-person me-1"></i>Contact: {{ $po->supplier->contact_person ?? 'Manager' }}</div>
            <div class="small text-muted mb-1"><i class="bi bi-telephone me-1"></i>Phone: {{ $po->supplier->phone }}</div>
            <div class="small text-muted mb-1"><i class="bi bi-envelope me-1"></i>Email: {{ $po->supplier->email ?? '—' }}</div>
            <div class="small text-muted mb-2"><i class="bi bi-geo-alt me-1"></i>Address: {{ $po->supplier->address }}, {{ $po->supplier->city }}</div>
            <div class="border-top pt-2 small text-muted">
                GSTIN: <code>{{ $po->supplier->gstin ?? 'Unregistered' }}</code> | Terms: <strong>{{ $po->supplier->payment_terms }}</strong>
            </div>
        </div>
    </div>
</div>
@endsection

