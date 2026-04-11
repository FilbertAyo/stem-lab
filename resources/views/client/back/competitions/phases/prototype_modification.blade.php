@php
    $prototype = $idea?->prototypeSubmission;
    $prototypeShortlist = $prototype?->prototypeShortlist;
    $prototypeShortlistStatus = $prototypeShortlist?->status;
    $modification = $idea?->prototypeModification;
    $modificationStatus = $modification?->status;
    $teacherUserId = auth()->id();
    $modificationEvidenceIdentifier = implode('-', array_filter([
        (string) $teacherUserId,
        (string) ($competition?->ulid ?? ''),
        (string) ($idea?->ulid ?? ''),
        'mod',
    ], fn (string $v): bool => $v !== ''));
@endphp

<div id="prototype-modification" class="row">
    <div class="col-12">
        <div class="card border-0 shadow-none mb-4">
            <div class="card-header bg-transparent">
                <h4 class="mb-0 text-uppercase">Prototype modification</h4>
                <p class="text-muted small mb-0 mt-1">Submit your improved prototype details after mentor guidance.</p>
            </div>

            <div class="card-body">
                @if(!$teacherClub)
                    <div class="alert alert-warning mb-0">You must be an approved teacher in this club to view this competition.</div>
                @elseif(!$registration)
                    <p class="text-muted mb-0">No registration found for your school in this competition.</p>
                @elseif(! $idea || ! $prototype)
                    <div class="alert alert-info mb-0">No prototype submission found yet.</div>
                @elseif($prototypeShortlistStatus !== 'shortlisted')
                    <div class="alert alert-warning mb-0">
                        Prototype modification is available only for teams shortlisted in prototype shortlisting.
                    </div>
                @else
                    @php
                        $badge = match ($modificationStatus) {
                            'submitted' => ['success', 'Submitted'],
                            'draft' => ['secondary', 'Draft'],
                            default => ['secondary', 'Not started'],
                        };
                    @endphp

                    <div class="d-flex flex-wrap align-items-center gap-2 mb-4 pb-3 border-bottom border-opacity-25">
                        <span class="badge bg-{{ $badge[0] }}">{{ $badge[1] }}</span>
                    </div>

                    <form method="POST" action="{{ route('client.competitions.prototype-modification.store', $competition) }}">
                        @csrf

                        <div class="mb-3">
                            <label class="form-label">Updated prototype title <span class="text-danger">*</span></label>
                            <input type="text" name="prototype_title" class="form-control @error('prototype_title') is-invalid @enderror border p-2"
                                   value="{{ old('prototype_title', $modification?->prototype_title ?? $prototype?->prototype_title) }}" required maxlength="255">
                            @error('prototype_title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">What you built now <span class="text-danger">*</span></label>
                            <textarea name="prototype_concept" class="form-control @error('prototype_concept') is-invalid @enderror border p-2" rows="3" required>{{ old('prototype_concept', $modification?->prototype_concept ?? $prototype?->prototype_concept) }}</textarea>
                            @error('prototype_concept')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Updated objectives <span class="text-danger">*</span></label>
                            <textarea name="prototype_objectives" class="form-control @error('prototype_objectives') is-invalid @enderror border p-2" rows="3" required>{{ old('prototype_objectives', $modification?->prototype_objectives ?? $prototype?->prototype_objectives) }}</textarea>
                            @error('prototype_objectives')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">How it works now <span class="text-danger">*</span></label>
                            <textarea name="prototype_design_description" class="form-control @error('prototype_design_description') is-invalid @enderror border p-2" rows="4" required>{{ old('prototype_design_description', $modification?->prototype_design_description ?? $prototype?->prototype_design_description) }}</textarea>
                            @error('prototype_design_description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Materials used <span class="text-danger">*</span></label>
                            <textarea name="prototype_materials" class="form-control @error('prototype_materials') is-invalid @enderror border p-2" rows="3" required>{{ old('prototype_materials', $modification?->prototype_materials ?? $prototype?->prototype_materials) }}</textarea>
                            @error('prototype_materials')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Build process <span class="text-danger">*</span></label>
                            <textarea name="prototype_build_process" class="form-control @error('prototype_build_process') is-invalid @enderror border p-2" rows="4" required>{{ old('prototype_build_process', $modification?->prototype_build_process ?? $prototype?->prototype_build_process) }}</textarea>
                            @error('prototype_build_process')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Challenges (optional)</label>
                            <textarea name="prototype_challenges" class="form-control @error('prototype_challenges') is-invalid @enderror border p-2" rows="3">{{ old('prototype_challenges', $modification?->prototype_challenges ?? $prototype?->prototype_challenges) }}</textarea>
                            @error('prototype_challenges')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Test results (optional)</label>
                            <textarea name="prototype_test_results" class="form-control @error('prototype_test_results') is-invalid @enderror border p-2" rows="3">{{ old('prototype_test_results', $modification?->prototype_test_results ?? $prototype?->prototype_test_results) }}</textarea>
                            @error('prototype_test_results')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Next steps (optional)</label>
                            <textarea name="prototype_next_steps" class="form-control @error('prototype_next_steps') is-invalid @enderror border p-2" rows="3">{{ old('prototype_next_steps', $modification?->prototype_next_steps ?? $prototype?->prototype_next_steps) }}</textarea>
                            @error('prototype_next_steps')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        @if(filled($prototypeModificationEvidenceFormUrl))
                            <div class="alert alert-light border mb-4">
                                <div class="small text-muted mb-1"><strong>Google Form link (modification evidence)</strong></div>
                                <a href="{{ $prototypeModificationEvidenceFormUrl }}" target="_blank" rel="noopener noreferrer" class="badge bg-primary border-0 mb-2 text-white">
                                    Open Google Form
                                </a>
                                <div class="small text-muted mb-1"><strong>Google Form identifier</strong></div>
                                <div class="input-group bg-white" style="max-width: 720px;">
                                    <input id="modificationEvidenceIdentifierInput" type="text" class="form-control font-monospace border p-2" value="{{ $modificationEvidenceIdentifier }}" readonly>
                                    <button type="button" class="badge bg-info border-0 text-white" id="copyModificationEvidenceIdentifierBtn">Copy ID</button>
                                </div>
                            </div>
                        @endif

                        <div class="d-flex flex-wrap gap-2 mt-2">
                            <button type="submit" class="badge bg-dark border-0" name="submit_final" value="0">Save draft</button>
                            <button type="submit" class="badge bg-primary border-0" name="submit_final" value="1">Submit modification</button>
                        </div>
                    </form>
                @endif
            </div>
        </div>
    </div>
</div>

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const btn = document.getElementById('copyModificationEvidenceIdentifierBtn');
            const input = document.getElementById('modificationEvidenceIdentifierInput');
            if (!btn || !input) return;
            btn.addEventListener('click', async function () {
                try {
                    await navigator.clipboard.writeText(input.value || '');
                } catch (e) {
                    input.select();
                    document.execCommand('copy');
                }
            });
        });
    </script>
@endpush

