<x-app-layout>
    <div class="col-lg-9">

        <div class="dashboard-content-header">
            <h4 class="mb-0">My Club</h4>
           
        </div>

        @include('elements.client-validation')

        @if ($errors->any())
            <div class="alert alert-dismissible fade show text-danger-emphasis bg-danger-subtle border border-danger-subtle" role="alert">
                <ul class="mb-0 ps-3">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if($isLinked && $match)
            @include('client.back.student-club.partials.linked-club-card', ['match' => $match])
        @else
            <div class="card border shadow-none">
                <div class="card-body p-4 p-md-5">
                    <div class="row g-4 align-items-start">
                        <div class="col-lg-7">
                            <h5 class="fw-semibold mb-2">Find your school club</h5>
                            <p class="text-muted small mb-4">
                                Enter the <strong>admission number</strong> your teacher gave you and your school’s
                                <strong>club code</strong>. If your school is a registered public secondary school,
                                linking your club unlocks free platform access.
                            </p>

                            <form method="POST" action="{{ route('client.student-club.lookup') }}" class="d-flex flex-column gap-3">
                                @csrf

                                <div>
                                    <label for="admission_number" class="form-label">Admission number</label>
                                    <input
                                        type="text"
                                        name="admission_number"
                                        id="admission_number"
                                        class="form-control @error('admission_number') is-invalid @enderror"
                                        value="{{ old('admission_number') }}"
                                       
                                        maxlength="8"
                                        autocapitalize="characters"
                                        autocomplete="off"
                                        required
                                    >
                                    @error('admission_number')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div>
                                    <label for="club_code" class="form-label">Club code</label>
                                    <input
                                        type="text"
                                        name="club_code"
                                        id="club_code"
                                        class="form-control @error('club_code') is-invalid @enderror"
                                        value="{{ old('club_code') }}"
                                        maxlength="8"
                                        autocapitalize="characters"
                                        autocomplete="off"
                                        required
                                    >
                                    @error('club_code')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="form-text">Ask your STEM club teacher for this code.</div>
                                </div>

                                <div>
                                    <button type="submit" class="btn btn-primary d-inline-flex align-items-center gap-2">
                                        <iconify-icon icon="lucide:search" class="fs-5"></iconify-icon>
                                        <span>Find my club</span>
                                    </button>
                                </div>
                            </form>
                        </div>

                        <div class="col-lg-5">
                            <div class="bg-light rounded p-4 h-100">
                                <h6 class="fw-semibold mb-2">Public secondary school — free access</h6>
                                <p class="text-muted small mb-3">
                                    Students at registered public secondary schools can use the platform at no cost
                                    when they verify membership in their school STEM club.
                                </p>
                                <a href="{{ route('dashboard') }}#platform-subscription" class="small text-decoration-none">
                                    Learn more on your dashboard
                                    <iconify-icon icon="lucide:arrow-right" class="fs-6"></iconify-icon>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>
</x-app-layout>
