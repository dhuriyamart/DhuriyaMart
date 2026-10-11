@extends('layouts.admin')

@section('title', 'Village Stores Network Management')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1"><i class="bi bi-shop me-2 text-primary"></i>ग्रामीण स्मार्ट मार्ट नेटवर्क (Village Stores Network)</h4>
        <span class="text-muted small">
            Centralized management of multi-store village smart branches, store inventory, managers, and cash registers.
        </span>
    </div>
    <div>
        <button class="btn btn-primary fw-bold" data-bs-toggle="modal" data-bs-target="#newStoreModal">
            <i class="bi bi-plus-circle me-1"></i> नई ग्रामीण शाखा जोड़ें (Add Village Branch)
        </button>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<!-- Network Metrics -->
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm p-3 bg-white border-start border-4 border-primary">
            <span class="text-muted small fw-bold text-uppercase">कुल स्टोर शाखाएं (Stores)</span>
            <div class="fs-3 fw-extrabold text-primary mt-1">{{ $stores->count() }} Branches</div>
            <span class="small text-muted">Village Mart Units</span>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm p-3 bg-white border-start border-4 border-success">
            <span class="text-muted small fw-bold text-uppercase">सक्रिय नकद दराज (Active Drawers)</span>
            <div class="fs-3 fw-extrabold text-success mt-1">{{ $activeRegistersCount }} Open</div>
            <span class="small text-muted">Currently in Session</span>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm p-3 bg-white border-start border-4 border-info">
            <span class="text-muted small fw-bold text-uppercase">कुल बिक्री ऑर्डर्स (Orders)</span>
            <div class="fs-3 fw-extrabold text-info mt-1">{{ number_format($totalOrdersCount) }} Orders</div>
            <span class="small text-muted">Network POS &amp; Web Sales</span>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm p-3 bg-white border-start border-4 border-warning">
            <span class="text-muted small fw-bold text-uppercase">कुल बिक्री राजस्व (Network Revenue)</span>
            <div class="fs-3 fw-extrabold text-dark mt-1">₹{{ number_format($totalSalesRupees, 2) }}</div>
            <span class="small text-muted">All-Store Gross Sales</span>
        </div>
    </div>
</div>

<!-- Stores Table -->
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white py-3">
        <h6 class="fw-bold mb-0 text-dark">
            <i class="bi bi-geo-alt-fill text-danger me-1"></i> शाखाओं की सूची (Village Store Branches)
        </h6>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light small">
                <tr>
                    <th>Store Code</th>
                    <th>Branch Name</th>
                    <th>Village &amp; Location</th>
                    <th>Branch Manager</th>
                    <th>Contact Phone</th>
                    <th class="text-center">Total Orders</th>
                    <th class="text-center">Status</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($stores as $st)
                    <tr>
                        <td><code>{{ $st->code }}</code></td>
                        <td>
                            <strong class="text-dark">{{ $st->name }}</strong>
                        </td>
                        <td>
                            <div class="small fw-bold">{{ $st->village_name }}</div>
                            <span class="text-muted" style="font-size: 0.75rem;">{{ $st->district }}, {{ $st->state }} - {{ $st->pincode }}</span>
                        </td>
                        <td>
                            <span class="small">{{ $st->manager->name ?? 'Unassigned' }}</span>
                        </td>
                        <td class="small">{{ $st->phone ?? '—' }}</td>
                        <td class="text-center">
                            <span class="badge bg-light text-dark border">{{ $st->orders_count }} orders</span>
                        </td>
                        <td class="text-center">
                            <span class="badge {{ $st->is_active ? 'bg-success' : 'bg-secondary' }}">
                                {{ $st->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td class="text-end">
                            <form action="{{ route('admin.village-stores.toggle', $st->id) }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-sm {{ $st->is_active ? 'btn-outline-danger' : 'btn-outline-success' }}">
                                    {{ $st->is_active ? 'Deactivate' : 'Activate' }}
                                </button>
                            </form>
                            <a href="{{ route('pos.index') }}" class="btn btn-sm btn-outline-primary ms-1" target="_blank">
                                Open POS
                            </a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<!-- Modal: Add New Store Branch -->
<div class="modal fade" id="newStoreModal" tabindex="-1">
    <div class="modal-dialog">
        <form action="{{ route('admin.village-stores.store') }}" method="POST" class="modal-content">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title fw-bold"><i class="bi bi-shop me-2 text-primary"></i>नई ग्रामीण शाखा जोड़ें</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label fw-bold">शाखा कोड (Store Code)</label>
                    <input type="text" name="code" class="form-control" placeholder="e.g. STORE-MAU-03" required>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">शाखा का नाम (Store Name)</label>
                    <input type="text" name="name" class="form-control" placeholder="e.g. Dhuriya Mart - Kopaganj Branch" required>
                </div>
                <div class="row g-2 mb-3">
                    <div class="col-6">
                        <label class="form-label fw-bold">गाँव / कस्बा (Village/Town)</label>
                        <input type="text" name="village_name" class="form-control" placeholder="Kopaganj" required>
                    </div>
                    <div class="col-6">
                        <label class="form-label fw-bold">जिला (District)</label>
                        <input type="text" name="district" class="form-control" placeholder="Mau" required>
                    </div>
                </div>
                <div class="row g-2 mb-3">
                    <div class="col-6">
                        <label class="form-label fw-bold">राज्य (State)</label>
                        <input type="text" name="state" class="form-control" value="Uttar Pradesh" required>
                    </div>
                    <div class="col-6">
                        <label class="form-label fw-bold">पिन कोड (Pincode)</label>
                        <input type="text" name="pincode" class="form-control" placeholder="275305" required>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">शाखा संपर्क फोन (Phone)</label>
                    <input type="text" name="phone" class="form-control" placeholder="+91 9876500000">
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">शाखा प्रबंधक (Branch Manager)</label>
                    <select name="manager_id" class="form-select">
                        <option value="">Select Manager</option>
                        @foreach($managers as $m)
                            <option value="{{ $m->id }}">{{ $m->name }} ({{ $m->email }})</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">रद्द करें</button>
                <button type="submit" class="btn btn-primary fw-bold">शाखा बनाएं (Create Branch)</button>
            </div>
        </form>
    </div>
</div>
@endsection

