<x-app-layout>
    <div class="col-lg-9">

        {{-- Sticky quiz toolbar --}}
        <div class="quiz-toolbar p-3 mb-4 sticky-top" style="top: 4.5rem; z-index: 1020;">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                <div class="min-w-0">
                    <h5 class="fw-bold mb-1 text-truncate">{{ $attempt->questionBank->title }}</h5>
                    <p class="text-muted mb-0 small text-truncate">
                        <i class="bi bi-vr me-1"></i>{{ $attempt->questionBank->vrExperiment->name }}
                    </p>
                </div>
                <div class="d-flex gap-2 align-items-center flex-shrink-0">
                    @if($timeRemaining !== null)
                        <span class="badge quiz-timer-badge fs-6 px-3 py-2" id="timer-container">
                            <i class="bi bi-clock me-1"></i>
                            <span id="time-display">{{ gmdate('H:i:s', (int) $timeRemaining) }}</span>
                        </span>
                    @endif
                    <span class="badge bg-primary fs-6 px-3 py-2">
                        <span id="answered-count">{{ count($answeredQuestionIds) }}</span>/{{ $attempt->total_questions }}
                        <span class="d-none d-sm-inline">answered</span>
                    </span>
                </div>
            </div>
            <div class="quiz-progress-track mt-3">
                <div class="quiz-progress-fill"
                     id="progress-bar"
                     role="progressbar"
                     aria-valuenow="{{ count($answeredQuestionIds) }}"
                     aria-valuemin="0"
                     aria-valuemax="{{ $attempt->total_questions }}"
                     style="width: {{ $attempt->total_questions > 0 ? (count($answeredQuestionIds) / $attempt->total_questions) * 100 : 0 }}%;"></div>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-lg-8">
                @foreach($attempt->questionBank->questions as $index => $question)
                    <div class="card border shadow-none quiz-question-card mb-3"
                         id="question-{{ $question->id }}"
                         data-question-id="{{ $question->id }}">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start gap-2 mb-3">
                                <span class="badge bg-light text-dark border">
                                    Question {{ $index + 1 }} of {{ $attempt->total_questions }}
                                </span>
                                <span class="badge {{ in_array($question->id, $answeredQuestionIds) ? 'bg-success' : 'bg-light text-dark border' }}"
                                      id="status-{{ $question->id }}">
                                    @if(in_array($question->id, $answeredQuestionIds))
                                        <i class="bi bi-check-circle me-1"></i>Answered
                                    @else
                                        Not answered
                                    @endif
                                </span>
                            </div>

                            <h5 class="fw-semibold mb-4">{{ $question->question_text }}</h5>

                            @if($question->question_type === 'mcq')
                                <div class="d-flex flex-column gap-2">
                                    @foreach($question->options as $optionIndex => $option)
                                        @php
                                            $existingAnswer = $attempt->answers->where('question_id', $question->id)->first();
                                            $isSelected = $existingAnswer && $existingAnswer->student_answer === $option;
                                        @endphp
                                        <div class="quiz-option-item p-3 {{ $isSelected ? 'is-selected' : '' }}"
                                             onclick="selectOption({{ $question->id }}, '{{ addslashes($option) }}', this)">
                                            <div class="form-check mb-0">
                                                <input class="form-check-input"
                                                       type="radio"
                                                       name="answer_{{ $question->id }}"
                                                       id="option_{{ $question->id }}_{{ $optionIndex }}"
                                                       value="{{ $option }}"
                                                       {{ $isSelected ? 'checked' : '' }}>
                                                <label class="form-check-label w-100 cursor-pointer"
                                                       for="option_{{ $question->id }}_{{ $optionIndex }}">
                                                    <span class="badge bg-light text-dark border me-2">{{ chr(65 + $optionIndex) }}</span>
                                                    {{ $option }}
                                                </label>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @elseif($question->question_type === 'true_false')
                                @php
                                    $existingAnswer = $attempt->answers->where('question_id', $question->id)->first();
                                @endphp
                                <div class="row g-2">
                                    <div class="col-6">
                                        <div class="quiz-option-item p-3 h-100 text-center {{ $existingAnswer && $existingAnswer->student_answer === 'True' ? 'is-selected' : '' }}"
                                             onclick="selectOption({{ $question->id }}, 'True', this)">
                                            <input class="form-check-input d-none"
                                                   type="radio"
                                                   name="answer_{{ $question->id }}"
                                                   id="true_{{ $question->id }}"
                                                   value="True"
                                                   {{ $existingAnswer && $existingAnswer->student_answer === 'True' ? 'checked' : '' }}>
                                            <label class="mb-0 fw-semibold cursor-pointer" for="true_{{ $question->id }}">
                                                <i class="bi bi-check-circle text-success me-1"></i>True
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="quiz-option-item p-3 h-100 text-center {{ $existingAnswer && $existingAnswer->student_answer === 'False' ? 'is-selected' : '' }}"
                                             onclick="selectOption({{ $question->id }}, 'False', this)">
                                            <input class="form-check-input d-none"
                                                   type="radio"
                                                   name="answer_{{ $question->id }}"
                                                   id="false_{{ $question->id }}"
                                                   value="False"
                                                   {{ $existingAnswer && $existingAnswer->student_answer === 'False' ? 'checked' : '' }}>
                                            <label class="mb-0 fw-semibold cursor-pointer" for="false_{{ $question->id }}">
                                                <i class="bi bi-x-circle text-danger me-1"></i>False
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            @else
                                @php
                                    $existingAnswer = $attempt->answers->where('question_id', $question->id)->first();
                                @endphp
                                <div class="input-group">
                                    <input type="text"
                                           class="form-control border"
                                           id="input_{{ $question->id }}"
                                           placeholder="Type your answer..."
                                           value="{{ $existingAnswer ? $existingAnswer->student_answer : '' }}">
                                    <button class="btn btn-primary text-white"
                                            type="button"
                                            onclick="saveShortAnswer({{ $question->id }})">
                                        <i class="bi bi-check-lg me-1"></i>Save
                                    </button>
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach

                <div class="card border shadow-none">
                    <div class="card-body text-center py-4">
                        <i class="bi bi-send-check text-primary fs-2 mb-2 d-block"></i>
                        <h6 class="fw-semibold mb-1">Ready to submit?</h6>
                        <p class="text-muted small mb-3">
                            <span id="unanswered-hint">
                                {{ max(0, $attempt->total_questions - count($answeredQuestionIds)) }} question(s) still unanswered.
                            </span>
                        </p>
                        <form action="{{ route('client.vr-assessments.submit', $attempt) }}"
                              method="POST"
                              id="submit-form"
                              onsubmit="return confirmSubmit()">
                            @csrf
                            <button type="submit" class="btn btn-success btn-lg px-5">
                                <i class="bi bi-check-circle me-1"></i>Submit quiz
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            {{-- Question navigator --}}
            <div class="col-lg-4">
                <div class="card border shadow-none sticky-top" style="top: 5.5rem;">
                    <div class="card-header bg-light-soft py-3">
                        <h6 class="mb-0 fw-semibold">
                            <i class="bi bi-grid-3x3-gap me-1 text-primary"></i>Question navigator
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="d-flex flex-wrap gap-2 mb-3">
                            @foreach($attempt->questionBank->questions as $index => $question)
                                <a href="#question-{{ $question->id }}"
                                   class="btn btn-sm btn-outline-secondary quiz-nav-btn {{ in_array($question->id, $answeredQuestionIds) ? 'is-answered' : '' }}"
                                   id="nav-{{ $question->id }}">
                                    {{ $index + 1 }}
                                </a>
                            @endforeach
                        </div>

                        <div class="d-flex flex-column gap-2 small text-muted">
                            <span><span class="badge bg-success me-1">&nbsp;</span> Answered</span>
                            <span><span class="badge bg-light border text-dark me-1">&nbsp;</span> Not answered</span>
                        </div>

                        <hr class="my-3">

                        <a href="{{ route('client.vr-assessments.show', $attempt->questionBank) }}"
                           class="btn btn-sm btn-outline-secondary w-100">
                            <i class="bi bi-arrow-left me-1"></i>Back to quiz info
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        const attemptId = {{ $attempt->id }};
        const totalQuestions = {{ $attempt->total_questions }};
        let answeredCount = {{ count($answeredQuestionIds) }};

        function updateUnansweredHint() {
            const hint = document.getElementById('unanswered-hint');
            if (!hint) return;
            const remaining = Math.max(0, totalQuestions - answeredCount);
            hint.textContent = remaining === 0
                ? 'All questions answered. You can submit when ready.'
                : remaining + ' question(s) still unanswered.';
        }

        @if($timeRemaining !== null)
        let timeRemaining = Math.floor({{ $timeRemaining }});
        const timerDisplay = document.getElementById('time-display');
        const timerContainer = document.getElementById('timer-container');

        const timerInterval = setInterval(() => {
            timeRemaining--;

            if (timeRemaining <= 0) {
                clearInterval(timerInterval);
                document.getElementById('submit-form').submit();
                return;
            }

            const hours = Math.floor(timeRemaining / 3600);
            const minutes = Math.floor((timeRemaining % 3600) / 60);
            const seconds = timeRemaining % 60;
            timerDisplay.textContent = String(hours).padStart(2, '0') + ':' + String(minutes).padStart(2, '0') + ':' + String(seconds).padStart(2, '0');

            if (timeRemaining <= 300) {
                timerContainer.classList.add('is-critical');
                timerContainer.classList.remove('is-warning');
            } else if (timeRemaining <= 600) {
                timerContainer.classList.add('is-warning');
            }
        }, 1000);
        @endif

        function selectOption(questionId, answer, element) {
            const card = document.getElementById('question-' + questionId);
            card.querySelectorAll('.quiz-option-item').forEach(item => {
                item.classList.remove('is-selected');
            });
            element.classList.add('is-selected');

            const radio = element.querySelector('input[type="radio"]');
            if (radio) {
                radio.checked = true;
            }

            saveAnswer(questionId, answer);
        }

        function saveShortAnswer(questionId) {
            const input = document.getElementById('input_' + questionId);
            const answer = input.value.trim();

            if (!answer) {
                alert('Please enter an answer');
                return;
            }

            saveAnswer(questionId, answer);
        }

        function saveAnswer(questionId, answer) {
            fetch('{{ route("client.vr-assessments.save-answer", $attempt) }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    question_id: questionId,
                    answer: answer
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const statusBadge = document.getElementById('status-' + questionId);
                    statusBadge.innerHTML = '<i class="bi bi-check-circle me-1"></i>Answered';
                    statusBadge.classList.remove('bg-light', 'text-dark', 'border');
                    statusBadge.classList.add('bg-success');

                    const navBtn = document.getElementById('nav-' + questionId);
                    navBtn.classList.remove('btn-outline-secondary');
                    navBtn.classList.add('is-answered');

                    answeredCount = data.answered_count;
                    document.getElementById('answered-count').textContent = answeredCount;
                    document.getElementById('progress-bar').style.width =
                        (totalQuestions > 0 ? (answeredCount / totalQuestions) * 100 : 0) + '%';
                    updateUnansweredHint();
                }
            })
            .catch(error => {
                console.error('Error saving answer:', error);
            });
        }

        function confirmSubmit() {
            const unanswered = totalQuestions - answeredCount;
            if (unanswered > 0) {
                return confirm(`You have ${unanswered} unanswered question(s). Are you sure you want to submit?`);
            }
            return confirm('Are you sure you want to submit your quiz?');
        }

        updateUnansweredHint();
    </script>
    @endpush
</x-app-layout>
