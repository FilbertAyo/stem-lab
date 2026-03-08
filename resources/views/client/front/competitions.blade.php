@extends('layouts.landing')

@section('title', 'Competitions | ' . config('app.name'))
@section('page', 'competitions')

@section('content')
<main>
  <!--  Banner Section -->
  <section class="banner-section banner-inner-section position-relative overflow-hidden d-flex align-items-end"
    style="background-image: url({{ asset('client/assets/images/backgrounds/competition.jpg') }});">
    <div class="container">
      <div class="d-flex flex-column gap-4 pb-5 pb-xl-10 position-relative z-1">
        <div class="row align-items-center">
          <div class="col-xl-4">
            <div class="d-flex align-items-center gap-4" data-aos="fade-up" data-aos-delay="100"
              data-aos-duration="1000">
              <!-- <img src="{{ asset('client/assets/images/svgs/primary-leaf.svg') }}" alt="" class="img-fluid animate-spin"> -->
              <p class="mb-0 text-white fs-5 text-opacity-70">Join exciting <span class="text-primary">STEM competitions</span> designed for school clubs across Tanzania.</p>
            </div>
          </div>
        </div>
        <div class="d-flex align-items-end gap-3" data-aos="fade-up" data-aos-delay="200" data-aos-duration="1000">
          <h1 class="mb-0 fs-16 text-white lh-1">Competitions</h1>
          <a href="javascript:void(0)" class="p-1 ps-7 bg-primary rounded-pill">
            <span class="bg-white round-52 rounded-circle d-flex align-items-center justify-content-center">
              <iconify-icon icon="lucide:arrow-up-right" class="fs-8 text-dark"></iconify-icon>
            </span>
          </a>
        </div>
      </div>
    </div>
  </section>

  <!--  Active Competitions Section -->
  @if($activeCompetitions->count() > 0)
  <section class="project py-5 py-lg-11 py-xl-12">
    <div class="container">
      <div class="mb-7 mb-lg-10" data-aos="fade-up" data-aos-delay="100" data-aos-duration="1000">
        <span class="badge text-dark border mb-3" style="background: #dcfce7; color: #16a34a; border-color: #16a34a !important;">
          <i class="ti ti-flame"></i> Open for Registration
        </span>
        <h2 class="mb-0">Active Competitions</h2>
      </div>
      <div class="row">
        @foreach($activeCompetitions as $index => $competition)
          <div class="col-lg-6 mb-7">
            <div class="portfolio d-flex flex-column gap-6" data-aos="fade-up" data-aos-delay="{{ ($index % 2 == 0) ? 100 : 200 }}"
              data-aos-duration="1000">
              <div class="portfolio-img position-relative overflow-hidden">
                @if($competition->cover_image)
                  <img src="{{ Storage::url($competition->cover_image) }}" alt="{{ $competition->name }}" class="img-fluid w-100">
                @else
                  <div class="w-100" style="height: 400px; background: linear-gradient(135deg, #0077b6 0%, #00b4d8 100%); display: flex; align-items: center; justify-content: center;">
                    <iconify-icon icon="lucide:trophy" class="fs-1 text-white"></iconify-icon>
                  </div>
                @endif
                <div class="portfolio-overlay">
                  <a href="{{ route('competitions.public.show', $competition) }}"
                    class="position-absolute top-50 start-50 translate-middle bg-primary round-64 rounded-circle hstack justify-content-center">
                    <iconify-icon icon="lucide:arrow-up-right" class="fs-8 text-dark"></iconify-icon>
                  </a>
                </div>
              </div>
              <div class="portfolio-details d-flex flex-column gap-3">
                <h3 class="mb-0">{{ $competition->name }}</h3>
                <div class="hstack gap-2 flex-wrap">
                  <span class="badge text-dark border">{{ $competition->club->name }}</span>
                  @if($competition->registration_deadline)
                    <span class="badge text-dark border">Deadline: {{ $competition->registration_deadline->format('d M') }}</span>
                  @endif
                  <span class="badge text-dark border">{{ $competition->competition_start->format('d M') }} - {{ $competition->competition_end->format('d M Y') }}</span>
                </div>
                <p class="mb-0 text-opacity-70">{{ Str::limit($competition->description, 120) }}</p>
              </div>
            </div>
          </div>
        @endforeach
      </div>
    </div>
  </section>
  @endif

  <!--  Upcoming Competitions Section -->
  @if($upcomingCompetitions->count() > 0)
  <section class="project py-5 py-lg-11 py-xl-12" style="background: #f9fafb;">
    <div class="container">
      <div class="mb-7 mb-lg-10" data-aos="fade-up" data-aos-delay="100" data-aos-duration="1000">
        <span class="badge text-dark border mb-3" style="background: #fef3c7; color: #92400e; border-color: #f59e0b !important;">
          <i class="ti ti-clock"></i> Coming Soon
        </span>
        <h2 class="mb-0">Upcoming Competitions</h2>
      </div>
      <div class="row">
        @foreach($upcomingCompetitions as $index => $competition)
          <div class="col-lg-6 mb-7">
            <div class="portfolio d-flex flex-column gap-6" data-aos="fade-up" data-aos-delay="{{ ($index % 2 == 0) ? 300 : 400 }}"
              data-aos-duration="1000" style="opacity: 0.9;">
              <div class="portfolio-img position-relative overflow-hidden">
                @if($competition->cover_image)
                  <img src="{{ Storage::url($competition->cover_image) }}" alt="{{ $competition->name }}" class="img-fluid w-100" style="filter: grayscale(20%);">
                @else
                  <div class="w-100" style="height: 400px; background: linear-gradient(135deg, #f59e0b 0%, #fbbf24 100%); display: flex; align-items: center; justify-content: center;">
                    <iconify-icon icon="lucide:clock" class="fs-1 text-white"></iconify-icon>
                  </div>
                @endif
                <div class="portfolio-overlay">
                  <a href="{{ route('competitions.public.show', $competition) }}"
                    class="position-absolute top-50 start-50 translate-middle bg-primary round-64 rounded-circle hstack justify-content-center">
                    <iconify-icon icon="lucide:arrow-up-right" class="fs-8 text-dark"></iconify-icon>
                  </a>
                </div>
              </div>
              <div class="portfolio-details d-flex flex-column gap-3">
                <h3 class="mb-0">{{ $competition->name }}</h3>
                <div class="hstack gap-2 flex-wrap">
                  <span class="badge text-dark border">Starts {{ $competition->competition_start->format('d M Y') }}</span>
                </div>
                <p class="mb-0 text-opacity-70">{{ Str::limit($competition->description, 120) }}</p>
              </div>
            </div>
          </div>
        @endforeach
      </div>
    </div>
  </section>
  @endif

  <!--  Completed Competitions Section -->
  @if($completedCompetitions->count() > 0)
  <section class="project py-5 py-lg-11 py-xl-12">
    <div class="container">
      <div class="mb-7 mb-lg-10" data-aos="fade-up" data-aos-delay="100" data-aos-duration="1000">
        <span class="badge text-dark border mb-3" style="background: #f3f4f6; color: #6b7280; border-color: #6b7280 !important;">
          <i class="ti ti-check"></i> Completed
        </span>
        <h2 class="mb-0">Past Competitions</h2>
        <p class="text-opacity-70 mt-2">View results and winners from previous competitions</p>
      </div>
      <div class="row">
        @foreach($completedCompetitions as $index => $competition)
          <div class="col-lg-6 mb-7">
            <div class="portfolio d-flex flex-column gap-6" data-aos="fade-up" data-aos-delay="{{ ($index % 2 == 0) ? 500 : 600 }}"
              data-aos-duration="1000" style="opacity: 0.85;">
              <div class="portfolio-img position-relative overflow-hidden">
                @if($competition->cover_image)
                  <img src="{{ Storage::url($competition->cover_image) }}" alt="{{ $competition->name }}" class="img-fluid w-100" style="filter: grayscale(40%);">
                @else
                  <div class="w-100" style="height: 400px; background: linear-gradient(135deg, #6b7280 0%, #9ca3af 100%); display: flex; align-items: center; justify-content: center;">
                    <iconify-icon icon="lucide:trophy" class="fs-1 text-white"></iconify-icon>
                  </div>
                @endif
                <div class="portfolio-overlay">
                  <a href="{{ route('competitions.public.show', $competition) }}"
                    class="position-absolute top-50 start-50 translate-middle bg-primary round-64 rounded-circle hstack justify-content-center">
                    <iconify-icon icon="lucide:arrow-up-right" class="fs-8 text-dark"></iconify-icon>
                  </a>
                </div>
              </div>
              <div class="portfolio-details d-flex flex-column gap-3">
                <h3 class="mb-0">{{ $competition->name }}</h3>
                <div class="hstack gap-2 flex-wrap">
                  <span class="badge text-dark border">{{ $competition->club->name }}</span>
                  <span class="badge text-dark border">Ended {{ $competition->competition_end->format('d M Y') }}</span>
                  <span class="badge text-dark border"><i class="ti ti-users"></i> {{ $competition->participants_count ?? 0 }} teams</span>
                </div>
                <p class="mb-0 text-opacity-70">Results available - View winners and competition outcomes</p>
              </div>
            </div>
          </div>
        @endforeach
      </div>
    </div>
  </section>
  @endif

  <!-- No Competitions -->
  @if($activeCompetitions->isEmpty() && $upcomingCompetitions->isEmpty() && $completedCompetitions->isEmpty())
  <section class="project py-5 py-lg-11 py-xl-12">
    <div class="container">
      <div class="text-center" data-aos="fade-up" data-aos-delay="100" data-aos-duration="1000">
        <iconify-icon icon="lucide:trophy" class="fs-1 text-opacity-30 mb-4"></iconify-icon>
        <h3 class="mb-0">No Competitions Yet</h3>
        <p class="text-opacity-70 mt-3">Check back soon for exciting STEM competitions!</p>
      </div>
    </div>
  </section>
  @endif
</main>
@endsection

