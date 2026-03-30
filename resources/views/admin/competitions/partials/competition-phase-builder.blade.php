{{-- Inline script (not @push): stacks inside <x-admin-layout> slots do not reach @stack('scripts'). Include competition-form-styles before page content. Expects $oldPhases. --}}
<template id="phaseRowTemplate">
    <div class="competition-phase-card phase-row p-3 mb-0" data-step="1">
        <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
            <div>
                <h6 class="mb-0 phase-title text-primary">Phase</h6>
                <small class="text-muted phase-sub">What participants do in this stage</small>
            </div>
            <button type="button" class="btn btn-sm btn-outline-danger remove-phase-btn flex-shrink-0">
                <i class="ti ti-trash"></i>
            </button>
        </div>
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label">Phase name <span class="text-danger">*</span></label>
                <input type="text" class="form-control phase-name" required placeholder="e.g. Idea submission">
            </div>
            <div class="col-md-3">
                <label class="form-label">Opens</label>
                <input type="datetime-local" class="form-control phase-start">
                <small class="text-muted">Phase window start</small>
            </div>
            <div class="col-md-3">
                <label class="form-label">Closes</label>
                <input type="datetime-local" class="form-control phase-deadline">
                <small class="text-muted">Deadline for this phase</small>
            </div>
            <div class="col-12">
                <label class="form-label">What happens in this phase</label>
                <textarea class="form-control phase-description" rows="3"
                    placeholder="Describe deliverables, judging criteria, or what schools must complete before moving on."></textarea>
            </div>
            <div class="col-md-4">
                <label class="form-label">Phase status <span class="text-danger">*</span></label>
                <select class="form-select phase-status" required>
                    <option value="upcoming">Upcoming</option>
                    <option value="open">Open</option>
                    <option value="closed">Closed</option>
                    <option value="results_pending">Results pending</option>
                </select>
            </div>
        </div>
    </div>
</template>

<script type="application/json" id="initialPhasesJson">@json($oldPhases)</script>

<script>
(function () {
    var FRAMEWORK_PHASES = [
        { name: 'Registration', description: 'Schools register teams and confirm representatives against eligibility rules.', status: 'upcoming' },
        { name: 'Idea submission', description: 'Teams submit their problem framing, initial idea, and required documentation.', status: 'upcoming' },
        { name: 'Idea shortlisting', description: 'Reviewers shortlist ideas; unsuccessful teams exit or revise per rules.', status: 'upcoming' },
        { name: 'Prototype shortlisting', description: 'Prototype concepts reviewed; selected teams move to build phase.', status: 'upcoming' },
        { name: 'Mentor onboarding', description: 'Mentors assigned; introductions and expectations shared with teams.', status: 'upcoming' },
        { name: 'Prototype development', description: 'Build and document the working prototype within the allotted window.', status: 'upcoming' },
        { name: 'Prototype modification', description: 'Iterate on feedback from mentors or judges before final showcase.', status: 'upcoming' },
        { name: 'Pitch & winner', description: 'Final pitch, demonstration, and winner announcement per rubric.', status: 'upcoming' }
    ];

    function initCompetitionPhases() {
        var phasesContainer = document.getElementById('phasesContainer');
        var phaseTemplate = document.getElementById('phaseRowTemplate');
        var addPhaseBtn = document.getElementById('addPhaseBtn');
        var loadFrameworkBtn = document.getElementById('loadFrameworkPhasesBtn');
        var initialPhasesNode = document.getElementById('initialPhasesJson');
        var initialPhases = [];
        if (initialPhasesNode) {
            try {
                initialPhases = JSON.parse(initialPhasesNode.textContent || '[]');
            } catch (e) {
                initialPhases = [];
            }
        }
        if (!phasesContainer || !phaseTemplate || !addPhaseBtn) {
            return;
        }

        function updatePhaseIndices() {
            var rows = phasesContainer.querySelectorAll('.phase-row');
            rows.forEach(function (row, index) {
                var n = index + 1;
                row.setAttribute('data-step', String(n));
                var titleEl = row.querySelector('.phase-title');
                if (titleEl) {
                    titleEl.textContent = 'Phase ' + n;
                }
                row.querySelector('.phase-name').name = 'phases[' + index + '][name]';
                row.querySelector('.phase-start').name = 'phases[' + index + '][start_at]';
                row.querySelector('.phase-deadline').name = 'phases[' + index + '][deadline_at]';
                row.querySelector('.phase-description').name = 'phases[' + index + '][description]';
                row.querySelector('.phase-status').name = 'phases[' + index + '][status]';
            });
        }

        function addPhaseRow(phase) {
            phase = phase || {};
            var content = phaseTemplate.content.cloneNode(true);
            var row = content.querySelector('.phase-row');
            row.querySelector('.phase-name').value = phase.name || '';
            row.querySelector('.phase-start').value = phase.start_at || '';
            row.querySelector('.phase-deadline').value = phase.deadline_at || '';
            row.querySelector('.phase-description').value = phase.description || '';
            row.querySelector('.phase-status').value = phase.status || 'upcoming';

            row.querySelector('.remove-phase-btn').addEventListener('click', function () {
                row.remove();
                if (phasesContainer.querySelectorAll('.phase-row').length === 0) {
                    addPhaseRow({ name: 'Registration', description: '', status: 'upcoming' });
                }
                updatePhaseIndices();
            });

            phasesContainer.appendChild(row);
            updatePhaseIndices();
        }

        addPhaseBtn.addEventListener('click', function () {
            addPhaseRow({ status: 'upcoming' });
        });

        if (loadFrameworkBtn) {
            loadFrameworkBtn.addEventListener('click', function () {
                var existing = phasesContainer.querySelectorAll('.phase-row');
                var hasContent = false;
                existing.forEach(function (r) {
                    var name = r.querySelector('.phase-name');
                    var desc = r.querySelector('.phase-description');
                    if ((name && name.value.trim()) || (desc && desc.value.trim())) {
                        hasContent = true;
                    }
                });
                if (hasContent && !window.confirm('Replace current phases with the 8-stage framework? You will lose unsaved phase text in this form.')) {
                    return;
                }
                phasesContainer.innerHTML = '';
                FRAMEWORK_PHASES.forEach(function (p) {
                    addPhaseRow(p);
                });
            });
        }

        if (initialPhases.length > 0) {
            initialPhases.forEach(function (phase) {
                addPhaseRow(phase);
            });
        } else {
            addPhaseRow({ name: 'Registration', description: '', status: 'upcoming' });
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initCompetitionPhases);
    } else {
        initCompetitionPhases();
    }
})();
</script>
