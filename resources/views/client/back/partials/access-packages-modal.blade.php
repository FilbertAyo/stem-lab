<div class="modal fade" id="access-packages-modal" tabindex="-1" aria-labelledby="access-packages-modal-label" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content border-0">
      <div class="modal-header border-0 pb-0">
        <div>
          <h5 class="modal-title fw-semibold" id="access-packages-modal-label">Choose a package to continue</h5>
          <p class="text-muted small mb-0">Browsing is free. A package unlocks this item.</p>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body pt-3">
        @include('client.back.partials.package', ['embedInModal' => true])
      </div>
    </div>
  </div>
</div>

@push('scripts')
<script>
(function () {
  var modalEl = document.getElementById('access-packages-modal');
  if (!modalEl || typeof bootstrap === 'undefined' || !bootstrap.Modal) {
    return;
  }

  var engagement = [
    /^\/vr-experiments\/.+/,
    /^\/models\/3d\/.+/,
    /^\/projects\/.+/,
    /^\/vr-quizzes\/.+/,
    /^\/weekly-sessions\/.+/,
    /^\/competitions\/.+/,
    /^\/my-students\/.+/
  ];

  function showPackages(event) {
    if (event) {
      event.preventDefault();
    }
    bootstrap.Modal.getOrCreateInstance(modalEl).show();
  }

  document.addEventListener('click', function (event) {
    var link = event.target.closest('a[href]');
    if (!link || link.target === '_blank' || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) {
      return;
    }
    var url;
    try {
      url = new URL(link.href, window.location.origin);
    } catch (error) {
      return;
    }
    if (url.origin !== window.location.origin) {
      return;
    }
    var path = url.pathname.replace(/\/$/, '');
    var blocked = engagement.some(function (pattern) {
      return pattern.test(path);
    });
    if (!blocked) {
      return;
    }
    showPackages(event);
  });

  @if (session('open_packages'))
  document.addEventListener('DOMContentLoaded', function () {
    showPackages();
  });
  @endif
})();
</script>
@endpush
