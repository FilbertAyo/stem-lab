<x-admin-layout>
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h5 class="mb-0">Pitch & Winner - {{ $competition->name }}</h5>
                    <p class="text-muted mb-0">Teams called for pitch (from shortlisted prototype modifications).</p>
                </div>
                <a href="{{ route('admin.competitions.show', $competition) }}" class="btn btn-outline-secondary btn-sm">Back</a>
            </div>

            <div class="card">
                <div class="card-body">
                    @if($calledTeams->isEmpty())
                        <div class="text-center text-muted py-4">No teams have been called for pitch yet.</div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover table-bordered align-middle">
                                <thead>
                                    <tr>
                                        <th>School</th>
                                        <th>Prototype</th>
                                        <th>Region</th>
                                        <th>Rank</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($calledTeams as $team)
                                        <tr>
                                            <td>
                                                <div class="fw-semibold">{{ $team->registration?->school_name ?? '—' }}</div>
                                                <small class="text-muted">{{ $team->registration?->teacher_name ?? '—' }}</small>
                                            </td>
                                            <td>
                                                <div class="fw-semibold">{{ $team->prototype_title ?: 'Untitled Prototype' }}</div>
                                                <small class="text-muted">Idea: {{ $team->idea?->problem_title ?: '—' }}</small>
                                            </td>
                                            <td>{{ $team->registration?->region?->name ?? '—' }}</td>
                                            <td>{{ $team->modificationShortlist?->rank ?? '—' }}</td>
                                            <td><span class="badge bg-success">Called for pitch</span></td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-admin-layout>

