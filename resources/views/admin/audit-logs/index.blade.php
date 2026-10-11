@extends('layouts.admin')

@section('title', 'Audit Logs - DhuriyaMart Admin')
@section('page_title', 'System & Security Audit Trail')

@section('content')
<div class="container-fluid px-0">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1 text-dark">System Audit Logs</h4>
            <p class="text-muted small mb-0">Immutable records of administrative operations, moderation actions, and configuration changes</p>
        </div>
        <form action="{{ route('admin.audit-logs.index') }}" method="GET" class="d-flex gap-2">
            <div class="input-group input-group-sm">
                <input type="text" name="action" class="form-control rounded-start-pill" placeholder="Filter by action..." value="{{ request('action') }}">
                <button type="submit" class="btn btn-outline-secondary rounded-end-pill px-3">
                    <i class="bi bi-search"></i>
                </button>
            </div>
            @if(request()->filled('action'))
                <a href="{{ route('admin.audit-logs.index') }}" class="btn btn-sm btn-light rounded-pill px-3">Clear</a>
            @endif
        </form>
    </div>

    @include('components.alerts')

    <div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-white">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light small text-uppercase">
                    <tr>
                        <th style="width: 160px;">Timestamp</th>
                        <th>User</th>
                        <th>Action</th>
                        <th>Target Model</th>
                        <th>IP &amp; Device</th>
                        <th class="text-end">Payload Details</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($logs as $log)
                        <tr>
                            <td class="small text-muted">
                                <div>{{ $log->created_at->format('d M Y') }}</div>
                                <div class="font-monospace text-secondary" style="font-size: 0.75rem;">{{ $log->created_at->format('H:i:s') }}</div>
                            </td>
                            <td>
                                @if($log->user)
                                    <div class="fw-semibold small text-dark">{{ $log->user->name }}</div>
                                    <div class="text-muted" style="font-size: 0.75rem;">{{ $log->user->email }}</div>
                                @else
                                    <span class="badge bg-secondary rounded-pill font-monospace" style="font-size: 0.7rem;">System / Guest</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 rounded-pill font-monospace small px-2 py-1">
                                    {{ $log->action }}
                                </span>
                            </td>
                            <td class="small">
                                @if($log->auditable_type)
                                    <span class="text-dark fw-semibold">{{ class_basename($log->auditable_type) }}</span>
                                    <span class="text-muted">#{{ $log->auditable_id }}</span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td class="small">
                                <div class="font-monospace text-muted" style="font-size: 0.75rem;">{{ $log->ip_address ?? '127.0.0.1' }}</div>
                                <div class="text-truncate text-secondary" style="max-width: 180px; font-size: 0.7rem;" title="{{ $log->user_agent }}">
                                    {{ Str::limit($log->user_agent, 25) }}
                                </div>
                            </td>
                            <td class="text-end">
                                @if($log->old_values || $log->new_values)
                                    <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill py-0 px-2" data-bs-toggle="modal" data-bs-target="#logModal{{ $log->id }}" style="font-size: 0.75rem;">
                                        <i class="bi bi-code-slash me-1"></i> View Diff
                                    </button>

                                    <!-- Modal -->
                                    <div class="modal fade text-start" id="logModal{{ $log->id }}" tabindex="-1" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered modal-lg">
                                            <div class="modal-content border-0 shadow rounded-4">
                                                <div class="modal-header border-0 pb-0">
                                                    <h6 class="modal-title fw-bold">Audit Payload: {{ $log->action }} (Log #{{ $log->id }})</h6>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="row g-3">
                                                        @if($log->old_values)
                                                            <div class="col-md-6">
                                                                <h6 class="small fw-bold text-muted text-uppercase mb-2">Previous Values</h6>
                                                                <pre class="bg-light p-3 rounded-3 small font-monospace text-danger" style="max-height: 250px; overflow-y: auto;">{{ json_encode($log->old_values, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
                                                            </div>
                                                        @endif
                                                        <div class="{{ $log->old_values ? 'col-md-6' : 'col-12' }}">
                                                            <h6 class="small fw-bold text-muted text-uppercase mb-2">New / Applied Values</h6>
                                                            <pre class="bg-light p-3 rounded-3 small font-monospace text-success" style="max-height: 250px; overflow-y: auto;">{{ json_encode($log->new_values, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="modal-footer border-0 pt-0">
                                                    <button type="button" class="btn btn-sm btn-secondary rounded-pill px-3" data-bs-dismiss="modal">Close</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @else
                                    <span class="text-muted small">-</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">
                                <i class="bi bi-clock-history fs-1 d-block mb-2 text-secondary opacity-50"></i>
                                No audit log entries recorded yet.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($logs->hasPages())
            <div class="card-footer bg-white border-0 py-3">
                {{ $logs->links() }}
            </div>
        @endif
    </div>
</div>
@endsection

