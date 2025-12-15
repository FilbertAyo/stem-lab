<section id="about" class="ji gp uq 2xl:ud-py-35 pg">
  <div class="bb ze ki xn wq">
    <div class="tc wf gg qq">
      <!-- About Images -->
      <div class="animate_left xc gn gg jn/2 i">
        <div>
          <img src="{{ asset('images/about-01.png') }}" alt="About" class="ib" loading="lazy" decoding="async" />
          <img src="{{ asset('images/about-02.png') }}" alt="About" loading="lazy" decoding="async" />
        </div>
        <div>
          <img src="{{ asset('images/shape-06.svg') }}" alt="Shape" loading="lazy" decoding="async" />
          <img src="{{ asset('images/about-03.png') }}" alt="About" class="ob gb" loading="lazy" decoding="async" />
          <img src="{{ asset('images/shape-07.svg') }}" alt="Shape" class="bb" loading="lazy" decoding="async" />
        </div>
      </div>

      <!-- About Content -->
      <div class="animate_right jn/2">
        <h4 class="ek yj mk gb">About STEM</h4>
        <h2 class="fk vj zp pr kk wm qb">Empowering girls to create, question and lead with technology.</h2>
        <p class="uo">
          Since 1999 Adilisha has worked to break stereotypes, amplify women's voices in technology,
          and open pathways for girls to explore STEM with curiosity and confidence. Our STEM Lab
          extends that mission through immersive, inclusive learning experiences.
        </p>

        @php
            $youtubeVideoUrl = \App\Models\Setting::get('youtube_video_url');
        @endphp
        @if(!empty($youtubeVideoUrl))
        <a href="{{ $youtubeVideoUrl }}" data-fslightbox class="vc wf hg mb">
          <span class="tc wf xf be dd rg i gh ua">
            <span class="nf h vc yc vd rg gh qk -ud-z-1"></span>
            <img src="{{ asset('images/icon-play.svg') }}" alt="Play" loading="lazy" decoding="async" />
          </span>
          <span class="kk">Discover how to use the platform</span>
        </a>
        @endif
      </div>
    </div>
  </div>
</section>