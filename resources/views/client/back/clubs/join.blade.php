<x-app-layout>
    <div class="page-heading">
        <div>
            <h3 class="mb-1">Join {{ $club->name }}</h3>
            <p class="text-muted mb-0">Please provide your school details to complete the registration.</p>
        </div>
    </div>

    <div class="page-content">
        <section class="row">
            <div class="col-12 col-lg-12 col-xl-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-4">
                     
                        <div class="mb-4">
                            <h5 class="fw-semibold mb-2">{{ $club->name }}</h5>
                            @if($club->description)
                                <p class="text-muted mb-0">{{ $club->description }}</p>
                            @endif
                        </div>

                        <hr class="my-4">

                        <form method="POST" action="{{ route('client.clubs.join', $club) }}">
                            @csrf

                            <div class="mb-3">
                                <label for="school_name" class="form-label">
                                    School Name <span class="text-danger">*</span>
                                </label>
                                <input type="text"
                                       class="form-control @error('school_name') is-invalid @enderror"
                                       id="school_name"
                                       name="school_name"
                                       value="{{ old('school_name') }}"
                                       required>
                                @error('school_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="school_address" class="form-label">School Address</label>
                                <textarea class="form-control @error('school_address') is-invalid @enderror"
                                          id="school_address"
                                          name="school_address"
                                          rows="3">{{ old('school_address') }}</textarea>
                                @error('school_address')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="school_phone" class="form-label">School Phone</label>
                                    <input type="text"
                                           class="form-control @error('school_phone') is-invalid @enderror"
                                           id="school_phone"
                                           name="school_phone"
                                           value="{{ old('school_phone') }}">
                                    @error('school_phone')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="school_email" class="form-label">School Email</label>
                                    <input type="email"
                                           class="form-control @error('school_email') is-invalid @enderror"
                                           id="school_email"
                                           name="school_email"
                                           value="{{ old('school_email') }}">
                                    @error('school_email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="school_district" class="form-label">District</label>
                                    <input type="text"
                                           class="form-control @error('school_district') is-invalid @enderror"
                                           id="school_district"
                                           name="school_district"
                                           value="{{ old('school_district') }}">
                                    @error('school_district')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="school_region" class="form-label">Region</label>
                                    <input type="text"
                                           class="form-control @error('school_region') is-invalid @enderror"
                                           id="school_region"
                                           name="school_region"
                                           value="{{ old('school_region') }}">
                                    @error('school_region')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="d-flex gap-2 mt-4">
                                <a href="{{ route('client.clubs.index') }}" class="btn btn-light">
                                    <i class="bi bi-arrow-left me-1"></i>
                                    Cancel
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-check-circle me-1"></i>
                                    Join Club
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </section>
    </div>
</x-app-layout>

