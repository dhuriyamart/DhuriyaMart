@props(['status'])

@php
    $class = match(strtolower($status)) {
        'paid', 'delivered', 'approved', 'active', 'successful' => 'bg-success text-white',
        'dispatched', 'in_transit', 'picked_up', 'processing' => 'bg-info text-dark',
        'pending', 'draft', 'in_settlement' => 'bg-warning text-dark',
        'cancelled', 'rejected', 'failed', 'suspended' => 'bg-danger text-white',
        'refunded', 'returned' => 'bg-secondary text-white',
        default => 'bg-light text-dark border',
    };
@endphp

<span class="badge {{ $class }} rounded-pill px-2 py-1 fw-semibold text-capitalize" style="font-size: 0.8rem;">
    {{ str_replace('_', ' ', $status) }}
</span>

