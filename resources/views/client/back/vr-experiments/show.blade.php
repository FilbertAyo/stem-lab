<x-app-layout>
    @php($subjects = $subjects ?? collect())

    {{-- Same sidebar as index: All Experiments + subject links (no global aside on vr-experiments.*) --}}
    <div class="col-12">
        <div class="row">
            <div class="col-lg-3 dashboard-sidebar mb-4 mb-lg-0">
                <ul class="category-list">
                    <li>
                        <a href="{{ route('client.vr-experiments.index') }}"
                           class="">
                            All Experiments <iconify-icon icon="lucide:chevron-right"></iconify-icon>
                        </a>
                    </li>
                    @foreach($subjects as $subject)
                        <li>
                            <a href="{{ route('client.vr-experiments.index', ['subject' => $subject->id]) }}"
                               class="{{ (string) $subject->id === (string) ($vrExperiment->subject_id ?? '') ? 'active' : '' }}">
                                {{ $subject->subject_name }} <iconify-icon icon="lucide:chevron-right"></iconify-icon>
                            </a>
                        </li>
                    @endforeach
                    <li>
                        <a href="{{ route('client.vr-experiments.math') }}"
                           class="{{ request()->routeIs('client.vr-experiments.math') ? 'active' : '' }}">
                            Mathematics <iconify-icon icon="lucide:chevron-right"></iconify-icon>
                        </a>
                    </li>
                </ul>
            </div>

            <div class="col-lg-9">

        @php($isStatic = $isStatic ?? false)
        <div class="dashboard-content-header d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
            <div>
                <h2 class="title mb-1">{{ $vrExperiment->name }}</h2>
                <p class="text-muted mb-0">
                    {{ $vrExperiment->subject->subject_name ?? 'General STEM Experiment' }}
                </p>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('client.vr-experiments.index') }}" class="badge bg-light-soft text-dark border d-flex align-items-center" style="font-size: 0.95rem;">
                   Back to library
                </a>
            </div>
        </div>

        <section class="row">
            <!-- VR Experiment Preview Column -->
            <div class="col-lg-8 col-12 mb-4">
                @if($isStatic && !empty($vrExperiment->use_iframe))
                    {{-- Static experiment with iframe (like PhET): try embedding; fallback link if blocked --}}
                    <div id="vr-iframe-container" class="position-relative mb-2" style="width: 100%; padding-bottom: 56.25%; height: 0; overflow: hidden; border-radius: 0.5rem; box-shadow: 0 0.125rem 0.25rem rgba(0,0,0,0.075);">
                        <iframe id="vr-experiment-iframe" src="{{ $vrExperiment->external_url }}"
                                class="position-absolute top-0 start-0 w-100 h-100"
                                style="border: none;"
                                allowfullscreen>
                        </iframe>
                    </div>
                    <p class="text-muted small mb-0">
                        <a href="{{ $vrExperiment->external_url }}" class="text-decoration-none">
                            <i class="bi bi-box-arrow-up-right me-1"></i>Open experience
                        </a>
                        @if(empty($vrExperiment->launch_url))
                            — If the experience doesn’t load above, use this link and click “Launch experiment” on their page.
                        @endif
                    </p>
                @elseif($isStatic)
                    {{-- Static experiment: preview image + open in new tab (no iframe) --}}
                    <div class="position-relative mb-4 rounded overflow-hidden" style="box-shadow: 0 0.125rem 0.25rem rgba(0,0,0,0.075);">
                        @if(!empty($vrExperiment->image))
                            <img src="{{ asset($vrExperiment->image) }}"
                                 alt="{{ $vrExperiment->name }}"
                                 class="w-100"
                                 style="aspect-ratio: 16/9; object-fit: cover;">
                        @else
                            <div class="d-flex flex-column justify-content-center align-items-center bg-primary py-5"
                                 style="aspect-ratio: 16/9;">
                                <i class="bi bi-vr text-white" style="font-size: 5rem;"></i>
                                <span class="text-white-50 mt-3 fs-5">Virtual Reality Experience</span>
                            </div>
                        @endif
                    </div>
                @else
                    {{-- DB experiment: embed iframe when allowed --}}
                    <div id="vr-iframe-container" class="position-relative mb-4" style="width: 100%; padding-bottom: 56.25%; height: 0; overflow: hidden; border-radius: 0.5rem; box-shadow: 0 0.125rem 0.25rem rgba(0,0,0,0.075);">
                        @if($vrExperiment->external_url)
                            <iframe id="vr-experiment-iframe" src="{{ $vrExperiment->external_url }}"
                                    class="position-absolute top-0 start-0 w-100 h-100"
                                    style="border: none;"
                                    allowfullscreen>
                            </iframe>
                        @else
                            <div class="position-absolute top-0 start-0 w-100 h-100 d-flex flex-column justify-content-center align-items-center"
                                 style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                                <i class="bi bi-vr text-white" style="font-size: 5rem;"></i>
                                <span class="text-white-50 mt-3 fs-5">Virtual Reality Experience</span>
                                <span class="text-white-50 mt-2 small">No URL available</span>
                            </div>
                        @endif
                    </div>
                @endif

                <div class="card border-0 shadow-none">
                    <div class="card-body">
                        <h5 class="fw-semibold mb-3">About this experiment</h5>
                        <div class="text-muted mb-4">
                            {!! nl2br(e(trim($vrExperiment->description ?? 'No description has been provided for this VR experiment yet.'))) !!}
                        </div>

                        <div class="row g-3">
                            <div class="col-sm-6">
                                <div class="p-3 bg-light-soft h-100">
                                    <span class="text-muted text-uppercase small d-block mb-1">Subject</span>
                                    <span class="fw-semibold">{{ $vrExperiment->subject->subject_name ?? 'General' }}</span>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="p-3 bg-light-soft h-100">
                                    <span class="text-muted text-uppercase small d-block mb-1">Added</span>
                                    <span class="fw-semibold">{{ isset($vrExperiment->created_at) ? (\Carbon\Carbon::parse($vrExperiment->created_at)->format('M d, Y')) : '—' }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Launch Panel Column -->
            <div class="col-lg-4 col-12 mb-4">
                <div class="card border-0 shadow-none">
                    <div class="card-header bg-gradient text-white">
                        <h5 class="fw-semibold mb-0">
                            {{ $isStatic ? 'Open experience' : 'View full page' }}
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="text-center py-4">
                            <i class="bi bi-arrow-up-right-circle-fill fs-12 text-primary"></i>
                            <h5 class="fw-semibold mb-2">Ready to explore?</h5>
                            <p class="text-muted small mb-4">
                                This browser-based interactive experience will help you understand
                                {{ $vrExperiment->subject->subject_name ?? 'STEM' }} concepts through hands-on simulation.
                            </p>

                            @if($vrExperiment->external_url)
                                <div class="d-grid gap-2 text-center">
                                    @if($isStatic)
                                        <a href="{{ $vrExperiment->external_url }}"
                                           class="btn btn-primary btn-sm text-white text-decoration-none">
                                            <i class="bi bi-box-arrow-up-right me-2"></i>Open experience
                                        </a>
                                    @else
                                        <button type="button"
                                                id="fullscreen-btn"
                                                class="btn btn-primary btn-sm text-white">
                                            View full page
                                        </button>
                                    @endif
                                </div>
                            @else
                                <div class="alert alert-warning mb-0">
                                    <i class="bi bi-exclamation-triangle me-2"></i>
                                    This VR experience is not yet available. Please check back later.
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- Requirements/Tips Section --}}
                    <div class="card-footer bg-light-soft">
                        <h6 class="fw-semibold mb-2">
                            <i class="bi bi-info-circle me-1"></i>Tips for best experience
                        </h6>
                        <ul class="small text-muted mb-0 ps-3">
                            <li>Use a modern web browser (Chrome, Firefox, Edge)</li>
                            <li>Ensure stable internet connection</li>
                            <li>Allow browser permissions if prompted</li>
                            <li>Use headphones for audio guidance</li>
                            <li>Full screen mode recommended for immersion</li>
                        </ul>
                    </div>
                </div>
            </div>
        </section>
            </div>
        </div>
    </div>

    @push('scripts')
    @if(!$isStatic || !empty($vrExperiment->use_iframe))
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const fullscreenBtn = document.getElementById('fullscreen-btn');
            const iframeContainer = document.getElementById('vr-iframe-container');
            const iframe = document.getElementById('vr-experiment-iframe');

            if (fullscreenBtn && iframeContainer) {
                fullscreenBtn.addEventListener('click', function() {
                    // Try to request fullscreen on the iframe container
                    if (iframeContainer.requestFullscreen) {
                        iframeContainer.requestFullscreen().catch(err => {
                            console.error('Error attempting to enable fullscreen:', err);
                            // Fallback: try to request fullscreen on the iframe itself
                            if (iframe && iframe.requestFullscreen) {
                                iframe.requestFullscreen().catch(err => {
                                    console.error('Error attempting to enable iframe fullscreen:', err);
                                });
                            }
                        });
                    } else if (iframeContainer.webkitRequestFullscreen) {
                        // Safari support
                        iframeContainer.webkitRequestFullscreen();
                    } else if (iframeContainer.mozRequestFullScreen) {
                        // Firefox support
                        iframeContainer.mozRequestFullScreen();
                    } else if (iframeContainer.msRequestFullscreen) {
                        // IE/Edge support
                        iframeContainer.msRequestFullscreen();
                    } else if (iframe && iframe.requestFullscreen) {
                        // Fallback to iframe
                        iframe.requestFullscreen();
                    }
                });

                // Update button text when exiting fullscreen
                document.addEventListener('fullscreenchange', updateButtonText);
                document.addEventListener('webkitfullscreenchange', updateButtonText);
                document.addEventListener('mozfullscreenchange', updateButtonText);
                document.addEventListener('MSFullscreenChange', updateButtonText);

                function updateButtonText() {
                    const isFullscreen = document.fullscreenElement || 
                                        document.webkitFullscreenElement || 
                                        document.mozFullScreenElement || 
                                        document.msFullscreenElement;
                    
                    if (isFullscreen) {
                        fullscreenBtn.innerHTML = '<i class="bi bi-fullscreen-exit me-2"></i>Exit full page';
                    } else {
                        fullscreenBtn.innerHTML = '<i class="bi bi-fullscreen me-2"></i>View full page';
                    }
                }
            }
        });
    </script>
    @endif
    @endpush
</x-app-layout>

