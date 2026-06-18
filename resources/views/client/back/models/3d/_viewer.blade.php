@php
    $hasGlb = $model3d->hasGlb();
    $hasEmbed = $model3d->hasEmbed();
    $defaultMode = $model3d->defaultViewerMode();
    $embedUrl = $model3d->viewerEmbedUrl();
@endphp

@if($model3d->hasViewer())
    <div class="model-viewer-shell border-0 overflow-hidden rounded-3">
        @if($hasGlb && $hasEmbed)
            <div class="viewer-toolbar d-flex flex-wrap align-items-center justify-content-between gap-2 px-3 py-2 border-bottom bg-white">
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle">
                        <i class="bi bi-layers me-1"></i>Two formats available
                    </span>
                    <span class="text-muted small d-none d-md-inline">Choose how you want to explore this model</span>
                </div>
                <div class="btn-group viewer-mode-toggle" role="group" aria-label="Viewer mode">
                    <button type="button"
                            class="btn btn-sm {{ $defaultMode === 'glb' ? 'btn-primary' : 'btn-outline-secondary' }}"
                            data-viewer-mode="glb">
                        <i class="bi bi-box me-1"></i>GLB model
                    </button>
                    <button type="button"
                            class="btn btn-sm {{ $defaultMode === 'embed' ? 'btn-primary' : 'btn-outline-secondary' }}"
                            data-viewer-mode="embed">
                        <i class="bi bi-play-btn me-1"></i>Embedded view
                    </button>
                </div>
            </div>
        @endif

        @if($hasGlb)
            <div id="viewer-panel-glb"
                 class="viewer-panel {{ $defaultMode === 'glb' ? '' : 'd-none' }}">
                <div class="position-relative">
                    <div id="model-loading" class="position-absolute top-50 start-50 translate-middle text-center bg-white bg-opacity-90 rounded-3 px-4 py-3 shadow-sm">
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
                        style="width: 100%; height: 520px; background: linear-gradient(180deg, #eef2ff 0%, #f8fafc 100%);"
                    ></model-viewer>
                </div>
            </div>
        @endif

        @if($hasEmbed && $embedUrl)
            <div id="viewer-panel-embed"
                 class="viewer-panel {{ $defaultMode === 'embed' ? '' : 'd-none' }}">
                <div class="embed-viewer-wrap">
                    <div class="embed-brand-bar d-flex align-items-center justify-content-between gap-3 px-3 py-2">
                        <div class="d-flex align-items-center gap-2 min-w-0">
                            <img src="{{ asset('images/logo/logo-dark.png') }}"
                                 alt="{{ config('app.name') }}"
                                 style="height: 28px; width: auto;"
                                 class="flex-shrink-0">
                            <div class="min-w-0">
                                <div class="fw-semibold text-truncate">{{ $model3d->name }}</div>
                                <div class="small text-white-50 text-truncate">
                                    {{ $model3d->subject->subject_name ?? 'STEM model' }} · {{ config('app.name') }}
                                </div>
                            </div>
                        </div>
                        <span class="badge rounded-pill text-bg-light text-dark flex-shrink-0">
                            <i class="bi bi-mortarboard me-1"></i>Learning view
                        </span>
                    </div>
                    <div class="embed-frame-shell position-relative">
                        <iframe
                            id="model-embed-frame"
                            src="{{ $embedUrl }}"
                            title="{{ $model3d->name }} — {{ config('app.name') }}"
                            allow="autoplay; fullscreen; xr-spatial-tracking"
                            allowfullscreen
                            loading="lazy"
                            referrerpolicy="strict-origin-when-cross-origin"
                            class="model-embed-frame"
                        ></iframe>
                    </div>
                </div>
            </div>
        @endif
    </div>

    @once
        @push('styles')
            <style>
                .model-viewer-shell {
                    background: #fff;
                }

                .embed-viewer-wrap {
                    background: #0f172a;
                }

                .embed-brand-bar {
                    background: linear-gradient(90deg, #1e3a8a 0%, #2563eb 55%, #1d4ed8 100%);
                    color: #fff;
                    border-bottom: 1px solid rgba(255, 255, 255, 0.12);
                }

                .embed-frame-shell {
                    height: 520px;
                    background: #111827;
                }

                .model-embed-frame {
                    width: 100%;
                    height: 100%;
                    border: 0;
                    display: block;
                    background: #111827;
                }

                .viewer-mode-toggle .btn {
                    min-width: 130px;
                }
            </style>
        @endpush
    @endonce

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const toggleButtons = document.querySelectorAll('[data-viewer-mode]');
                const glbPanel = document.getElementById('viewer-panel-glb');
                const embedPanel = document.getElementById('viewer-panel-embed');

                toggleButtons.forEach(function (button) {
                    button.addEventListener('click', function () {
                        const mode = button.dataset.viewerMode;

                        toggleButtons.forEach(function (item) {
                            item.classList.toggle('btn-primary', item.dataset.viewerMode === mode);
                            item.classList.toggle('btn-outline-secondary', item.dataset.viewerMode !== mode);
                        });

                        if (glbPanel) {
                            glbPanel.classList.toggle('d-none', mode !== 'glb');
                        }

                        if (embedPanel) {
                            embedPanel.classList.toggle('d-none', mode !== 'embed');
                        }
                    });
                });
            });
        </script>
    @endpush
@else
    <div class="text-center py-5">
        <i class="bi bi-exclamation-triangle text-warning fs-1 mb-3"></i>
        <p class="mb-0 text-muted">This model does not have a GLB file or embed URL yet.</p>
    </div>
@endif
