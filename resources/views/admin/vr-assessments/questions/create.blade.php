<x-admin-layout>
    <div class="row">
        <div class="col-md-12 col-xl-12">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h5 class="mb-0">Add Question</h5>
                    <p class="text-muted mb-0 small">Bank: {{ $questionBank->title }}</p>
                </div>
                <a href="{{ route('admin.vr-assessments.question-banks.show', $questionBank) }}" class="btn btn-secondary">
                    <i class="ti ti-arrow-left"></i> Back to Question Bank
                </a>
            </div>

            <div class="card">
                <div class="card-body">
                    <form action="{{ route('admin.vr-assessments.questions.store', $questionBank) }}" method="POST">
                        @csrf

                        <div class="row">
                            <div class="col-md-8 mb-3">
                                <label for="question_type" class="form-label">Question Type <span class="text-danger">*</span></label>
                                <select class="form-select @error('question_type') is-invalid @enderror"
                                        id="question_type"
                                        name="question_type"
                                        required>
                                    <option value="">Select question type</option>
                                    <option value="mcq" {{ old('question_type') === 'mcq' ? 'selected' : '' }}>Multiple Choice (MCQ)</option>
                                    <option value="true_false" {{ old('question_type') === 'true_false' ? 'selected' : '' }}>True / False</option>
                                    <option value="short_answer" {{ old('question_type') === 'short_answer' ? 'selected' : '' }}>Short Answer</option>
                                </select>
                                @error('question_type')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="points" class="form-label">Points <span class="text-danger">*</span></label>
                                <input type="number" class="form-control @error('points') is-invalid @enderror"
                                       id="points" name="points" value="{{ old('points', 1) }}"
                                       min="1" max="100" required>
                                @error('points')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="question_text" class="form-label">Question Text <span class="text-danger">*</span></label>
                            <textarea class="form-control @error('question_text') is-invalid @enderror"
                                      id="question_text"
                                      name="question_text"
                                      rows="3"
                                      required>{{ old('question_text') }}</textarea>
                            @error('question_text')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3" id="options-container" style="display: none;">
                            <label class="form-label">Options (for MCQ) <span class="text-danger">*</span></label>
                            <div id="options-list">
                                <div class="input-group mb-2">
                                    <span class="input-group-text">A</span>
                                    <input type="text" class="form-control" name="options[]" placeholder="Option A">
                                    <button type="button" class="btn btn-outline-danger" onclick="removeOption(this)">
                                        <i class="ti ti-trash"></i>
                                    </button>
                                </div>
                                <div class="input-group mb-2">
                                    <span class="input-group-text">B</span>
                                    <input type="text" class="form-control" name="options[]" placeholder="Option B">
                                    <button type="button" class="btn btn-outline-danger" onclick="removeOption(this)">
                                        <i class="ti ti-trash"></i>
                                    </button>
                                </div>
                                <div class="input-group mb-2">
                                    <span class="input-group-text">C</span>
                                    <input type="text" class="form-control" name="options[]" placeholder="Option C">
                                    <button type="button" class="btn btn-outline-danger" onclick="removeOption(this)">
                                        <i class="ti ti-trash"></i>
                                    </button>
                                </div>
                                <div class="input-group mb-2">
                                    <span class="input-group-text">D</span>
                                    <input type="text" class="form-control" name="options[]" placeholder="Option D">
                                    <button type="button" class="btn btn-outline-danger" onclick="removeOption(this)">
                                        <i class="ti ti-trash"></i>
                                    </button>
                                </div>
                            </div>
                            <button type="button" class="btn btn-sm btn-outline-primary" onclick="addOption()">
                                <i class="ti ti-plus"></i> Add Option
                            </button>
                            @error('options')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Correct Answer <span class="text-danger">*</span></label>
                            <div id="correct-answer-text-wrapper">
                                <input type="text"
                                       class="form-control @error('correct_answer') is-invalid @enderror"
                                       id="correct_answer"
                                       name="correct_answer"
                                       value="{{ old('correct_answer') }}"
                                       required>
                                <small class="form-text text-muted" id="correct-answer-help">
                                    For MCQ: enter the exact option text. For Short Answer: enter the expected answer.
                                </small>
                            </div>
                            <div id="correct-answer-boolean-wrapper" style="display: none;">
                                <select class="form-select" id="correct_answer_boolean">
                                    <option value="True" {{ old('correct_answer') === 'True' ? 'selected' : '' }}>True</option>
                                    <option value="False" {{ old('correct_answer') === 'False' ? 'selected' : '' }}>False</option>
                                </select>
                                <small class="form-text text-muted">
                                    Select whether the statement is true or false.
                                </small>
                            </div>
                            @error('correct_answer')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="explanation" class="form-label">Explanation (Optional)</label>
                            <textarea class="form-control @error('explanation') is-invalid @enderror"
                                      id="explanation"
                                      name="explanation"
                                      rows="2">{{ old('explanation') }}</textarea>
                            <small class="form-text text-muted">This will be shown to students after they submit their answer.</small>
                            @error('explanation')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('admin.vr-assessments.question-banks.show', $questionBank) }}" class="btn btn-secondary">Cancel</a>
                            <button type="submit" class="btn btn-primary">
                                <i class="ti ti-check"></i> Create Question
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            const questionType = document.getElementById('question_type');
            const optionsContainer = document.getElementById('options-container');
            const correctAnswerInput = document.getElementById('correct_answer');
            const correctAnswerTextWrapper = document.getElementById('correct-answer-text-wrapper');
            const correctAnswerBooleanWrapper = document.getElementById('correct-answer-boolean-wrapper');
            const correctAnswerBooleanSelect = document.getElementById('correct_answer_boolean');
            const correctAnswerHelp = document.getElementById('correct-answer-help');

            function syncBooleanValue() {
                correctAnswerInput.value = correctAnswerBooleanSelect.value;
            }

            function handleQuestionTypeChange() {
                const isMcq = questionType.value === 'mcq';
                const isTrueFalse = questionType.value === 'true_false';
                const isShortAnswer = questionType.value === 'short_answer';

                optionsContainer.style.display = isMcq ? 'block' : 'none';
                correctAnswerTextWrapper.style.display = isTrueFalse ? 'none' : 'block';
                correctAnswerBooleanWrapper.style.display = isTrueFalse ? 'block' : 'none';

                if (isTrueFalse) {
                    syncBooleanValue();
                }

                if (isMcq) {
                    correctAnswerHelp.textContent = 'Enter the exact option text that is correct.';
                } else if (isShortAnswer) {
                    correctAnswerHelp.textContent = 'Enter the expected answer. Will be compared case-insensitively.';
                }
            }

            questionType.addEventListener('change', handleQuestionTypeChange);
            correctAnswerBooleanSelect.addEventListener('change', syncBooleanValue);

            let optionCount = 4;
            const optionLabels = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J'];

            function addOption() {
                const optionsList = document.getElementById('options-list');
                if (optionCount >= 10) {
                    alert('Maximum 10 options allowed.');
                    return;
                }
                const newOption = document.createElement('div');
                newOption.className = 'input-group mb-2';
                newOption.innerHTML = `
                    <span class="input-group-text">${optionLabels[optionCount]}</span>
                    <input type="text" class="form-control" name="options[]" placeholder="Option ${optionLabels[optionCount]}">
                    <button type="button" class="btn btn-outline-danger" onclick="removeOption(this)">
                        <i class="ti ti-trash"></i>
                    </button>
                `;
                optionsList.appendChild(newOption);
                optionCount++;
            }

            function removeOption(button) {
                const optionsList = document.getElementById('options-list');
                if (optionsList.children.length > 2) {
                    button.closest('.input-group').remove();
                    optionCount--;
                    updateOptionLabels();
                } else {
                    alert('At least 2 options are required for MCQ questions.');
                }
            }

            function updateOptionLabels() {
                const optionsList = document.getElementById('options-list');
                const inputs = optionsList.querySelectorAll('.input-group');
                inputs.forEach((input, index) => {
                    const label = input.querySelector('.input-group-text');
                    if (label) {
                        label.textContent = optionLabels[index];
                    }
                });
            }

            // Initialize UI based on pre-selected value
            handleQuestionTypeChange();
        </script>
    @endpush
</x-admin-layout>

