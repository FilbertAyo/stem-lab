@pushOnce('head')
<style>
  .auth-top-bar {
    position: fixed;
    top: 0;
    left: 0;
    z-index: 100;
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 1rem 1.25rem;
  }

  .auth-top-bar__back {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 40px;
    height: 40px;
    border-radius: 50%;
    border: 1px solid #e5e7eb;
    background: #fff;
    color: #212529;
    font-size: 1.125rem;
    text-decoration: none;
    box-shadow: 0 1px 4px rgba(0, 0, 0, 0.06);
    transition: border-color 0.2s ease, box-shadow 0.2s ease, transform 0.15s ease;
    flex-shrink: 0;
    cursor: pointer;
    padding: 0;
  }

  .auth-top-bar__back:hover,
  .auth-top-bar__back:focus-visible {
    border-color: #212529;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    color: #212529;
    outline: none;
  }

  .auth-top-bar__logo {
    display: inline-flex;
    align-items: center;
    text-decoration: none;
  }

  .auth-top-bar__logo-img {
    height: 36px;
    width: auto;
  }
</style>
@endPushOnce

<header class="auth-top-bar">
  <button
    type="button"
    id="auth-top-back"
    class="auth-top-bar__back"
    data-auth-back
    data-fallback="{{ url('/') }}"
    aria-label="Go back"
  >
    <iconify-icon icon="lucide:arrow-left"></iconify-icon>
  </button>
  <a href="{{ url('/') }}" class="auth-top-bar__logo">
    <img
      src="{{ asset('images/logo/logo-dark.png') }}"
      alt="{{ config('app.name') }}"
      class="auth-top-bar__logo-img"
    >
  </a>
</header>

@pushOnce('scripts')
<script>
  document.addEventListener('DOMContentLoaded', function() {
    const backBtn = document.querySelector('[data-auth-back]');
    if (!backBtn) {
      return;
    }

    backBtn.addEventListener('click', function() {
      if (typeof window.handleAuthBack === 'function' && window.handleAuthBack()) {
        return;
      }

      const fallback = backBtn.dataset.fallback || '/';

      if (window.history.length > 1) {
        history.back();
        return;
      }

      window.location.href = fallback;
    });
  });
</script>
@endPushOnce
