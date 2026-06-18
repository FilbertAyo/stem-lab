{{-- Include on multipart forms. Pass $uploadLimits: ['field_name' => ['label' => 'Thumbnail', 'max_kb' => 1024]] --}}
<script>
(function () {
    var limits = @json($uploadLimits ?? []);

    document.querySelectorAll('form[data-validate-uploads]').forEach(function (form) {
        form.addEventListener('submit', function (event) {
            var errors = [];

            Object.keys(limits).forEach(function (field) {
                var input = form.querySelector('[name="' + field + '"]');
                if (!input || !input.files || !input.files.length) {
                    return;
                }

                var file = input.files[0];
                var config = limits[field];
                var maxBytes = config.max_kb * 1024;

                if (file.size > maxBytes) {
                    var maxLabel = config.max_kb >= 1024 && config.max_kb % 1024 === 0
                        ? (config.max_kb / 1024) + ' MB'
                        : (config.max_kb >= 1024
                            ? (Math.round((config.max_kb / 1024) * 10) / 10) + ' MB'
                            : config.max_kb + ' KB');

                    errors.push({
                        field: field,
                        message: 'The ' + config.label.toLowerCase() + ' must not be larger than ' + maxLabel + '.'
                    });
                }
            });

            if (!errors.length) {
                return;
            }

            event.preventDefault();

            errors.forEach(function (error) {
                var input = form.querySelector('[name="' + error.field + '"]');
                if (!input) {
                    return;
                }

                input.classList.add('is-invalid');

                var feedback = input.parentElement.querySelector('.client-upload-error');
                if (!feedback) {
                    feedback = document.createElement('div');
                    feedback.className = 'invalid-feedback d-block client-upload-error';
                    input.parentElement.appendChild(feedback);
                }

                feedback.textContent = error.message;
            });

            errors[0].field && form.querySelector('[name="' + errors[0].field + '"]')?.scrollIntoView({ behavior: 'smooth', block: 'center' });
        });

        form.querySelectorAll('input[type="file"]').forEach(function (input) {
            input.addEventListener('change', function () {
                input.classList.remove('is-invalid');
                var feedback = input.parentElement.querySelector('.client-upload-error');
                if (feedback) {
                    feedback.remove();
                }
            });
        });
    });
})();
</script>
