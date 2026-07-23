<x-admin-layout>
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h5 class="mb-0">Competitions</h5>
                    <p class="text-muted mb-0">Manage club competitions</p>
                </div>
                <a href="{{ route('admin.competitions.create') }}" class="btn btn-primary">
                    <i class="ti ti-plus"></i> Create Competition
                </a>
            </div>

            

            <!-- Filters -->
            <div class="card mb-3">
                <div class="card-body py-3">
                    <form method="GET" action="{{ route('admin.competitions.index') }}" class="row g-3 align-items-end">
                        <div class="col-md-3">
                            <input type="text" name="search" class="form-control" placeholder="Competition name..." value="{{ request('search') }}">
                        </div>
                        <div class="col-md-3">
                            <select name="club_id" class="form-select">
                                <option value="">All Clubs</option>
                                @foreach($clubs as $club)
                                    <option value="{{ $club->id }}" {{ request('club_id') == $club->id ? 'selected' : '' }}>
                                        {{ $club->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <select name="status" class="form-select">
                                <option value="">All Status</option>
                                <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                                <option value="published" {{ request('status') == 'published' ? 'selected' : '' }}>Published</option>
                                <option value="ongoing" {{ request('status') == 'ongoing' ? 'selected' : '' }}>Ongoing</option>
                                <option value="judging" {{ request('status') == 'judging' ? 'selected' : '' }}>Judging</option>
                                <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                                <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <button type="submit" class="btn btn-outline-primary me-2">
                                <i class="ti ti-filter"></i> Filter
                            </button>
                            <a href="{{ route('admin.competitions.index') }}" class="btn btn-outline-secondary">
                                <i class="ti ti-x"></i> Clear
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card tbl-card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover table-bordered mb-0 align-middle">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Competition</th>
                                    <th>Club</th>
                                    <th>Timeline</th>
                                    <th>Stages</th>
                                    <th>Status</th>
                                    <th class="text-center" style="width: 140px;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($competitions as $competition)
                                    <tr>
                                        <td>{{ $loop->iteration + ($competitions->currentPage() - 1) * $competitions->perPage() }}</td>
                                        <td>
                                            <div class="d-flex align-items-center gap-2">
                                                @if($competition->cover_image)
                                                    <img src="{{ Storage::url($competition->cover_image) }}" alt="{{ $competition->name }}" 
                                                         class="rounded" style="width: 48px; height: 48px; object-fit: cover;">
                                                @else
                                                    <div class="bg-light rounded d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                                                        <i class="ti ti-trophy text-muted"></i>
                                                    </div>
                                                @endif
                                                <div>
                                                    <a href="{{ route('admin.competitions.show', $competition) }}" class="fw-semibold text-dark">
                                                        {{ $competition->name }}
                                                    </a>
                                                    <div class="small text-muted">
                                                        Created {{ $competition->created_at?->format('d M Y') }}
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge bg-info">{{ $competition->club->name }}</span>
                                        </td>
                                        <td>
                                            <div class="small">
                                                <div>
                                                    <i class="ti ti-calendar-event text-success"></i>
                                                    {{ $competition->competition_start?->format('d M Y') ?? 'Not set' }}
                                                </div>
                                                <div>
                                                    <i class="ti ti-calendar-off text-danger"></i>
                                                    {{ $competition->competition_end?->format('d M Y') ?? 'Not set' }}
                                                </div>
                                            </div>
                                        </td>
                                        <td style="min-width: 280px;">
                                            @if($competition->phases->isEmpty())
                                                <span class="text-muted small">No stages</span>
                                            @else
                                                <div class="d-flex flex-wrap gap-1">
                                                    @foreach($competition->phases as $phase)
                                                        @php
                                                            $phaseAction = match ($phase->phase_key) {
                                                                'registration' => [
                                                                    'route' => route('admin.competitions.registrations.index', $competition),
                                                                    'btn' => 'btn-outline-success',
                                                                    'icon' => 'ti ti-users',
                                                                ],
                                                                'idea_submission', 'idea_shortlisting' => [
                                                                    'route' => route('admin.competitions.ideas.index', $competition),
                                                                    'btn' => 'btn-outline-warning',
                                                                    'icon' => 'ti ti-bulb',
                                                                ],
                                                                'prototype_development', 'prototype_shortlisting' => [
                                                                    'route' => route('admin.competitions.prototype-shortlisting.index', $competition),
                                                                    'btn' => 'btn-outline-info',
                                                                    'icon' => 'ti ti-list-check',
                                                                ],
                                                                'mentor_onboarding', 'prototype_modification' => [
                                                                    'route' => route('admin.competitions.prototype-modification.index', $competition),
                                                                    'btn' => 'btn-outline-dark',
                                                                    'icon' => 'ti ti-adjustments',
                                                                ],
                                                                'pitch_winner' => [
                                                                    'route' => route('admin.competitions.pitch-winner.index', $competition),
                                                                    'btn' => 'btn-outline-success',
                                                                    'icon' => 'ti ti-microphone',
                                                                ],
                                                                default => null,
                                                            };
                                                            $statusClass = match ($phase->status) {
                                                                'open' => 'bg-success',
                                                                'closed' => 'bg-secondary',
                                                                'results_pending' => 'bg-warning text-dark',
                                                                default => 'bg-light text-dark',
                                                            };
                                                        @endphp
                                                        @if($phaseAction)
                                                            <a href="{{ $phaseAction['route'] }}"
                                                               class="btn btn-sm {{ $phaseAction['btn'] }} py-0 px-2"
                                                               title="{{ $phase->name }} · {{ ucfirst(str_replace('_', ' ', $phase->status)) }}">
                                                                <i class="{{ $phaseAction['icon'] }}"></i>
                                                                {{ $phase->name }}
                                                                <span class="badge {{ $statusClass }} ms-1">{{ ucfirst(str_replace('_', ' ', $phase->status)) }}</span>
                                                            </a>
                                                        @else
                                                            <span class="badge bg-light text-dark border" title="No admin page for this stage">
                                                                {{ $phase->name }}
                                                            </span>
                                                        @endif
                                                    @endforeach
                                                </div>
                                            @endif
                                        </td>
                                    
                                        <td>
                                            <span class="badge bg-{{ $competition->status_badge }}">
                                                {{ ucfirst($competition->status) }}
                                            </span>
                                            @if(!$competition->is_active)
                                                <span class="badge bg-dark">Disabled</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <div class="btn-group" role="group">
                                                <a href="{{ route('admin.competitions.show', $competition) }}" 
                                                   class="btn btn-sm btn-outline-info" title="View">
                                                    <i class="ti ti-eye"></i>
                                                </a>
                                                <a href="{{ route('admin.competitions.edit', $competition) }}" 
                                                   class="btn btn-sm btn-outline-primary" title="Edit">
                                                    <i class="ti ti-edit"></i>
                                                </a>
                                                <button type="button" class="btn btn-sm btn-outline-danger" 
                                                        data-bs-toggle="modal" data-bs-target="#deleteModal"
                                                        data-action="{{ route('admin.competitions.destroy', $competition) }}"
                                                        data-name="{{ $competition->name }}" title="Delete">
                                                    <i class="ti ti-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center text-muted py-4">
                                            No competitions found. <a href="{{ route('admin.competitions.create') }}">Create the first one</a>.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if($competitions->hasPages())
                        <div class="mt-3">
                            {{ $competitions->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Delete Competition</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" id="deleteForm">
                    @csrf
                    @method('DELETE')
                    <div class="modal-body">
                        <p class="mb-0">Are you sure you want to delete <strong id="deleteName"></strong>?</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger">Delete</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const deleteModal = document.getElementById('deleteModal');
                deleteModal?.addEventListener('show.bs.modal', function (event) {
                    const button = event.relatedTarget;
                    document.getElementById('deleteForm').action = button.getAttribute('data-action');
                    document.getElementById('deleteName').textContent = button.getAttribute('data-name');
                });
            });
        </script>
    @endpush
</x-admin-layout>

