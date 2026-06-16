<div class="card h-100 border shadow-none hover-shadow-lg transition-all">
    <div class="position-relative" style="overflow: hidden; height: 190px;">
        @if($model->thumbnail)
            <img src="{{ asset('storage/' . $model->thumbnail) }}"
                 alt="{{ $model->name }} thumbnail"
                 class="w-100 h-100"
                 style="object-fit: cover;"
                 loading="lazy"
                 decoding="async">
        @else
            <div class="d-flex flex-column justify-content-center align-items-center bg-light h-100 w-100">
                <i class="bi bi-cube text-secondary fs-1"></i>
                <span class="text-muted small mt-2">Thumbnail coming soon</span>
            </div>
        @endif

        {{-- Questions badge --}}
        @if($model->questions_count > 0)
            <span class="position-absolute top-0 end-0 m-2 badge bg-dark bg-opacity-75">
                <i class="bi bi-question-circle me-1"></i>{{ $model->questions_count }} questions
            </span>
        @endif
    </div>

    <div class="card-body d-flex flex-column">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <span class="badge border text-dark">
                <i class="bi bi-book me-1"></i>
                {{ $model->subject->subject_name ?? 'General' }}
            </span>
            <small class="text-muted">
                {{ $model->created_at?->format('M d, Y') }}
            </small>
        </div>

        <h5 class="fw-semibold">{{ $model->name }}</h5>
        <!-- <p class="text-muted small">
            {{ Str::limit($model->description ?? 'No description provided yet.', 60) }}
        </p> -->
        <div class="mt-auto d-flex justify-content-between align-items-center">
            <a href="#" target="_blank" class="text-muted small">
                <i class="bi bi-box-arrow-up-right me-1"></i>
            </a>
            <a href="{{ route('client.models.3d.show', $model) }}" class="btn btn-primary">
                View model
            </a>
        </div>
    </div>
</div>

