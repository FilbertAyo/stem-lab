<x-app-layout>
    <div class="col-lg-9">

        <div class="dashboard-content-header d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
            <div>
                <h2 class="title mb-1">{{ $model3d->name }}</h2>
                <p class="text-muted mb-0">
                    {{ $model3d->subject->subject_name ?? 'General STEM model' }}
                </p>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('client.models.3d.index') }}" class="badge bg-light-soft text-dark border d-flex align-items-center" style="font-size: 0.95rem;">
                    Back to library
                </a>
            </div>
        </div>

        <section class="row">
            <!-- 3D Model Viewer Column -->
            <div class="col-lg-8 col-12 mb-4">
                <div class="card border shadow-none mb-4">
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

                <div class="card border shadow-none">
                    <div class="card-body">
                        <h5 class="fw-semibold mb-3">Model overview</h5>
                        <p class="text-muted mb-4">{{ $model3d->description ?? 'No description has been provided for this model yet.' }}</p>

                        <div class="row g-3">
                            <div class="col-sm-12">
                                <div class="p-3 bg-light-soft h-100">
                                    <span class="text-muted text-uppercase small d-block mb-1">Subject</span>
                                    <span class="fw-semibold">{{ $model3d->subject->subject_name ?? 'General' }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Questions Panel Column with Progress Bar on Top -->
            <div class="col-lg-4 col-12 mb-4">
                <div class="card border shadow-none sticky-top" style="top: 20px;">
                    <!-- Progress (Score) Bar & Quick Status -->
                    @if($progress && $progress['has_attempted'])
                        <div class="border-bottom px-4 pt-3 pb-2">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <span class="mb-1 d-block fw-bold">
                                        <i class="bi bi-{{ $progress['is_completed'] ? 'check-circle' : 'clock' }} me-1"></i>
                                        {{ $progress['is_completed'] ? 'Quiz Completed' : 'Quiz In Progress' }}
                                    </span>
                                    <span class="small text-muted">
                                        <strong>Score:</strong>
                                        <span class="text-{{ $progress['percentage'] >= 70 ? 'success' : ($progress['percentage'] >= 50 ? 'warning' : 'danger') }}">
                                            {{ $progress['correct'] }}/{{ $progress['answered'] }}
                                            ({{ $progress['percentage'] }}%)
                                        </span>
                                        @if($progress['total'] > $progress['answered'])
                                            | {{ $progress['answered'] }}/{{ $progress['total'] }} answered
                                        @endif
                                    </span>
                                </div>
                                @if($progress['total'] > 0)
                                    @php
                                        $progressPercentage = round(($progress['answered'] / $progress['total']) * 100);
                                        $progressBarClass = $progress['percentage'] >= 70 ? 'success' : ($progress['percentage'] >= 50 ? 'warning' : 'danger');
                                    @endphp
                                    <div class="text-end ms-2">
                                        <div class="progress" style="width: 90px; height: 14px;">
                                            <div class="progress-bar bg-{{ $progressBarClass }}"
                                                 role="progressbar"
                                                 style="width: <?php echo $progressPercentage; ?>%">
                                                {{ $progressPercentage }}%
                                            </div>
                                        </div>
                                        <small class="text-muted">Progress</small>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endif

                    <div class="card-header">
                        <h5 class="fw-semibold mb-0">
                            <i class="bi bi-question-circle me-2"></i>Questions
                        </h5>
                    </div>
                    <div class="card-body">
                        <div id="questions-container">
                            <div class="text-center py-4">
                                <div class="spinner-border text-primary mb-3" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                                <p class="text-muted mb-0">Loading questions...</p>
                            </div>
                        </div>
                    </div>
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

                    // Load questions
                    loadQuestions();
                });

                let questions = [];
                let submitted = false;
                let results = {};

                function loadQuestions() {
                    fetch('{{ route("client.models.3d.questions.get", $model3d) }}', {
                        method: 'GET',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json',
                        }
                    })
                    .then(response => {
                        if (!response.ok) {
                            throw new Error(`HTTP error! status: ${response.status}`);
                        }
                        return response.json();
                    })
                    .then(data => {
                        console.log('Questions data received:', data);
                        if (data.success && Array.isArray(data.questions)) {
                            if (data.questions.length > 0) {
                                questions = data.questions;
                                renderQuestions();
                            } else {
                                document.getElementById('questions-container').innerHTML = `
                                    <div class="text-center py-4">
                                        <i class="bi bi-info-circle text-muted fs-1 mb-3"></i>
                                        <p class="text-muted mb-0">No questions available for this model yet.</p>
                                    </div>
                                `;
                            }
                        } else {
                            console.error('Invalid response format:', data);
                            document.getElementById('questions-container').innerHTML = `
                                <div class="text-center py-4">
                                    <p class="text-danger mb-0">Invalid response format. Please refresh the page.</p>
                                </div>
                            `;
                        }
                    })
                    .catch(error => {
                        console.error('Error loading questions:', error);
                        document.getElementById('questions-container').innerHTML = `
                            <div class="text-center py-4">
                                <p class="text-danger mb-0">Error loading questions. Please refresh the page.</p>
                                <small class="text-muted d-block mt-2">${error.message}</small>
                            </div>
                        `;
                    });
                }

                function renderQuestions() {
                    const container = document.getElementById('questions-container');
                    let html = '<form id="questions-form">';

                    questions.forEach((question, index) => {
                        html += `
                            <div class="mb-4 question-item" data-question-id="${question.id}">
                                <div class="d-flex align-items-start mb-2">
                                    <span class="badge bg-primary me-2">${index + 1}</span>
                                    <p class="mb-0 fw-semibold">${escapeHtml(question.question_text)}</p>
                                </div>
                                <div class="question-input">
                                    ${renderQuestionInput(question, index)}
                                </div>
                                <div class="question-feedback mt-2" id="feedback-${question.id}" style="display: none;"></div>
                            </div>
                        `;
                    });

                    html += `
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary" id="submit-btn">
                                <i class="bi bi-check-circle me-1"></i>Submit Answers
                            </button>
                            <button type="button" class="btn btn-outline-secondary" id="retry-btn" onclick="resetQuestions()" style="display: none;">
                                <i class="bi bi-arrow-clockwise me-1"></i>Try Again
                            </button>
                        </div>
                    </form>
                    <div id="score-display" class="mt-3" style="display: none;"></div>
                    `;

                    container.innerHTML = html;

                    document.getElementById('questions-form').addEventListener('submit', handleSubmit);
                }

                function renderQuestionInput(question, index) {
                    if (question.question_type === 'mcq' && question.options) {
                        let html = '';
                        question.options.forEach((option, optIndex) => {
                            html += `
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="radio"
                                           name="answer_${question.id}"
                                           id="option_${question.id}_${optIndex}"
                                           value="${escapeHtml(option)}"
                                           ${submitted ? 'disabled' : ''}>
                                    <label class="form-check-label" for="option_${question.id}_${optIndex}">
                                        ${escapeHtml(option)}
                                    </label>
                                </div>
                            `;
                        });
                        return html;
                    } else if (question.question_type === 'true_false') {
                        return `
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="radio"
                                       name="answer_${question.id}"
                                       id="true_${question.id}"
                                       value="True"
                                       ${submitted ? 'disabled' : ''}>
                                <label class="form-check-label" for="true_${question.id}">True</label>
                            </div>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="radio"
                                       name="answer_${question.id}"
                                       id="false_${question.id}"
                                       value="False"
                                       ${submitted ? 'disabled' : ''}>
                                <label class="form-check-label" for="false_${question.id}">False</label>
                            </div>
                        `;
                    } else {
                        return `
                            <input type="text"
                                   class="form-control"
                                   name="answer_${question.id}"
                                   placeholder="Enter your answer"
                                   ${submitted ? 'disabled' : ''}>
                        `;
                    }
                }

                function handleSubmit(e) {
                    e.preventDefault();

                    if (submitted) return;

                    const formData = new FormData(e.target);
                    const answers = [];

                    questions.forEach(question => {
                        const answer = formData.get(`answer_${question.id}`);
                        if (answer) {
                            answers.push({
                                question_id: question.id,
                                answer: answer.trim()
                            });
                        }
                    });

                    if (answers.length !== questions.length) {
                        alert('Please answer all questions before submitting.');
                        return;
                    }

                    const submitBtn = document.getElementById('submit-btn');
                    submitBtn.disabled = true;
                    submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Submitting...';

                    fetch('{{ route("client.models.3d.questions.submit", $model3d) }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify({ answers: answers })
                    })
                    .then(response => response.json())
                    .then(data => {
                        submitted = true;
                        results = data.results.reduce((acc, result) => {
                            acc[result.question_id] = result;
                            return acc;
                        }, {});

                        displayResults(data);
                        submitBtn.style.display = 'none';
                        document.getElementById('retry-btn').style.display = 'block';
                    })
                    .catch(error => {
                        console.error('Error submitting answers:', error);
                        alert('Error submitting answers. Please try again.');
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = '<i class="bi bi-check-circle me-1"></i>Submit Answers';
                    });
                }

                function displayResults(data) {
                    // Display score
                    const scoreDisplay = document.getElementById('score-display');
                    const scoreClass = data.percentage >= 70 ? 'success' : data.percentage >= 50 ? 'warning' : 'danger';
                    scoreDisplay.innerHTML = `
                        <div class="alert alert-${scoreClass}">
                            <h6 class="mb-1"><i class="bi bi-trophy me-2"></i>Your Score</h6>
                            <p class="mb-0 fs-4 fw-bold">${data.score} / ${data.total} (${data.percentage}%)</p>
                        </div>
                    `;
                    scoreDisplay.style.display = 'block';

                    // Display feedback for each question
                    questions.forEach(question => {
                        const result = results[question.id];
                        if (result) {
                            const feedbackDiv = document.getElementById(`feedback-${question.id}`);
                            const isCorrect = result.is_correct;
                            const alertClass = isCorrect ? 'success' : 'danger';
                            let feedbackHtml = `
                                <div class="alert alert-${alertClass} alert-sm py-2 mb-0">
                                    <i class="bi bi-${isCorrect ? 'check-circle' : 'x-circle'} me-1"></i>
                                    <strong>${isCorrect ? 'Correct!' : 'Incorrect'}</strong>
                            `;

                            if (!isCorrect) {
                                feedbackHtml += `<br><small>Correct answer: ${escapeHtml(result.correct_answer)}</small>`;
                            }

                            if (result.explanation) {
                                feedbackHtml += `<br><small class="d-block mt-1">${escapeHtml(result.explanation)}</small>`;
                            }

                            feedbackHtml += `</div>`;
                            feedbackDiv.innerHTML = feedbackHtml;
                            feedbackDiv.style.display = 'block';

                            // Highlight the correct answer
                            if (question.question_type === 'mcq' || question.question_type === 'true_false') {
                                const inputs = document.querySelectorAll(`input[name="answer_${question.id}"]`);
                                inputs.forEach(input => {
                                    if (input.value === result.correct_answer) {
                                        input.closest('.form-check').classList.add('border', 'border-success', 'rounded', 'p-2', 'bg-light');
                                    }
                                });
                            }
                        }
                    });
                }

                function resetQuestions() {
                    submitted = false;
                    results = {};
                    document.getElementById('score-display').style.display = 'none';
                    document.getElementById('retry-btn').style.display = 'none';
                    document.getElementById('submit-btn').style.display = 'block';
                    document.getElementById('submit-btn').disabled = false;
                    document.getElementById('submit-btn').innerHTML = '<i class="bi bi-check-circle me-1"></i>Submit Answers';

                    questions.forEach(question => {
                        const feedbackDiv = document.getElementById(`feedback-${question.id}`);
                        if (feedbackDiv) {
                            feedbackDiv.style.display = 'none';
                            feedbackDiv.innerHTML = '';
                        }

                        const inputs = document.querySelectorAll(`input[name="answer_${question.id}"]`);
                        inputs.forEach(input => {
                            input.disabled = false;
                            input.checked = false;
                            input.value = '';
                            input.closest('.form-check')?.classList.remove('border', 'border-success', 'rounded', 'p-2', 'bg-light');
                        });

                        const textInputs = document.querySelectorAll(`input[type="text"][name="answer_${question.id}"]`);
                        textInputs.forEach(input => {
                            input.disabled = false;
                            input.value = '';
                        });
                    });
                }

                function escapeHtml(text) {
                    const div = document.createElement('div');
                    div.textContent = text;
                    return div.innerHTML;
                }
            </script>
        @endpush
    @endif
</x-app-layout>
