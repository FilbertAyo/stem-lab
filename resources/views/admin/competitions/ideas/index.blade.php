<x-admin-layout>
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h5 class="mb-0">Ideas - {{ $competition->name }}</h5>
                    <p class="text-muted mb-0">Regional scoring and shortlist decisions.</p>
                </div>
                <a href="{{ route('admin.competitions.show', $competition) }}" class="btn btn-outline-secondary">Back</a>
            </div>

            
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover table-bordered align-middle">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>School</th>
                                    <th>Idea</th>
                                    <th>Region</th>
                                    <th>Avg Score</th>
                                    <th>Shortlist</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($ideas as $idea)
                                    @php($avgScore = round((float) $idea->scores->avg('total_score'), 2))
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>
                                            <div class="fw-semibold">{{ $idea->registration?->resolvedSchoolName() ?? 'N/A' }}</div>
                                            <small class="text-muted">{{ $idea->registration?->teacher_name }}</small>
                                        </td>
                                        <td>
                                            <div>{{ $idea->problem_title ?: 'Untitled' }}</div>
                                            <small class="text-muted">{{ ucfirst($idea->status) }}</small>
                                        </td>
                                        <td>{{ $idea->communityRegion?->name ?? $idea->registration?->region?->name ?? 'N/A' }}</td>
                                        <td>{{ $avgScore ?: '—' }}</td>
                                        <td>
                                            @if($idea->shortlist)
                                                <span class="badge bg-{{ $idea->shortlist->status === 'shortlisted' ? 'success' : ($idea->shortlist->status === 'waitlist' ? 'warning' : 'danger') }}">
                                                    {{ ucfirst($idea->shortlist->status) }}
                                                </span>
                                            @else
                                                <span class="badge bg-light text-dark">Pending</span>
                                            @endif
                                        </td>
                                        <td class="text-nowrap">
                                            <a href="{{ route('admin.competitions.ideas.show', [$competition, $idea]) }}" class="btn btn-sm btn-outline-secondary">View</a>
                                        </td>
                                    </tr>

                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center text-muted py-4">No ideas yet.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-3">{{ $ideas->links() }}</div>
                </div>
            </div>
        </div>
    </div>
</x-admin-layout>
