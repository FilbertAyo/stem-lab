@php
    $club = $match->club;
    $school = $match->school;
    $student = $match->student;
    $leader = $match->leader;
@endphp

<x-app-layout>
    <div class="col-lg-9">

        <div class="dashboard-content-header">
        <h4 class="mb-1 mt-1">Confirm your club</h4>
            <a href="{{ route('client.student-club.index') }}" class="text-muted small text-decoration-none">
                <i class="bi bi-arrow-left me-1"></i>Back 
            </a>
           
      </div>

        @include('elements.client-validation')

        <div class="card border shadow-none mb-4">
            <div class="card-body p-4 p-md-5">
                <div class="alert alert-success border-0 bg-success-subtle text-success-emphasis mb-4" role="status">
                    <strong>We found you on the club roster.</strong>
                    Is this your school club membership?
                </div>

                <div class="row g-4">
                    <div class="col-md-6">
                        <h6 class="text-muted text-uppercase small fw-semibold mb-3">Your roster details</h6>
                        <dl class="row mb-0 small">
                            <dt class="col-sm-5 text-muted">Name</dt>
                            <dd class="col-sm-7 mb-2 fw-semibold">{{ $student->name }}</dd>

                            @if($student->schoolClass)
                                <dt class="col-sm-5 text-muted">Class</dt>
                                <dd class="col-sm-7 mb-2">{{ $student->schoolClass->name }}</dd>
                            @endif

                            @if($student->gender)
                                <dt class="col-sm-5 text-muted">Gender</dt>
                                <dd class="col-sm-7 mb-2 text-capitalize">{{ $student->gender }}</dd>
                            @endif

                            @if($student->date_of_birth)
                                <dt class="col-sm-5 text-muted">Date of birth</dt>
                                <dd class="col-sm-7 mb-2">{{ $student->date_of_birth->format('d M Y') }}</dd>
                            @endif

                            <dt class="col-sm-5 text-muted">Admission number</dt>
                            <dd class="col-sm-7 mb-0"><code>{{ $student->admission_number }}</code></dd>
                        </dl>
                    </div>

                    <div class="col-md-6">
                        <h6 class="text-muted text-uppercase small fw-semibold mb-3">School club</h6>
                        <dl class="row mb-0 small">
                            <dt class="col-sm-5 text-muted">Club</dt>
                            <dd class="col-sm-7 mb-2 fw-semibold">{{ $club->name }}</dd>

                            @if($school)
                                <dt class="col-sm-5 text-muted">School</dt>
                                <dd class="col-sm-7 mb-2">{{ $school->name }}</dd>
                            @endif

                            <dt class="col-sm-5 text-muted">Club code</dt>
                            <dd class="col-sm-7 mb-2"><code>{{ $match->clubCode }}</code></dd>

                            @if($leader)
                                <dt class="col-sm-5 text-muted">Club leader</dt>
                                <dd class="col-sm-7 mb-0">{{ $leader->name }}</dd>
                            @endif
                        </dl>
                    </div>
                </div>

                @if($club->image_path)
                    <div class="mt-4">
                        <img src="{{ asset('storage/' . $club->image_path) }}"
                             alt="{{ $club->name }}"
                             class="rounded"
                             style="max-height: 140px; object-fit: cover;"
                             loading="lazy">
                    </div>
                @endif

                <div class="d-flex flex-wrap gap-2 mt-4 pt-4 border-top">
                    <form method="POST" action="{{ route('client.student-club.link') }}">
                        @csrf
                        <button type="submit" class="btn btn-primary d-inline-flex align-items-center gap-2">
                            <iconify-icon icon="lucide:check" class="fs-5"></iconify-icon>
                            <span>Yes, link my account</span>
                        </button>
                    </form>
                    <a href="{{ route('client.student-club.index') }}" class="btn btn-outline-secondary">
                        Not me — search again
                    </a>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
