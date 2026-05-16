@extends('layouts.landing')

@section('title', 'Competitions | ' . config('app.name'))
@section('page', 'competitions')

@push('styles')
<style>
  .competition-card-link {
    transition: box-shadow 0.28s ease, transform 0.28s ease, border-color 0.28s ease, opacity 0.28s ease;
    border: 1px solid rgba(0, 0, 0, 0.06);
  }
  .competition-card-link:hover,
  .competition-card-link:focus-within {
    transform: translateY(-6px);
    box-shadow: 0 1rem 2rem rgba(0, 0, 0, 0.12) !important;
    border-color: var(--bs-primary, #0d6efd);
  }
  .competition-card-link .overflow-hidden img {
    transition: transform 0.45s ease;
  }
  .competition-card-link:hover .overflow-hidden img,
  .competition-card-link:focus-within .overflow-hidden img {
    transform: scale(1.04);
  }
</style>
@endpush

@section('content')
<main>
  <!--  Banner Section -->
  <section class="banner-section banner-inner-section position-relative overflow-hidden d-flex align-items-end"
    style="background-image: url('/client/assets/images/backgrounds/competition.jpg');">
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
  <section class="project py-lg-11 py-xl-12 bg-light">
    <div class="container">
      <div class="my-3 mb-lg-10" data-aos="fade-up" data-aos-delay="100" data-aos-duration="1000">

        @php
          $registrationOpenNow = $activeCompetitions->contains(fn ($c) => $c->isPhaseOpen('registration'));
        @endphp
        @if($registrationOpenNow)
          <span class="badge mb-3 rounded-pill bg-success text-white">Registration open</span>
        @else
          <span class="badge mb-3 rounded-pill bg-danger text-white">Registration closed</span>
        @endif
        <h2 class="mb-0">Active Competitions</h2>
      </div>
      <div class="row">
        @foreach($activeCompetitions as $index => $competition)
          <div class="col-lg-6 mb-7">
            <div class="card competition-card-link h-100 d-flex flex-column position-relative shadow-sm cursor-pointer" data-aos="fade-up" data-aos-delay="{{ ($index % 2 == 0) ? 100 : 200 }}" data-aos-duration="1000">
              <div class="overflow-hidden">
                @if($competition->cover_image)
                  <img src="{{ Storage::url($competition->cover_image) }}" alt="" class="card-img-top img-fluid w-100">
                @endif
              </div>
              <div class="card-body d-flex flex-column gap-3 portfolio-details">
                <h3 class="mb-0">{{ $competition->name }}</h3>
                <div class="hstack gap-2 flex-wrap">
                  <span class="badge text-dark border">{{ $competition->club->name }}</span>
                  <span class="badge text-dark border">Current stage: {{ $competition->currentPhase()?->name ?? 'Upcoming' }}</span>
                  @if($competition->registration_deadline)
                    <span class="badge text-dark border">Deadline: {{ $competition->registration_deadline->format('d M') }}</span>
                  @endif
                  @if($competition->competition_start && $competition->competition_end)
                    <span class="badge text-dark border">{{ $competition->competition_start->format('d M') }} - {{ $competition->competition_end->format('d M Y') }}</span>
                  @endif
                </div>
                @auth
                  @if(auth()->user()->role === 'teacher')
                    <div class="d-flex flex-wrap gap-2">
                      <a href="{{ route('client.competitions.index', ['competition' => $competition->ulid]) }}" class="badge bg-danger text-white">Register now</a>
                    </div>
                  @endif
                @endauth
                <p class="mb-0 text-opacity-70">{{ Str::limit(Str::squish(strip_tags((string) ($competition->description ?? ''))), 120) }}</p>
                <a href="{{ route('competitions.public.show', $competition) }}" class="stretched-link">
                  <span class="visually-hidden">{{ $competition->name }}</span>
                </a>
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
            <div class="card competition-card-link h-100 d-flex flex-column position-relative shadow-sm cursor-pointer" data-aos="fade-up" data-aos-delay="{{ ($index % 2 == 0) ? 300 : 400 }}" data-aos-duration="1000" style="opacity: 0.9;">
              <div class="overflow-hidden">
                @if($competition->cover_image)
                  <img src="{{ Storage::url($competition->cover_image) }}" alt="" class="card-img-top img-fluid w-100" style="filter: grayscale(20%);">
                @else
                  <div class="w-100" style="height: 400px; background: linear-gradient(135deg, #f59e0b 0%, #fbbf24 100%); display: flex; align-items: center; justify-content: center;">
                    <iconify-icon icon="lucide:clock" class="fs-1 text-white"></iconify-icon>
                  </div>
                @endif
              </div>
              <div class="card-body d-flex flex-column gap-3 portfolio-details">
                <h3 class="mb-0">{{ $competition->name }}</h3>
                <div class="hstack gap-2 flex-wrap">
                  <span class="badge text-dark border">Starts {{ $competition->competition_start->format('d M Y') }}</span>
                  <span class="badge text-dark border">{{ $competition->phases->count() }} phases</span>
                </div>
                <p class="mb-0 text-opacity-70">{{ Str::limit(Str::squish(strip_tags((string) ($competition->description ?? ''))), 120) }}</p>
                <a href="{{ route('competitions.public.show', $competition) }}" class="stretched-link">
                  <span class="visually-hidden">{{ $competition->name }}</span>
                </a>
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
            <div class="card competition-card-link h-100 d-flex flex-column position-relative shadow-sm cursor-pointer" data-aos="fade-up" data-aos-delay="{{ ($index % 2 == 0) ? 500 : 600 }}" data-aos-duration="1000" style="opacity: 0.85;">
              <div class="overflow-hidden">
                @if($competition->cover_image)
                  <img src="{{ Storage::url($competition->cover_image) }}" alt="" class="card-img-top img-fluid w-100" style="filter: grayscale(40%);">
                @else
                  <div class="w-100" style="height: 400px; background: linear-gradient(135deg, #6b7280 0%, #9ca3af 100%); display: flex; align-items: center; justify-content: center;">
                    <iconify-icon icon="lucide:trophy" class="fs-1 text-white"></iconify-icon>
                  </div>
                @endif
              </div>
              <div class="card-body d-flex flex-column gap-3 portfolio-details">
                <h3 class="mb-0">{{ $competition->name }}</h3>
                <div class="hstack gap-2 flex-wrap">
                  <span class="badge text-dark border">{{ $competition->club->name }}</span>
                  <span class="badge text-dark border">Ended {{ $competition->competition_end->format('d M Y') }}</span>
                  <span class="badge text-dark border"><i class="ti ti-users"></i> {{ $competition->registrations_count ?? 0 }} schools</span>
                </div>
                <p class="mb-0 text-opacity-70">Results available - View winners and competition outcomes</p>
                <a href="{{ route('competitions.public.show', $competition) }}" class="stretched-link">
                  <span class="visually-hidden">{{ $competition->name }}</span>
                </a>
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
      <div class="card text-center" data-aos="fade-up" data-aos-delay="100" data-aos-duration="1000">
        <div class="card-body">
          <iconify-icon icon="lucide:trophy" class="fs-1 text-opacity-30 mb-4"></iconify-icon>
          <h3 class="mb-0">No Competitions Yet</h3>
          <p class="text-opacity-70 mt-3">Check back soon for exciting STEM competitions!</p>
        </div>
      </div>
    </div>
  </section>
  @endif
</main>
@endsection

