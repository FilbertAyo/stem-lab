@php
    $club = $match->club;
    $school = $match->school;
    $student = $match->student;
    $leader = $match->leader;
@endphp

<div class="card border shadow-none mb-4">
    <div class="card-body">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
            <div>
                <span class="badge bg-success-subtle text-success-emphasis border border-success-subtle mb-2">
                    Public secondary school — free access
                </span>
                <h5 class="mb-1">{{ $club->name }}</h5>
                @if($school)
                    <p class="text-muted mb-0 small">{{ $school->name }}</p>
                @endif
            </div>
        </div>

        <div class="row g-3">
            @if($club->image_path)
                <div class="col-md-4">
                    <img src="{{ asset('storage/' . $club->image_path) }}"
                         alt="{{ $club->name }}"
                         class="w-100 rounded"
                         style="max-height: 180px; object-fit: cover;"
                         loading="lazy">
                </div>
            @endif
            <div class="{{ $club->image_path ? 'col-md-8' : 'col-12' }}">
                @if($club->description)
                    <p class="text-muted mb-3">{{ $club->description }}</p>
                @endif

                <dl class="row mb-0 small">
                    <dt class="col-sm-4 text-muted">Your name on roster</dt>
                    <dd class="col-sm-8 mb-2">{{ $student->name }}</dd>

                    @if($student->schoolClass)
                        <dt class="col-sm-4 text-muted">Class</dt>
                        <dd class="col-sm-8 mb-2">{{ $student->schoolClass->name }}</dd>
                    @endif

                    @if($school)
                        <dt class="col-sm-4 text-muted">School</dt>
                        <dd class="col-sm-8 mb-2">{{ $school->name }}</dd>

                        @php
                            $regionName = $school->regionModel?->name ?? $school->region;
                            $districtRegion = collect([$school->district, $regionName])->filter()->implode(', ');
                        @endphp
                        @if($districtRegion !== '')
                            <dt class="col-sm-4 text-muted">District / region</dt>
                            <dd class="col-sm-8 mb-2">{{ $districtRegion }}</dd>
                        @endif

                        @if($school->latitude !== null && $school->longitude !== null)
                            <dt class="col-sm-4 text-muted">Location</dt>
                            <dd class="col-sm-8 mb-2">
                                <a href="https://www.google.com/maps?q={{ $school->latitude }},{{ $school->longitude }}" target="_blank" rel="noopener noreferrer">
                                    {{ $school->latitude }}, {{ $school->longitude }}
                                </a>
                            </dd>
                        @endif
                    @endif

                    <dt class="col-sm-4 text-muted">Club code</dt>
                    <dd class="col-sm-8 mb-2"><code>{{ $match->clubCode }}</code></dd>

                    @if($leader)
                        <dt class="col-sm-4 text-muted">Club leader</dt>
                        <dd class="col-sm-8 mb-2">
                            {{ $leader->name }}
                            @if($leader->email)
                                <span class="text-muted d-block">{{ $leader->email }}</span>
                            @endif
                        </dd>
                    @endif
                </dl>
            </div>
        </div>

        <div class="alert alert-light border mt-4 mb-0 small" role="status">
            <iconify-icon icon="lucide:info" class="me-1"></iconify-icon>
            You can view your club details here. Club management is handled by your teacher.
        </div>
    </div>
</div>
