<x-admin-layout>
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h5 class="mb-0">Prototype Modification Review - {{ $competition->name }}</h5>
                    <p class="text-muted mb-0">Submitted modification forms ready for shortlisting.</p>
                </div>
                <div class="d-flex gap-2">
                    @if(filled($googleFormUrl))
                        <a href="{{ $googleFormUrl }}" target="_blank" rel="noopener noreferrer" class="btn btn-sm btn-outline-primary">
                            Open Google Form
                        </a>
                    @endif
                    <a href="{{ route('admin.competitions.show', $competition) }}" class="btn btn-outline-secondary btn-sm">Back</a>
                </div>
            </div>

            
            <div class="card">
                <div class="card-body">
                    @if($modifications->isEmpty())
                        <div class="text-center text-muted py-4">No submitted prototype modifications found yet.</div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover table-bordered align-middle">
                                <thead>
                                    <tr>
                                        <th>School</th>
                                        <th>Prototype Title</th>
                                        <th>Submitted</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($modifications as $modification)
                                        @php
                                            $shortlist = $modification->modificationShortlist;
                                            $status = $shortlist?->status;
                                            $badge = $status === 'shortlisted'
                                                ? 'success'
                                                : ($status === 'waitlist'
                                                    ? 'warning'
                                                    : ($status === 'rejected' ? 'danger' : 'light'));
                                        @endphp
                                        <tr>
                                            <td>
                                                <div class="fw-semibold">{{ $modification->registration?->resolvedSchoolName() ?? '—' }}</div>
                                                <small class="text-muted">{{ $modification->registration?->teacher_name ?? '—' }}</small>
                                            </td>
                                            <td>
                                                <div class="fw-semibold">{{ $modification->prototype_title ?: 'Untitled Prototype' }}</div>
                                                <div class="small text-muted">Idea: {{ $modification->idea?->problem_title ?: '—' }}</div>
                                            </td>
                                            <td>{{ $modification->submitted_at?->format('d M Y, H:i') ?? '—' }}</td>
                                            <td>
                                                <span class="badge bg-{{ $badge }}">{{ $status ? ucfirst($status) : 'Pending' }}</span>
                                            </td>
                                            <td>
                                                <a href="{{ route('admin.competitions.prototype-modification.show', [$competition, $modification]) }}"
                                                   class="btn btn-sm btn-outline-secondary">
                                                    View
                                                </a>
                                            </td>
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

