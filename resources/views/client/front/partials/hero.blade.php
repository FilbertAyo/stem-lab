<section class="banner-section position-relative d-flex align-items-end min-vh-100" style="background-color: #0f2749;">
      <video id="hero-video" class="position-absolute top-0 start-0 w-100 h-100 object-fit-cover" muted loop playsinline preload="none">
        <source data-src="{{ asset('images/hero.mp4') }}" type="video/mp4" />
      </video>
      <div class="container">
        <div class="d-flex flex-column gap-4 pb-8 position-relative z-1">
          <div class="row align-items-center">
            <div class="col-xl-4">
              <div class="d-flex align-items-center gap-4" data-aos="fade-up" data-aos-delay="100"
                data-aos-duration="1000">
                <!-- <img src="{{ asset('client/assets/images/svgs/primary-leaf.svg') }}" alt="" class="img-fluid animate-spin"> -->
                <p class="mb-0 text-white fs-5 text-opacity-70">
                  Empowering children through access to quality <span class="text-primary">STEM education</span> using innovative VR headsets and immersive, hands on learning experiences.
                </p>
              </div>
            </div>
          </div>
          <div class="d-flex align-items-end gap-3" data-aos="fade-up" data-aos-delay="200" data-aos-duration="1000">
            <h1 class="mb-0 fs-16 text-white lh-2">STEM Labs</h1>
            <a href="javascript:void(0)" class="p-1 ps-7 bg-primary rounded-pill">
              <span class="bg-white round-52 rounded-circle d-flex align-items-center justify-content-center">
                <iconify-icon icon="lucide:arrow-up-right" class="fs-8 text-dark"></iconify-icon>
              </span>
            </a>
          </div>
        </div>
      </div>
    </section>
    <script>
      (function () {
        var video = document.getElementById('hero-video');
        var source = video && video.querySelector('source');
        if (!video || !source) return;

        var connection = navigator.connection || navigator.mozConnection || navigator.webkitConnection;
        if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;
        if (connection && (connection.saveData || connection.effectiveType === 'slow-2g' || connection.effectiveType === '2g')) return;

        function startHeroVideo() {
          if (source.getAttribute('src')) return;
          source.src = source.getAttribute('data-src');
          video.load();
          var play = video.play();
          if (play && typeof play.catch === 'function') play.catch(function () {});
        }

        if ('requestIdleCallback' in window) {
          requestIdleCallback(startHeroVideo, { timeout: 1500 });
        } else {
          window.addEventListener('load', function () {
            setTimeout(startHeroVideo, 300);
          });
        }
      })();
    </script>
