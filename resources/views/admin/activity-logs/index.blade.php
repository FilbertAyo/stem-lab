@php
    $isClientContext = in_array($logScope ?? '', ['client_activity', 'client_all'], true);
@endphp
<x-admin-layout>
    <div class="row">
        <div class="col-md-12 col-xl-12">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h5 class="mb-0">Audit Logs</h5>
                    <p class="text-muted mb-0 small">
                        Use <strong>Log scope</strong> to switch between admin subdomain activity (<code>admin_*</code>) and main-app client usage (<code>client_*</code>). All entries include route, method, status, IP, and redacted payloads where applicable.
                    </p>
                </div>
            </div>

            @if (! $logTableExists)
                <div class="alert alert-danger" role="alert">
                    Activity log table not found. Run migrations to create <code>{{ config('activitylog.table_name', 'activity_log') }}</code>.
                </div>
            @else
                <div class="card mb-4">
                    <div class="card-body">
                        <form method="GET" action="{{ route('admin.activity-logs.index') }}" class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label">Log Scope</label>
                                <select name="log_scope" class="form-select">
                                    <option value="admin_activity" {{ ($logScope ?? 'admin_activity') === 'admin_activity' ? 'selected' : '' }}>
                                        Only `admin_activity`
                                    </option>
                                    <option value="admin_all" {{ ($logScope ?? 'admin_activity') === 'admin_all' ? 'selected' : '' }}>
                                        All `admin_*` logs
                                    </option>
                                    <option value="client_activity" {{ ($logScope ?? '') === 'client_activity' ? 'selected' : '' }}>
                                        Only `client_activity`
                                    </option>
                                    <option value="client_all" {{ ($logScope ?? '') === 'client_all' ? 'selected' : '' }}>
                                        All `client_*` logs
                                    </option>
                                    <option value="all" {{ ($logScope ?? 'admin_activity') === 'all' ? 'selected' : '' }}>
                                        All logs
                                    </option>
                                </select>
                            </div>

                            <div class="col-md-3">
                                <label class="form-label">Log Name</label>
                                <select name="log_name" class="form-select">
                                    <option value="">All</option>
                                    @foreach($availableLogNames as $name)
                                        <option value="{{ $name }}" {{ $selectedLogName === $name ? 'selected' : '' }}>
                                            {{ $name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-3">
                                <label class="form-label">Event / Method</label>
                                <select name="event" class="form-select">
                                    <option value="">Any</option>
                                    @foreach(['GET', 'POST', 'PUT', 'PATCH', 'DELETE'] as $evt)
                                        <option value="{{ $evt }}" {{ $event === $evt ? 'selected' : '' }}>
                                            {{ $evt }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-3">
                                <label class="form-label">Search (description)</label>
                                <input type="text" name="search" class="form-control"
                                    value="{{ $search ?? '' }}" placeholder="e.g. created project" />
                            </div>

                            <div class="col-md-2 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary w-100">Filter</button>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="card tbl-card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="ti ti-history me-2"></i>
                            Recent Activity
                            @if(method_exists($logs, 'total'))
                                ({{ $logs->total() }})
                            @endif
                        </h5>
                    </div>

                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover table-bordered mb-0">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Time</th>
                                        <th>{{ $isClientContext ? 'User' : 'Admin' }}</th>
                                        <th>Action</th>
                                        <th>Route</th>
                                        <th>Meta</th>
                                    </tr>
                                </thead>

                                <tbody>
                                    @forelse($logs as $index => $activity)
                                        <tr>
                                            <td>
                                                {{ $index + 1 + (($logs->currentPage() - 1) * $logs->perPage()) }}
                                            </td>

                                            <td>
                                                @if($activity->created_at)
                                                    <div class="small">{{ $activity->created_at->format('M d, Y H:i:s') }}</div>
                                                    <div><small class="text-muted">{{ $activity->created_at->diffForHumans() }}</small></div>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>

                                            <td>
                                                @if($activity->causer)
                                                    @php
                                                        $userRole = $activity->getExtraProperty('user_role');
                                                    @endphp
                                                    <div class="small">
                                                        <strong>{{ $activity->causer->name ?? ($isClientContext ? 'User' : 'Admin') }}</strong>
                                                        @if($userRole)
                                                            <span class="badge bg-info ms-1">{{ $userRole }}</span>
                                                        @endif
                                                    </div>
                                                    <div><small class="text-muted">{{ $activity->causer->email ?? '' }}</small></div>
                                                @else
                                                    <span class="text-muted">System</span>
                                                @endif
                                            </td>

                                            <td style="max-width: 360px;">
                                                <div class="mb-1">
                                                    <span class="badge bg-secondary me-1">{{ $activity->log_name }}</span>
                                                </div>
                                                <div class="small">{{ $activity->description }}</div>

                                                @php
                                                    $payload = $activity->getExtraProperty('payload');
                                                    $routeName = $activity->getExtraProperty('route_name');
                                                @endphp
                                                @if($routeName)
                                                    <div class="mt-1">
                                                        <small class="text-muted">
                                                            <i class="ti ti-location me-1"></i>{{ $routeName }}
                                                        </small>
                                                    </div>
                                                @endif
                                            </td>

                                            <td>
                                                @php
                                                    $uri = $activity->getExtraProperty('uri');
                                                    $statusCode = $activity->getExtraProperty('status_code');
                                                @endphp
                                                @if($uri)
                                                    <code class="small">{{ $uri }}</code>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif

                                                <div class="mt-2">
                                                    <small class="text-muted">
                                                        <i class="ti ti-world me-1"></i>{{ $activity->event ?? '-' }}
                                                    </small>
                                                </div>
                                            </td>

                                            <td>
                                                @if(! is_null($statusCode) && $statusCode !== '')
                                                    <span
                                                        class="badge {{ (int) $statusCode >= 400 ? 'bg-danger' : ((int) $statusCode >= 300 ? 'bg-warning' : 'bg-success') }}">
                                                        HTTP {{ $statusCode }}
                                                    </span>
                                                @else
                                                    <span class="badge bg-light text-muted border">No status</span>
                                                @endif

                                                @php
                                                    $ip = $activity->getExtraProperty('ip_address');
                                                @endphp
                                                @if($ip)
                                                    <div class="mt-2">
                                                        <small class="text-muted">
                                                            <i class="ti ti-server me-1"></i>{{ $ip }}
                                                        </small>
                                                    </div>
                                                @endif

                                                <div class="mt-2">
                                                    <details>
                                                        <summary class="small text-primary cursor-pointer">Payload</summary>
                                                        <pre class="bg-light p-2 mt-2 mb-0 small" style="white-space: pre-wrap;">
@php
    $payloadJson = is_array($payload) ? json_encode($payload, JSON_PRETTY_PRINT) : (is_string($payload) ? $payload : json_encode($payload ?? [], JSON_PRETTY_PRINT));
@endphp
{{ $payloadJson }}
                                                        </pre>
                                                    </details>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="text-center py-4">
                                                <i class="ti ti-inbox fs-1 text-muted mb-3 d-block"></i>
                                                <p class="text-muted mb-0">No activity logs found for the selected filters.</p>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        @if(method_exists($logs, 'hasPages') && $logs->hasPages())
                            <div class="mt-3">
                                {{ $logs->links() }}
                            </div>
                        @endif
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-admin-layout>

