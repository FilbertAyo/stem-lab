<x-app-layout>
    <div class="page-heading d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div>
            <h3 class="mb-1">{{ $vrExperiment->name }}</h3>
            <p class="text-muted mb-0">
                {{ $vrExperiment->subject->subject_name ?? 'General STEM Experiment' }}
            </p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('client.vr-experiments.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left me-1"></i> Back to library
            </a>
        </div>
    </div>

    <div class="page-content">
        <section class="row">
            <!-- VR Experiment Preview Column -->
            <div class="col-lg-8 col-12 mb-4">
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body p-0">
                        <div class="position-relative" style="min-height: 500px;">
                            @if($vrExperiment->external_url)
                                <iframe src="{{ $vrExperiment->external_url }}"
                                        class="w-100"
                                        height="500"
                                        style="border: none; border-radius: 0.5rem 0.5rem 0 0;"
                                        allowfullscreen>
                                </iframe>
                            @else
                                <div class="d-flex flex-column justify-content-center align-items-center h-100 w-100"
                                     style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 0.5rem 0.5rem 0 0; min-height: 500px;">
                                    <i class="bi bi-vr text-white" style="font-size: 5rem;"></i>
                                    <span class="text-white-50 mt-3 fs-5">Virtual Reality Experience</span>
                                    <span class="text-white-50 mt-2 small">No URL available</span>
                                </div>
                            @endif

                        </div>
                    </div>
                </div>

                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <h5 class="fw-semibold mb-3">About this experiment</h5>
                        <p class="text-muted mb-4">
                            {{ $vrExperiment->description ?? 'No description has been provided for this VR experiment yet.' }}
                        </p>

                        <div class="row g-3">
                            <div class="col-sm-6">
                                <div class="p-3 bg-light rounded-3 h-100">
                                    <span class="text-muted text-uppercase small d-block mb-1">Subject</span>
                                    <span class="fw-semibold">{{ $vrExperiment->subject->subject_name ?? 'General' }}</span>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="p-3 bg-light rounded-3 h-100">
                                    <span class="text-muted text-uppercase small d-block mb-1">Added</span>
                                    <span class="fw-semibold">{{ $vrExperiment->created_at?->format('M d, Y') }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Launch Panel Column -->
            <div class="col-lg-4 col-12 mb-4">
                <div class="card border-0 shadow-sm sticky-top" style="top: 20px;">
                    <div class="card-header bg-gradient text-white" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                        <h5 class="fw-semibold mb-0">
                            <i class="bi bi-vr me-2"></i>Launch VR Experience
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="text-center py-4">
                            <div class="bg-light rounded-circle d-inline-flex align-items-center justify-content-center mb-4" style="width: 100px; height: 100px;">
                                <i class="bi bi-browser-chrome fs-1 text-primary"></i>
                            </div>

                            <h5 class="fw-semibold mb-2">Ready to explore?</h5>
                            <p class="text-muted small mb-4">
                                This browser-based interactive experience will help you understand
                                {{ $vrExperiment->subject->subject_name ?? 'STEM' }} concepts through hands-on simulation.
                            </p>

                            @if($vrExperiment->external_url)
                                <div class="d-grid gap-2">
                                    <a href="{{ $vrExperiment->external_url }}"
                                       target="_blank"
                                       class="btn btn-primary btn-lg">
                                        <i class="bi bi-play-fill me-2"></i>Launch VR Experience
                                    </a>
                                  
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
                    <div class="card-footer bg-light">
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
</x-app-layout>

