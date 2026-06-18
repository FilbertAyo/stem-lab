<div class="col">
    <a href="{{ route('client.projects.show', $project) }}"
       class="card h-100 text-decoration-none text-dark border">
        <div class="ratio ratio-16x10 overflow-hidden" style="height: 190px;">
            @if($project->cover_image)
                <img src="{{ Storage::url($project->cover_image) }}"
                     alt="{{ $project->name }}"
                     class="object-fit-cover"
                     loading="lazy">
            @else
                <div class="d-flex align-items-center justify-content-center text-secondary" style="height: 200px;">
                    <iconify-icon icon="lucide:lightbulb" width="48" height="48"></iconify-icon>
                </div>
            @endif
        </div>
        <div class="card-body d-flex flex-column">
            <h3 class="card-title h6 fw-bold mb-2">{{ $project->name }}</h3>
            <p class="card-text small text-muted mb-2 flex-grow-1">
                {{ Str::limit($project->description ?? 'No description provided yet.', 100) }}
            </p>
            <div class="d-flex flex-wrap gap-1 mb-2">
                <span class="badge bg-light bg-opacity-10 text-dark border text-capitalize">{{ $project->difficulty }}</span>

                @foreach($project->platforms->take(3) as $platform)
                    <span class="badge bg-light bg-opacity-10 text-dark border">{{ $platform->name }}</span>
                @endforeach
                @if($project->platforms->count() > 3)
                    <span class="badge bg-light bg-opacity-10 text-dark border">+{{ $project->platforms->count() - 3 }}</span>
                @endif
            </div>
        </div>
    </a>
</div>
