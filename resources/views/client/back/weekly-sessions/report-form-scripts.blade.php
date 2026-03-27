<script>
    document.addEventListener('DOMContentLoaded', function () {
        const activityCompleted = document.getElementById('activity_completed');
        const incompletionWrap = document.getElementById('incompletion_reason_wrap');
        const sections = Array.from(document.querySelectorAll('.report-section'));
        const progressText = document.getElementById('progressText');

        function toggleIncompletionReason() {
            if (!activityCompleted || !incompletionWrap) {
                return;
            }

            const show = activityCompleted.value === 'partially_completed' || activityCompleted.value === 'not_completed';
            incompletionWrap.classList.toggle('d-none', !show);
        }

        function updateProgress() {
            if (!progressText || sections.length === 0) {
                return;
            }

            let visibleIndex = 1;
            const top = window.scrollY + 180;

            for (let i = 0; i < sections.length; i++) {
                if (sections[i].offsetTop <= top) {
                    visibleIndex = i + 1;
                } else {
                    break;
                }
            }

            progressText.textContent = 'Section ' + visibleIndex + ' of ' + sections.length;
        }

        if (activityCompleted) {
            activityCompleted.addEventListener('change', toggleIncompletionReason);
        }

        window.addEventListener('scroll', updateProgress, { passive: true });

        toggleIncompletionReason();
        updateProgress();
    });
</script>
