@php
    $hasSteps = $project->steps->isNotEmpty();
    $hasCircuit = $project->circuit_image;
    $hasGuide = (bool) $project->guide;
    $hasCodes = $project->codes->isNotEmpty();
    $hasEmbeddedCode = !$hasCodes && (bool) $project->embedded_code;
    $hasResources = $project->resources->isNotEmpty();
    $hasEquipment = $project->equipment->count() > 0;
    $hasTags = $project->tags->count() > 0;
    $hasCategories = $categories->count() > 0;
@endphp

<x-app-layout>
    <div class="col-lg-12 project-show">
        {{-- Breadcrumb + back --}}
        <nav class="project-show__breadcrumb mb-3" aria-label="Breadcrumb">
            <a href="{{ route('client.projects.index') }}" class="text-muted text-decoration-none small">
                <iconify-icon icon="lucide:arrow-left" class="me-1"></iconify-icon>Projects
            </a>
            @if($project->category)
                <span class="text-muted mx-1">/</span>
                <a href="{{ route('client.projects.index', ['category' => $project->category->slug]) }}" class="text-muted text-decoration-none small">
                    {{ $project->category->name }}
                </a>
            @endif
        </nav>

        <div class="dashboard-content-header d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
            <div class="flex-grow-1">
                <h1 class="project-show__title mb-2">{{ $project->name }}</h1>
                <div class="project-show__meta d-flex flex-wrap align-items-center gap-2">
                    @if($project->category)
                        <span class="badge bg-primary bg-opacity-10 text-primary">{{ $project->category->name }}</span>
                    @endif
                    <span class="badge bg-light bg-opacity-10 text-dark border text-capitalize">{{ $project->difficulty }}</span>
                    @if($project->estimated_time)
                        <span class="badge bg-light bg-opacity-10 text-dark border">{{ $project->estimated_time }} min</span>
                    @endif
                    @foreach($project->platforms as $platform)
                        <span class="badge bg-light bg-opacity-10 text-dark border">{{ $platform->name }}</span>
                    @endforeach
                </div>
                @if($project->youtube_url || $project->external_link)
                    <div class="d-flex flex-wrap gap-2 mt-2">
                        @if($project->youtube_url)
                            <a href="{{ $project->youtube_url }}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-danger">
                                <i class="bi bi-youtube me-1"></i>Watch video
                            </a>
                        @endif
                        @if($project->external_link)
                            <a href="{{ $project->external_link }}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-link-45deg me-1"></i>External link
                            </a>
                        @endif
                    </div>
                @endif
            </div>
            <a href="{{ route('client.projects.index') }}">
                <iconify-icon icon="lucide:layout-list" class="me-1"></iconify-icon>All projects
            </a>
        </div>

        <div class="row">
            {{-- Main content --}}
            <div class="col-lg-8 mb-4">
                {{-- Cover + overview in one card when there's a description or cover --}}
                @if($project->cover_image || $project->description)
                    <section class="card border shadow-none mb-4 project-show__section">
                        @if($project->cover_image)
                            <div class="project-show__cover">
                                <img src="{{ Storage::url($project->cover_image) }}"
                                     alt="{{ $project->name }}"
                                     loading="eager">
                            </div>
                        @endif
                        @if($project->description)
                            <div class="card-body">
                                <h2 class="h5 fw-semibold mb-2">Overview</h2>
                                <p class="text-muted mb-0">{{ $project->description }}</p>
                            </div>
                        @endif
                    </section>
                @endif

                @if($hasSteps)
                    <section class="card border shadow-none mb-4 project-show__section" id="section-steps">
                        <div class="card-header border-0 py-3">
                            <h2 class="h5 fw-semibold mb-0">
                              Steps
                            </h2>
                        </div>
                        <div class="card-body pt-0">
                            @foreach($project->steps as $step)
                                <div class="project-show__step border rounded p-3 mb-3">
                                    <div class="d-flex align-items-start gap-3">
                                        <span class="project-show__step-num">{{ $step->step_number }}</span>
                                        <div class="flex-grow-1 min-w-0">
                                            <h3 class="h6 fw-semibold mb-1">{{ $step->title }}</h3>
                                            @if($step->description)
                                                <p class="text-muted small mb-2">{{ $step->description }}</p>
                                            @endif
                                            @if($step->image)
                                                <img src="{{ Storage::url($step->image) }}" alt="Step {{ $step->step_number }}" class="img-fluid rounded mt-2" style="max-height: 220px;">
                                            @endif
                                            @if($step->code_snippet)
                                                <pre class="project-show__snippet text-white">{{ $step->code_snippet }}</pre>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </section>
                @endif

                @if($hasCircuit)
                    <section class="card border shadow-none mb-4 project-show__section" id="section-circuit">
                        <div class="card-header border-0 py-3">
                            <h2 class="h5 fw-semibold mb-0">
                                Circuit diagram
                            </h2>
                        </div>
                        <div class="card-body text-center">
                            <img src="{{ Storage::url($project->circuit_image) }}"
                                 alt="Circuit for {{ $project->name }}"
                                 class="img-fluid rounded"
                                 style="max-height: 420px; object-fit: contain;">
                        </div>
                    </section>
                @endif

                @if($hasGuide)
                    <section class="card border shadow-none mb-4 project-show__section" id="section-guide">
                        <div class="card-body">
                            <h2 class="h5 fw-semibold mb-3">Guide</h2>
                            <div class="project-guide content-body text-muted" style="white-space: pre-wrap;">{!! nl2br(e($project->guide)) !!}</div>
                        </div>
                    </section>
                @endif

                @if($hasCodes)
                    @foreach($project->codes as $idx => $code)
                        <section class="card border shadow-none mb-4 project-show__section" id="section-code-{{ $idx }}">
                            <div class="card-header border-0 py-3 d-flex flex-wrap justify-content-between align-items-center gap-2">
                                <h2 class="h5 fw-semibold mb-0">
                                    <i class="bi bi-code-slash me-2 text-primary"></i>{{ $code->title }}
                                    <span class="badge bg-secondary ms-1">{{ $code->language }}</span>
                                </h2>
                                <button type="button" class="btn btn-sm btn-outline-secondary copy-code-btn" data-target="codeBlock{{ $idx }}" title="Copy code" aria-label="Copy code">
                                    <i class="bi bi-clipboard"></i> Copy
                                </button>
                            </div>
                            <div class="card-body">
                                @if($code->description)
                                    <p class="text-muted small mb-2">{{ $code->description }}</p>
                                @endif
                                <div class="project-show__code-wrap">
                                    <pre id="codeBlock{{ $idx }}" class="project-show__code text-white">{{ $code->code }}</pre>
                                </div>
                            </div>
                        </section>
                    @endforeach
                @elseif($hasEmbeddedCode)
                    <section class="card border shadow-none mb-4 project-show__section" id="section-code">
                        <div class="card-header border-0 py-3 d-flex justify-content-between align-items-center">
                            <h2 class="h5 fw-semibold mb-0"><i class="bi bi-code-slash me-2 text-primary"></i>Code</h2>
                            <button type="button" id="copyCodeBtn" title="Copy code" aria-label="Copy code">
                                <i class="bi bi-clipboard"></i> 
                            </button>
                        </div>
                        <div class="card-body">
                            <div class="project-show__code-wrap">
                                <pre id="projectEmbeddedCode" class="project-show__code text-white">{{ $project->embedded_code }}</pre>
                            </div>
                        </div>
                    </section>
                @endif

                @push('scripts')
                <script>
                    (function () {
                        document.querySelectorAll('.copy-code-btn').forEach(function (btn) {
                            var targetId = btn.getAttribute('data-target');
                            var pre = targetId ? document.getElementById(targetId) : null;
                            if (!pre) return;
                            btn.addEventListener('click', function () {
                                var code = pre.textContent || '';
                                if (!code) return;
                                navigator.clipboard.writeText(code).then(function () {
                                    btn.innerHTML = '<i class="bi bi-check2"></i> Copied!';
                                    setTimeout(function () { btn.innerHTML = '<i class="bi bi-clipboard"></i> Copy'; }, 2000);
                                });
                            });
                        });
                        var copyCodeBtn = document.getElementById('copyCodeBtn');
                        var projectEmbeddedCode = document.getElementById('projectEmbeddedCode');
                        if (copyCodeBtn && projectEmbeddedCode) {
                            copyCodeBtn.addEventListener('click', function () {
                                var code = projectEmbeddedCode.textContent || '';
                                if (!code) return;
                                navigator.clipboard.writeText(code).then(function () {
                                    copyCodeBtn.innerHTML = '<i class="bi bi-check2"></i> Copied!';
                                    setTimeout(function () { copyCodeBtn.innerHTML = '<i class="bi bi-clipboard"></i> Copy'; }, 2000);
                                });
                            });
                        }
                    })();
                </script>
                @endpush
            </div>

            {{-- Sticky sidebar --}}
            <aside class="col-lg-4 mb-4 project-show__sidebar">
                @if($hasResources)
                    <div class="card border shadow-none mb-3">
                        <div class="card-header border-0 py-2">
                            <h3 class="h6 fw-semibold mb-0"><i class="bi bi-download me-1"></i>Resources</h3>
                        </div>
                        <div class="card-body p-0">
                            <ul class="list-group list-group-flush">
                                @foreach($project->resources as $res)
                                    <li class="list-group-item d-flex justify-content-between align-items-center border-0 py-2">
                                        <span class="small text-truncate me-2">{{ $res->title }}</span>
                                        @if($res->url)
                                            <a href="{{ $res->url }}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-primary flex-shrink-0" aria-label="Open"><i class="bi bi-box-arrow-up-right"></i></a>
                                        @elseif($res->file_path)
                                            <a href="{{ Storage::url($res->file_path) }}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-secondary flex-shrink-0" aria-label="Download"><i class="bi bi-download"></i></a>
                                        @endif
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                @endif

                @if($hasEquipment)
                        <div class="card border shadow-none mb-3">
                        <div class="card-header border-0 py-2">
                            <h3 class="h6 fw-semibold mb-0">Equipment</h3>
                        </div>
                        <div class="card-body p-0">
                            <table class="table table-sm table-hover align-middle mb-0">
                                <tbody>
                                    @foreach($project->equipment as $item)
                                        <tr>
                                            <td>{{ $item->name }}</td>
                                            <td class="text-muted small" style="width: 60px;">{{ $item->pivot->quantity ?? '—' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif

                @if($hasTags)
                    <div class="card border shadow-none mb-3">
                        <div class="card-header border-0 py-2">
                            <h3 class="h6 fw-semibold mb-0">Tags</h3>
                        </div>
                        <div class="card-body py-2">
                            <div class="d-flex flex-wrap gap-1">
                                @foreach($project->tags as $tag)
                                    <span class="badge bg-light bg-opacity-10 text-dark border">{{ $tag->name }}</span>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endif

                @if($hasCategories)
                        <div class="card border shadow-none">
                        <div class="card-header border-0 py-2">
                            <h3 class="h6 fw-semibold mb-0">Categories</h3>
                        </div>
                        <div class="card-body py-2">
                            <ul class="list-unstyled mb-0 small">
                                @foreach($categories as $category)
                                    <li class="mb-1">
                                        <a href="{{ route('client.projects.index', ['category' => $category->slug]) }}"
                                           class="d-flex justify-content-between align-items-center text-decoration-none {{ $activeCategory && $activeCategory->id === $category->id ? 'fw-semibold text-primary' : 'text-dark' }}">
                                            <span>{{ $category->name }}</span>
                                          
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                @endif
            </aside>
        </div>
    </div>

    @push('styles')
    <style>
        .project-show__breadcrumb { font-size: 0.9rem; }
        .project-show__title { font-size: 1.75rem; font-weight: 700; color: #1f2a2e; line-height: 1.3; }
        .project-show__meta .badge { font-size: 0.75rem; font-weight: 500; }
        .project-show__cover {
            aspect-ratio: 16 / 9;
            max-height: 380px;
            overflow: hidden;
            background: #f4f8fa;
        }
        .project-show__cover img { width: 100%; height: 100%; object-fit: cover; }
        .project-show__step-num {
            width: 32px; height: 32px; min-width: 32px;
            display: flex; align-items: center; justify-content: center;
            background: var(--bs-primary); color: #fff; border-radius: 50%;
            font-weight: 700; font-size: 0.9rem;
        }
        .project-show__snippet {
            background: #1f2a2e; color: #fff; padding: 0.75rem 1rem; border-radius: 8px;
            font-size: 0.8rem; white-space: pre-wrap; overflow-x: auto; margin: 0.5rem 0 0;
        }
        .project-show__code-wrap { max-height: 400px; overflow: auto; }
        .project-show__code {
            background: #1f2a2e; color: #fff; padding: 1rem; border-radius: 8px;
            font-size: 0.85rem; white-space: pre; overflow-x: auto; margin: 0;
        }
        .project-show__sidebar { position: relative; }
        @media (min-width: 992px) {
            .project-show__sidebar { position: sticky; top: 1.5rem; align-self: start; }
        }
        @media (max-width: 767px) {
            .project-show__title { font-size: 1.35rem; }
        }
    </style>
    @endpush
</x-app-layout>
