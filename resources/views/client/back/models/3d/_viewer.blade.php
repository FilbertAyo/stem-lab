@php
    $hasGlb = $model3d->hasGlb();
    $hasEmbed = $model3d->hasEmbed();
    $defaultMode = $model3d->defaultViewerMode();
    $embedUrl = $model3d->viewerEmbedUrl();
    $embedTitle = 'Adilisha '.$model3d->name;
    $brandTag = 'Interactive 3D '.strtolower($model3d->subject->subject_name ?? 'STEM').' visualization';
@endphp

@if($model3d->hasViewer())
    <div class="model-viewer-shell">
        @if($hasGlb && $hasEmbed)
            <div class="viewer-toolbar d-flex flex-wrap align-items-center justify-content-between gap-2 px-3 py-2 border-bottom bg-white">
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-light text-dark border">
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
                    <div id="model-loading" class="position-absolute top-50 start-50 translate-middle text-center bg-white bg-opacity-90 px-4 py-3 shadow-sm">
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
                <div class="sketchfab-embed-wrapper">
                    <div class="embed-brand-overlay">
                        <div class="brand-logo">A</div>
                        <div>
                            <span class="brand-text">Adilisha</span>
                            <span class="brand-sub">{{ $model3d->name }}</span>
                        </div>
                    </div>
                    <iframe
                        id="model-embed-frame"
                        title="{{ $embedTitle }}"
                        allowfullscreen
                        mozallowfullscreen="true"
                        webkitallowfullscreen="true"
                        allow="autoplay; fullscreen; xr-spatial-tracking"
                        xr-spatial-tracking
                        execution-while-out-of-viewport
                        execution-while-not-rendered
                        web-share
                        loading="lazy"
                        referrerpolicy="strict-origin-when-cross-origin"
                        src="{{ $embedUrl }}"
                    ></iframe>
                </div>
                <div class="model-brand">
                    <span class="brand-name">Adilisha</span>
                    <span class="brand-tag">{{ $brandTag }}</span>
                </div>
            </div>
        @endif
    </div>

    @once
        @push('styles')
            <style>
                .model-viewer-shell {
                    background: #fff;
                    border-radius: 0;
                    overflow: hidden;
                }

                .sketchfab-embed-wrapper {
                    position: relative;
                    width: 100%;
                    height: 520px;
                    background: #0f172a;
                    border-radius: 0;
                    overflow: hidden;
                }

                .sketchfab-embed-wrapper iframe {
                    width: 100%;
                    height: 100%;
                    border: 0;
                    display: block;
                    border-radius: 0;
                }

                .embed-brand-overlay {
                    position: absolute;
                    top: 14px;
                    left: 14px;
                    z-index: 2;
                    display: flex;
                    align-items: center;
                    gap: 10px;
                    padding: 8px 12px;
                    background: rgba(15, 23, 42, 0.88);
                    border: 1px solid rgba(255, 255, 255, 0.12);
                    border-radius: 0;
                    color: #fff;
                    pointer-events: none;
                }

                .embed-brand-overlay .brand-logo {
                    width: 34px;
                    height: 34px;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    background: #2563eb;
                    color: #fff;
                    font-weight: 700;
                    font-size: 1rem;
                    border-radius: 0;
                    flex-shrink: 0;
                }

                .embed-brand-overlay .brand-text {
                    display: block;
                    font-size: 0.82rem;
                    font-weight: 600;
                    line-height: 1.2;
                    letter-spacing: 0.02em;
                }

                .embed-brand-overlay .brand-sub {
                    display: block;
                    font-size: 0.72rem;
                    color: rgba(255, 255, 255, 0.72);
                    line-height: 1.2;
                    max-width: 220px;
                    white-space: nowrap;
                    overflow: hidden;
                    text-overflow: ellipsis;
                }

                .model-brand {
                    display: flex;
                    flex-direction: column;
                    gap: 2px;
                    padding: 12px 16px;
                    background: #f8fafc;
                    border-top: 1px solid #e2e8f0;
                    border-radius: 0;
                }

                .model-brand .brand-name {
                    font-size: 0.9rem;
                    font-weight: 700;
                    color: #0f172a;
                    letter-spacing: 0.01em;
                }

                .model-brand .brand-tag {
                    font-size: 0.78rem;
                    color: #64748b;
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
