<div class="card h-100 border-0 shadow-sm hover-shadow-lg transition-all">
    <div class="rounded-top position-relative" style="overflow: hidden; height: 220px;">
        @if($experiment->thumbnail)
            <img src="{{ asset('storage/' . $experiment->thumbnail) }}"
                 alt="{{ $experiment->name }} thumbnail"
                 class="w-100 h-100"
                 style="object-fit: cover;"
                 loading="lazy"
                 decoding="async">
        @else
            <div class="d-flex flex-column justify-content-center align-items-center bg-primary h-100 w-100">
               
                <span class="text-white-50 small mt-2">VR Experience</span>
            </div>
        @endif

        {{-- VR badge --}}
        <span class="position-absolute top-0 end-0 m-2 badge bg-dark bg-opacity-75">
            <i class="bi bi-vr me-1"></i>VR
        </span>
    </div>

    <div class="card-body d-flex flex-column">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <span class="badge bg-light text-dark">
                <i class="bi bi-book me-1"></i>
                {{ $experiment->subject->subject_name ?? 'General' }}
            </span>
            <small class="text-muted">
                {{ $experiment->created_at?->format('M d, Y') }}
            </small>
        </div>

        <h5 class="fw-semibold">{{ $experiment->name }}</h5>
        <p class="text-muted small">
            {{ Str::limit($experiment->description ?? 'No description provided yet.', 60) }}
        </p>
        <div class="mt-auto d-flex justify-content-between align-items-center">
            @if($experiment->external_url)
                <span class="text-muted small">
                    <i class="bi bi-box-arrow-up-right me-1"></i>External VR
                </span>
            @else
                <span class="text-muted small">
                    <i class="bi bi-vr me-1"></i>VR Experience
                </span>
            @endif
            <a href="{{ route('client.vr-experiments.show', $experiment) }}" class="btn btn-primary btn-sm">
                View VR
            </a>
        </div>
    </div>
</div>

