<x-admin-layout>
    <div class="row">
        <div class="col-md-12 col-xl-12">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h5 class="mb-0">Add Question</h5>
                    <p class="text-muted mb-0 small">Model: {{ $model3d->name }}</p>
                </div>
                <a href="{{ route('admin.models.3d.questions.index', $model3d) }}" class="btn btn-secondary">
                    <i class="ti ti-arrow-left"></i> Back to Questions
                </a>
            </div>

            <div class="card">
                <div class="card-body">
                    <form action="{{ route('admin.models.3d.questions.store', $model3d) }}" method="POST">
                        @csrf

                        <div class="mb-3">
                            <label for="question_type" class="form-label">Question Type <span class="text-danger">*</span></label>
                            <select class="form-select @error('question_type') is-invalid @enderror"
                                    id="question_type"
                                    name="question_type"
                                    required>
                                <option value="">Select question type</option>
                                <option value="mcq" {{ old('question_type') === 'mcq' ? 'selected' : '' }}>Multiple Choice (MCQ)</option>
                                <option value="true_false" {{ old('question_type') === 'true_false' ? 'selected' : '' }}>True / False</option>
                            </select>
                            @error('question_type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
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
                                    <input type="text" class="form-control" name="options[]" placeholder="Option 1">
                                    <button type="button" class="btn btn-outline-danger" onclick="removeOption(this)">
                                        <i class="ti ti-trash"></i>
                                    </button>
                                </div>
                                <div class="input-group mb-2">
                                    <input type="text" class="form-control" name="options[]" placeholder="Option 2">
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
                                <small class="form-text text-muted">
                                    For MCQ: enter the exact option text.
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
                            <a href="{{ route('admin.models.3d.questions.index', $model3d) }}" class="btn btn-secondary">Cancel</a>
                            <button type="submit" class="btn btn-primary">Create Question</button>
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

            function syncBooleanValue() {
                correctAnswerInput.value = correctAnswerBooleanSelect.value;
            }

            function handleQuestionTypeChange() {
                const isMcq = questionType.value === 'mcq';
                const isTrueFalse = questionType.value === 'true_false';

                optionsContainer.style.display = isMcq ? 'block' : 'none';
                correctAnswerTextWrapper.style.display = isTrueFalse ? 'none' : 'block';
                correctAnswerBooleanWrapper.style.display = isTrueFalse ? 'block' : 'none';

                if (isTrueFalse) {
                    syncBooleanValue();
                }
            }

            questionType.addEventListener('change', handleQuestionTypeChange);
            correctAnswerBooleanSelect.addEventListener('change', syncBooleanValue);

            function addOption() {
                const optionsList = document.getElementById('options-list');
                const optionCount = optionsList.children.length;
                const newOption = document.createElement('div');
                newOption.className = 'input-group mb-2';
                newOption.innerHTML = `
                    <input type="text" class="form-control" name="options[]" placeholder="Option ${optionCount + 1}">
                    <button type="button" class="btn btn-outline-danger" onclick="removeOption(this)">
                        <i class="ti ti-trash"></i>
                    </button>
                `;
                optionsList.appendChild(newOption);
            }

            function removeOption(button) {
                const optionsList = document.getElementById('options-list');
                if (optionsList.children.length > 2) {
                    button.closest('.input-group').remove();
                } else {
                    alert('At least 2 options are required for MCQ questions.');
                }
            }

            // Initialize UI based on pre-selected value
            handleQuestionTypeChange();
        </script>
    @endpush
</x-admin-layout>

