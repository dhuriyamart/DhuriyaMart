@extends('layouts.seller')

@section('title', 'Assigned Orders - ' . $shop->name)
@section('page_title', 'Assigned Seller Orders')

@section('content')
<div class="container-fluid px-0">
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-white">
        <!-- Filter Tabs -->
        <div class="card-header bg-light py-3 border-bottom d-flex flex-wrap justify-content-between align-items-center gap-2">
            <div class="d-flex gap-2">
                <a href="{{ route('seller.orders.index') }}" class="btn btn-sm {{ !request('status') ? 'btn-dark' : 'btn-outline-secondary' }} rounded-pill px-3">
                    All Orders
                </a>
                <a href="{{ route('seller.orders.index', ['status' => 'confirmed']) }}" class="btn btn-sm {{ request('status') === 'confirmed' ? 'btn-dark' : 'btn-outline-secondary' }} rounded-pill px-3">
                    Confirmed
                </a>
                <a href="{{ route('seller.orders.index', ['status' => 'dispatched']) }}" class="btn btn-sm {{ request('status') === 'dispatched' ? 'btn-dark' : 'btn-outline-secondary' }} rounded-pill px-3">
                    Dispatched
                </a>
                <a href="{{ route('seller.orders.index', ['status' => 'delivered']) }}" class="btn btn-sm {{ request('status') === 'delivered' ? 'btn-dark' : 'btn-outline-secondary' }} rounded-pill px-3">
                    Delivered
                </a>
            </div>
            <span class="small text-muted">Showing {{ $orders->total() }} assigned order(s)</span>
        </div>

        @if($orders->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light small text-uppercase">
                        <tr>
                            <th>Sub-Order #</th>
                            <th>Parent Order</th>
                            <th>Items</th>
                            <th>Gross Total</th>
                            <th>Commission</th>
                            <th>Net Earnings</th>
                            <th>Status</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($orders as $order)
                            <tr>
                                <td class="font-monospace fw-bold text-dark">{{ $order->seller_order_number }}</td>
                                <td class="small font-monospace text-muted">{{ $order->order->order_number }}</td>
                                <td>
                                    <span class="badge bg-light text-dark border">{{ $order->items->count() }} item(s)</span>
                                </td>
                                <td>{{ $order->formatted_total }}</td>
                                <td class="small text-danger">- {{ $order->formatted_commission }}</td>
                                <td class="fw-bold text-success">{{ $order->formatted_net_seller }}</td>
                                <td><x-status-badge :status="$order->status" /></td>
                                <td class="text-end">
                                    <a href="{{ route('seller.orders.show', $order->id) }}" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                                        View & Dispatch
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="p-3 border-top d-flex justify-content-center">
                {{ $orders->links('pagination::bootstrap-5') }}
            </div>
        @else
            <div class="text-center py-5 text-muted">
                <i class="bi bi-inbox fs-1 text-muted d-block mb-3"></i>
                <h5>No orders found</h5>
                <p class="small text-muted">When customers buy your products, orders will appear here for packaging and dispatch.</p>
            </div>
        @endif
    </div>
</div>
@endsection

