<x-app-layout>
    <div class="col-lg-9">

        {{-- Quiz Header --}}
        <div class="card border-0 shadow-none mb-4">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                    <div>
                        <h4 class="mb-1">{{ $attempt->questionBank->title }}</h4>
                        <p class="text-muted mb-0 small">
                            <i class="bi bi-vr me-1"></i>{{ $attempt->questionBank->vrExperiment->name }}
                        </p>
                    </div>
                    <div class="d-flex gap-3 align-items-center">
                        {{-- Timer --}}
                        @if($timeRemaining !== null)
                            <div class="text-center" id="timer-container">
                                <span class="badge bg-warning text-dark fs-5 px-3 py-2" id="timer">
                                    <i class="bi bi-clock me-1"></i>
                                    <span id="time-display">{{ gmdate('H:i:s', (int) $timeRemaining) }}</span>
                                </span>
                            </div>
                        @endif

                        {{-- Progress --}}
                        <div class="text-center">
                            <span class="badge bg-primary fs-6 px-3 py-2">
                                <span id="answered-count">{{ count($answeredQuestionIds) }}</span> / {{ $attempt->total_questions }}
                            </span>
                            <small class="d-block text-muted">Answered</small>
                        </div>
                    </div>
                </div>

                {{-- Progress Bar --}}
                <div class="progress mt-3" style="height: 6px;">
                    <div class="progress-bar bg-success" 
                         role="progressbar" 
                         id="progress-bar"
                         style="width: {{ $attempt->total_questions > 0 ? (count($answeredQuestionIds) / $attempt->total_questions) * 100 : 0 }}%"></div>
                </div>
            </div>
        </div>

        {{-- Questions --}}
        <div class="row">
            <div class="col-lg-8">
                @foreach($attempt->questionBank->questions as $index => $question)
                    <div class="card border-0 shadow-none mb-3 question-card" 
                         id="question-{{ $question->id }}"
                         data-question-id="{{ $question->id }}">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <span class="badge bg-secondary">Question {{ $index + 1 }}</span>
                                <span class="badge {{ in_array($question->id, $answeredQuestionIds) ? 'bg-success' : 'bg-light text-dark' }}" 
                                      id="status-{{ $question->id }}">
                                    {{ in_array($question->id, $answeredQuestionIds) ? 'Answered' : 'Not answered' }}
                                </span>
                            </div>

                            <h5 class="mb-4">{{ $question->question_text }}</h5>

                            @if($question->question_type === 'mcq')
                                <div class="options-list">
                                    @foreach($question->options as $optionIndex => $option)
                                        @php
                                            $existingAnswer = $attempt->answers->where('question_id', $question->id)->first();
                                            $isSelected = $existingAnswer && $existingAnswer->student_answer === $option;
                                        @endphp
                                        <div class="form-check mb-2 p-3 border rounded option-item {{ $isSelected ? 'bg-primary-subtle border-primary' : '' }}"
                                             onclick="selectOption({{ $question->id }}, '{{ addslashes($option) }}', this)">
                                            <input class="form-check-input" 
                                                   type="radio" 
                                                   name="answer_{{ $question->id }}" 
                                                   id="option_{{ $question->id }}_{{ $optionIndex }}"
                                                   value="{{ $option }}"
                                                   {{ $isSelected ? 'checked' : '' }}>
                                            <label class="form-check-label w-100" 
                                                   for="option_{{ $question->id }}_{{ $optionIndex }}">
                                                <strong>{{ chr(65 + $optionIndex) }}.</strong> {{ $option }}
                                            </label>
                                        </div>
                                    @endforeach
                                </div>
                            @elseif($question->question_type === 'true_false')
                                @php
                                    $existingAnswer = $attempt->answers->where('question_id', $question->id)->first();
                                @endphp
                                <div class="d-flex gap-3">
                                    <div class="form-check p-3 border rounded flex-fill option-item {{ $existingAnswer && $existingAnswer->student_answer === 'True' ? 'bg-primary-subtle border-primary' : '' }}"
                                         onclick="selectOption({{ $question->id }}, 'True', this)">
                                        <input class="form-check-input" 
                                               type="radio" 
                                               name="answer_{{ $question->id }}" 
                                               id="true_{{ $question->id }}"
                                               value="True"
                                               {{ $existingAnswer && $existingAnswer->student_answer === 'True' ? 'checked' : '' }}>
                                        <label class="form-check-label w-100" for="true_{{ $question->id }}">
                                            <i class="bi bi-check-circle me-1 text-success"></i> True
                                        </label>
                                    </div>
                                    <div class="form-check p-3 border rounded flex-fill option-item {{ $existingAnswer && $existingAnswer->student_answer === 'False' ? 'bg-primary-subtle border-primary' : '' }}"
                                         onclick="selectOption({{ $question->id }}, 'False', this)">
                                        <input class="form-check-input" 
                                               type="radio" 
                                               name="answer_{{ $question->id }}" 
                                               id="false_{{ $question->id }}"
                                               value="False"
                                               {{ $existingAnswer && $existingAnswer->student_answer === 'False' ? 'checked' : '' }}>
                                        <label class="form-check-label w-100" for="false_{{ $question->id }}">
                                            <i class="bi bi-x-circle me-1 text-danger"></i> False
                                        </label>
                                    </div>
                                </div>
                            @else
                                @php
                                    $existingAnswer = $attempt->answers->where('question_id', $question->id)->first();
                                @endphp
                                <div class="input-group">
                                    <input type="text" 
                                           class="form-control" 
                                           id="input_{{ $question->id }}"
                                           placeholder="Type your answer..."
                                           value="{{ $existingAnswer ? $existingAnswer->student_answer : '' }}">
                                    <button class="btn btn-primary" 
                                            type="button"
                                            onclick="saveShortAnswer({{ $question->id }})">
                                        Save
                                    </button>
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach

                {{-- Submit Button --}}
                <div class="card border-0 shadow-none">
                    <div class="card-body text-center">
                        <p class="text-muted mb-3">
                            Make sure you've answered all questions before submitting.
                        </p>
                        <form action="{{ route('client.vr-assessments.submit', $attempt) }}" 
                              method="POST" 
                              id="submit-form"
                              onsubmit="return confirmSubmit()">
                            @csrf
                            <button type="submit" class="btn btn-success btn-lg px-5">
                                <i class="bi bi-check-circle me-1"></i>Submit Quiz
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            {{-- Question Navigator --}}
            <div class="col-lg-4">
                <div class="card border-0 shadow-none sticky-top" style="top: 20px;">
                    <div class="card-header bg-light">
                        <h6 class="mb-0"><i class="bi bi-list-ol me-1"></i>Question Navigator</h6>
                    </div>
                    <div class="card-body">
                        <div class="d-flex flex-wrap gap-2">
                            @foreach($attempt->questionBank->questions as $index => $question)
                                <a href="#question-{{ $question->id }}" 
                                   class="btn btn-sm {{ in_array($question->id, $answeredQuestionIds) ? 'btn-success' : 'btn-outline-secondary' }}"
                                   id="nav-{{ $question->id }}"
                                   style="width: 40px; height: 40px;">
                                    {{ $index + 1 }}
                                </a>
                            @endforeach
                        </div>
                        
                        <hr>
                        
                        <div class="d-flex justify-content-between small text-muted">
                            <span><span class="badge bg-success">&nbsp;</span> Answered</span>
                            <span><span class="badge bg-outline-secondary border">&nbsp;</span> Not answered</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('styles')
    <style>
        .option-item {
            cursor: pointer;
            transition: all 0.2s ease;
        }
        .option-item:hover {
            background-color: #f8f9fa;
            border-color: #dee2e6;
        }
        .option-item.bg-primary-subtle {
            background-color: rgba(13, 110, 253, 0.1) !important;
        }
        .question-card {
            scroll-margin-top: 100px;
        }
    </style>
    @endpush

    @push('scripts')
    <script>
        const attemptId = {{ $attempt->id }};
        const totalQuestions = {{ $attempt->total_questions }};
        let answeredCount = {{ count($answeredQuestionIds) }};
        
        @if($timeRemaining !== null)
        // Timer functionality
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
            
            // Format time as HH:MM:SS
            const hours = Math.floor(timeRemaining / 3600);
            const minutes = Math.floor((timeRemaining % 3600) / 60);
            const seconds = timeRemaining % 60;
            timerDisplay.textContent = String(hours).padStart(2, '0') + ':' + String(minutes).padStart(2, '0') + ':' + String(seconds).padStart(2, '0');
            
            // Warning when 5 minutes left
            if (timeRemaining <= 300 && timeRemaining > 60) {
                timerContainer.querySelector('.badge').classList.remove('bg-warning', 'text-dark');
                timerContainer.querySelector('.badge').classList.add('bg-danger', 'text-white');
            }
            
            // Critical warning when 1 minute left
            if (timeRemaining <= 60) {
                timerContainer.querySelector('.badge').classList.add('pulse');
            }
        }, 1000);
        @endif

        function selectOption(questionId, answer, element) {
            // Update visual selection
            const card = document.getElementById('question-' + questionId);
            card.querySelectorAll('.option-item').forEach(item => {
                item.classList.remove('bg-primary-subtle', 'border-primary');
            });
            element.classList.add('bg-primary-subtle', 'border-primary');
            
            // Select radio
            element.querySelector('input[type="radio"]').checked = true;
            
            // Save answer
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
                    // Update status badge
                    const statusBadge = document.getElementById('status-' + questionId);
                    statusBadge.textContent = 'Answered';
                    statusBadge.classList.remove('bg-light', 'text-dark');
                    statusBadge.classList.add('bg-success');
                    
                    // Update navigator
                    const navBtn = document.getElementById('nav-' + questionId);
                    navBtn.classList.remove('btn-outline-secondary');
                    navBtn.classList.add('btn-success');
                    
                    // Update progress
                    answeredCount = data.answered_count;
                    document.getElementById('answered-count').textContent = answeredCount;
                    document.getElementById('progress-bar').style.width = 
                        (totalQuestions > 0 ? (answeredCount / totalQuestions) * 100 : 0) + '%';
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
    </script>
    @endpush
</x-app-layout>

