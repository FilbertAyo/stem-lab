<!-- ===== Hero Start ===== -->
<section class="gj do ir hj sp jr i pg">
  <!-- Hero Images -->
  <div class="xc fn zd/2 2xl:ud-w-187.5 bd 2xl:ud-h-171.5 h q r">
    <img src="{{ asset('images/shape-04.svg') }}" alt="shape" class="h q r" loading="lazy" decoding="async" />
    <model-viewer
      src="{{ asset('images/microscope.glb') }}"
      alt="Interactive heart model"
      auto-rotate
      camera-controls
      touch-action="pan-y"
      shadow-intensity="1"
      class="h q r"
      style="width: 100%; height: 100%; min-height: 420px;"
    ></model-viewer>

    @once
      @push('scripts')
        <script
          type="module"
          src="https://unpkg.com/@google/model-viewer@3.3.0/dist/model-viewer.min.js"
          async
        ></script>
      @endpush
    @endonce
  </div>

  <!-- Hero Content -->
  <div class="bb ze ki xn 2xl:ud-px-0">
    <div class="tc _o">
      <div class="animate_left jn/2">
        <h1 class="fk vj zp or kk wm wb">Immersive STEM labs designed for every girl in Tanzania.</h1>
        <p class="fq">
          Adilisha STEM Lab delivers browser-based and headset VR experiences that let learners practice real science,
          explore cutting-edge technology, and build confidence without needing physical equipment.
        </p>

        <div class="tc tf yo zf mb">
          <a href="{{ route('register') }}" class="ek jk lk gh gi hi rg ml il vc _d _l">Explore the Labs</a>

          <span class="tc sf">
            @php
                $sitePhone = \App\Models\Setting::get('site_phone', '+255 282 561 724');
                $whatsappUrl = \App\Models\Setting::get('whatsapp_url');
            @endphp
            @if(!empty($whatsappUrl))
                <a href="{{ $whatsappUrl }}" target="_blank" rel="noopener noreferrer" class="inline-block ek xj kk wm"> Call us {{ $sitePhone }} </a>
            @else
                <a href="tel:{{ preg_replace('/[^0-9+]/', '', $sitePhone) }}" class="inline-block ek xj kk wm"> Call us {{ $sitePhone }} </a>
            @endif
            <span class="inline-block">Let's co-create the future of STEM education</span>
          </span>
        </div>
      </div>
    </div>
  </div>
</section>

