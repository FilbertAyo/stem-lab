@push('scripts')
    <script src="{{ asset('admin/js/plugins/tinymce/tinymce.js') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            if (typeof tinymce === 'undefined') {
                return;
            }
            tinymce.init({
                selector: '.js-tinymce-competition',
                height: 320,
                menubar: false,
                branding: false,
                promotion: false,
                plugins: 'lists link image table code autoresize',
                toolbar: 'undo redo | blocks | bold italic underline strikethrough | alignleft aligncenter alignright | bullist numlist | link image table | code removeformat',
                block_formats: 'Paragraph=p; Heading 2=h2; Heading 3=h3',
                content_style: 'body { font-family: Public Sans, -apple-system, BlinkMacSystemFont, Segoe UI, sans-serif; font-size: 14px; line-height: 1.6; }',
                browser_spellcheck: true,
                relative_urls: false,
                convert_urls: false,
            });
        });
    </script>
@endpush
