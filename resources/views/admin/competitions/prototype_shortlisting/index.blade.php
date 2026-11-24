<x-admin-layout>
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h5 class="mb-0">Prototype Shortlisting - {{ $competition->name }}</h5>
                    <p class="text-muted mb-0">Review submitted prototypes and shortlist the best teams.</p>
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
                    @if($submissions->isEmpty())
                        <div class="text-center text-muted py-4">No submitted prototypes found yet.</div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover table-bordered align-middle">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>School</th>
                                        <th>Prototype Title</th>
                                        <th>Submitted</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($submissions as $submission)
                                        @php
                                            $shortlist = $submission->prototypeShortlist;
                                            $status = $shortlist?->status ?? null;
                                            $badgeClass = $status === 'shortlisted'
                                                ? 'success'
                                                : ($status === 'waitlist'
                                                    ? 'warning'
                                                    : ($status === 'rejected' ? 'danger' : 'light'));
                                        @endphp
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td style="min-width: 180px;">
                                                <div class="fw-semibold">{{ $submission->registration?->resolvedSchoolName() ?? '—' }}</div>
                                                <small class="text-muted">{{ $submission->registration?->teacher_name ?? '—' }}</small>
                                            </td>
                                            <td style="min-width: 240px;">
                                                <div class="fw-semibold">{{ $submission->prototype_title ?: 'Untitled Prototype' }}</div>
                                                <div class="small text-muted">
                                                    Idea: {{ $submission->idea?->problem_title ?: '—' }}
                                                </div>
                                            </td>
                                            <td style="min-width: 170px;">
                                                <div>{{ $submission->submitted_at?->format('d M Y, H:i') ?? '—' }}</div>
                                            </td>
                                            <td style="min-width: 160px;">
                                                <span class="badge bg-{{ $badgeClass }}">
                                                    {{ $shortlist?->status ? ucfirst($shortlist->status) : 'Pending' }}
                                                </span>
                                            </td>
                                            <td style="min-width: 120px;">
                                                <x-admin.table-actions :actions="[
                                                    [
                                                        'type' => 'link',
                                                        'label' => 'View',
                                                        'icon' => 'ti ti-eye',
                                                        'url' => route('admin.competitions.prototype-shortlisting.show', [$competition, $submission]),
                                                    ],
                                                ]" />
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

