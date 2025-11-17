<x-app-layout>
    <div class="page-heading d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div>
            <h3 class="mb-1">{{ $model3d->name }}</h3>
            <p class="text-muted mb-0">
                {{ $model3d->subject->subject_name ?? 'General STEM model' }}
            </p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('client.models.3d.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left me-1"></i> Back to library
            </a>
        </div>
    </div>

    <div class="page-content">
        <section class="row">
            <div class="col-12">
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body p-0">
                        @if($model3d->file)
                            <div class="position-relative">
                                <div id="model-loading" class="position-absolute top-50 start-50 translate-middle text-center bg-white bg-opacity-75 rounded-3 px-4 py-3 shadow-sm">
                                    <div class="spinner-border text-primary mb-2" role="status">
                                        <span class="visually-hidden">Loading...</span>
                                    </div>
                                    <p class="mb-0 text-muted small">Rendering 3D model</p>
                                </div>
                                <model-viewer
                                    id="model-viewer"
                                    src="{{ asset('storage/' . $model3d->file) }}"
                                    alt="{{ $model3d->name }}"
                                    auto-rotate
                                    camera-controls
                                    touch-action="pan-y"
                                    ar
                                    shadow-intensity="0.8"
                                    style="width: 100%; height: 520px; background: #f4f6fb;"
                                ></model-viewer>
                            </div>
                        @else
                            <div class="text-center py-5">
                                <i class="bi bi-exclamation-triangle text-warning fs-1 mb-3"></i>
                                <p class="mb-0 text-muted">The 3D file for this model is not available.</p>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="row">
                    <div class="col-lg-12">
                        <div class="card border-0 shadow-sm mb-4">
                            <div class="card-body">
                                <h5 class="fw-semibold mb-3">Model overview</h5>
                                <p class="text-muted mb-4">{{ $model3d->description ?? 'No description has been provided for this model yet.' }}</p>

                                <div class="row g-3">
                                    <div class="col-sm-12">
                                        <div class="p-3 bg-light rounded-3 h-100">
                                            <span class="text-muted text-uppercase small d-block mb-1">Subject</span>
                                            <span class="fw-semibold">{{ $model3d->subject->subject_name ?? 'General' }}</span>
                                        </div>
                                    </div>
                                    <!-- Tags column removed -->
                                </div>
                            </div>
                        </div>
                    </div>


                    <!-- End thumbnail preview removed -->
                </div>
            </div>
        </section>
    </div>

    @if($model3d->file)
        @once
            @push('scripts')
                <script type="module" src="https://unpkg.com/@google/model-viewer@3.3.0/dist/model-viewer.min.js"></script>
            @endpush
        @endonce

        @push('scripts')
            <script>
                document.addEventListener('DOMContentLoaded', function () {
                    const modelViewer = document.getElementById('model-viewer');
                    const loadingState = document.getElementById('model-loading');

                    if (!modelViewer || !loadingState) {
                        return;
                    }

                    modelViewer.addEventListener('load', () => {
                        loadingState.style.display = 'none';
                    });

                    modelViewer.addEventListener('error', () => {
                        loadingState.innerHTML = '<p class="text-danger mb-0 small">Unable to load this 3D model. Please try again later.</p>';
                    });
                });
            </script>
        @endpush
    @endif
</x-app-layout>
