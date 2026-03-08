@extends('layouts.landing')

@section('title', $competition->name . ' | ' . config('app.name'))
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
              <img src="{{ asset('client/assets/images/svgs/primary-leaf.svg') }}" alt="" class="img-fluid animate-spin">
              <p class="mb-0 text-white fs-5 text-opacity-70">Join exciting <span class="text-primary">STEM competitions</span> and showcase your innovations.</p>
            </div>
          </div>
        </div>
        <div class="d-flex align-items-end gap-3" data-aos="fade-up" data-aos-delay="200" data-aos-duration="1000">
          <h1 class="mb-0 fs-16 text-white lh-1">{{ $competition->name }}</h1>
          <a href="javascript:void(0)" class="p-1 ps-7 bg-primary rounded-pill">
            <span class="bg-white round-52 rounded-circle d-flex align-items-center justify-content-center">
              <iconify-icon icon="lucide:arrow-up-right" class="fs-8 text-dark"></iconify-icon>
            </span>
          </a>
        </div>
      </div>
    </div>
  </section>

  <!--  Competition Detail Section -->
  <section class="project-detail py-5 py-lg-11 py-xl-12">
    <div class="container">
      <div class="d-flex flex-column gap-5 gap-xl-11">
        <div class="d-flex flex-column gap-8">
          <a href="{{ route('competitions.public') }}" class="btn py-2 ps-3 pe-5" data-aos="fade-up" data-aos-delay="100"
            data-aos-duration="1000">
            <span class="btn-text pe-1">Back</span>
            <iconify-icon icon="lucide:arrow-up-right"
              class="btn-icon bg-white text-dark round-36 rounded-circle hstack justify-content-center fs-5 shadow-sm"></iconify-icon>
          </a>
          <div class="d-md-flex align-items-center gap-4 gap-lg-8" data-aos="fade-up" data-aos-delay="200"
            data-aos-duration="1000">
            <div class="d-flex flex-column gap-2 py-2 pe-4 pe-lg-8 border-end">
              <p class="mb-0">Status</p>
              <p class="mb-0 text-dark fs-5 fw-medium">
                @if($competition->isRegistrationOpen())
                  <span class="badge" style="background: #dcfce7; color: #16a34a;">Open for Registration</span>
                @elseif($competition->status === 'ongoing')
                  <span class="badge" style="background: #dbeafe; color: #1d4ed8;">In Progress</span>
                @elseif($competition->status === 'completed')
                  <span class="badge" style="background: #fef3c7; color: #92400e;">Completed</span>
                @else
                  <span class="badge" style="background: #fee2e2; color: #dc2626;">Registration Closed</span>
                @endif
              </p>
            </div>
            <div class="d-flex flex-column gap-2 py-2 pe-4 pe-lg-8 border-end">
              <p class="mb-0">Club</p>
              <p class="mb-0 text-dark fs-5 fw-medium">{{ $competition->club->name }}</p>
            </div>
            <div class="d-flex flex-column gap-2 py-2 pe-4 pe-lg-8 border-end">
              <p class="mb-0">Team Size</p>
              <p class="mb-0 text-dark fs-5 fw-medium">{{ $competition->min_members }}-{{ $competition->max_members ?? '∞' }} members</p>
            </div>
            <div class="d-flex flex-column gap-2 py-2">
              <p class="mb-0">Participants</p>
              <p class="mb-0 text-dark fs-5 fw-medium">{{ $competition->participants_count ?? 0 }} teams</p>
            </div>
          </div>
        </div>

        <!-- Cover Image -->
        @if($competition->cover_image)
          <div class="row">
            <div class="col-12 mb-7" data-aos="fade-up" data-aos-delay="100" data-aos-duration="1000">
              <img src="{{ Storage::url($competition->cover_image) }}" alt="{{ $competition->name }}" class="w-100 object-fit-cover">
            </div>
          </div>
        @endif

        <!-- Description Section -->
        <div class="row gap-4 gap-lg-0">
          <div class="col-lg-4">
            <h2 class="fs-13 mb-0" data-aos="fade-right" data-aos-delay="200" data-aos-duration="1000">Description</h2>
          </div>
          <div class="col-lg-8">
            <div data-aos="fade-up" data-aos-delay="200" data-aos-duration="1000">
              <p class="fs-5 mb-6">
                {{ $competition->description ?: 'No description provided for this competition.' }}
              </p>
              
              @if($competition->instructions)
                <h4>How to Participate</h4>
                <div class="fs-5 mb-6" style="white-space: pre-line;">{{ $competition->instructions }}</div>
              @endif

              @if($competition->terms_and_conditions)
                <h4>Terms and Conditions</h4>
                <div class="fs-5 mb-0" style="white-space: pre-line;">{{ $competition->terms_and_conditions }}</div>
              @endif
            </div>
          </div>
        </div>

        <!-- Awards Section -->
        @if($competition->awards->count() > 0)
          <div class="row gap-4 gap-lg-0">
            <div class="col-lg-4">
              <h2 class="fs-13 mb-0" data-aos="fade-right" data-aos-delay="300" data-aos-duration="1000">Awards</h2>
            </div>
            <div class="col-lg-8">
              <div data-aos="fade-up" data-aos-delay="300" data-aos-duration="1000">
                <div class="row g-4">
                  @foreach($competition->awards as $award)
                    <div class="col-md-6">
                      <div class="text-center p-4 rounded border" style="background: {{ $award->position <= 3 ? '#fef3c7' : '#f3f4f6' }};">
                        @if($award->prize_image)
                          <img src="{{ Storage::url($award->prize_image) }}" alt="{{ $award->title }}" class="img-fluid mb-3" style="max-height: 80px;">
                        @else
                          <iconify-icon icon="lucide:award" class="fs-1 mb-3" style="color: {{ $award->position == 1 ? '#f59e0b' : ($award->position == 2 ? '#9ca3af' : '#b45309') }};"></iconify-icon>
                        @endif
                        <h5 class="mb-2">{{ $award->title }}</h5>
                        <p class="mb-2 text-opacity-70">{{ $award->position_label }}</p>
                        @if($award->prize_value)
                          <p class="mb-0 fs-5 fw-medium" style="color: #16a34a;">{{ number_format($award->prize_value) }} TZS</p>
                        @endif
                      </div>
                    </div>
                  @endforeach
                </div>
              </div>
            </div>
          </div>
        @endif

        <!-- Winners Section (if completed) -->
        @if($competition->status === 'completed' && isset($winners) && $winners->count() > 0)
          <div class="row gap-4 gap-lg-0">
            <div class="col-lg-4">
              <h2 class="fs-13 mb-0" data-aos="fade-right" data-aos-delay="400" data-aos-duration="1000">Winners</h2>
            </div>
            <div class="col-lg-8">
              <div data-aos="fade-up" data-aos-delay="400" data-aos-duration="1000">
                <div class="d-flex flex-column gap-3 p-5 rounded" style="background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);">
                  @foreach($winners as $winner)
                    <div class="d-flex align-items-center gap-4 p-3 rounded" style="background: white;">
                      <div class="round-64 rounded-circle d-flex align-items-center justify-content-center fw-bold text-white" style="background: {{ $winner->award->position == 1 ? '#f59e0b' : ($winner->award->position == 2 ? '#9ca3af' : '#b45309') }}; min-width: 64px; height: 64px;">
                        #{{ $winner->award->position }}
                      </div>
                      <div class="flex-grow-1">
                        <h5 class="mb-1">{{ $winner->team_name ?: 'Team #'.$winner->id }}</h5>
                        <p class="mb-1 text-opacity-70">{{ $winner->teacherClub->school->name ?? '' }}</p>
                        <span class="badge text-dark border">{{ $winner->award->title }}</span>
                      </div>
                    </div>
                  @endforeach
                </div>
              </div>
            </div>
          </div>
        @endif

        <!-- Timeline Section -->
        <div class="row gap-4 gap-lg-0">
          <div class="col-lg-4">
            <h2 class="fs-13 mb-0" data-aos="fade-right" data-aos-delay="500" data-aos-duration="1000">Timeline</h2>
          </div>
          <div class="col-lg-8">
            <div data-aos="fade-up" data-aos-delay="500" data-aos-duration="1000">
              <div class="d-flex flex-column gap-3">
                @if($competition->registration_start)
                  <div class="d-flex justify-content-between align-items-center py-3 border-bottom">
                    <span class="fs-5">Registration Opens</span>
                    <span class="fs-5 fw-medium">{{ $competition->registration_start->format('d M Y') }}</span>
                  </div>
                @endif
                @if($competition->registration_deadline)
                  <div class="d-flex justify-content-between align-items-center py-3 border-bottom">
                    <span class="fs-5">Registration Closes</span>
                    <span class="fs-5 fw-medium">{{ $competition->registration_deadline->format('d M Y') }}</span>
                  </div>
                @endif
                <div class="d-flex justify-content-between align-items-center py-3 border-bottom">
                  <span class="fs-5">Competition Start</span>
                  <span class="fs-5 fw-medium" style="color: #16a34a;">{{ $competition->competition_start->format('d M Y') }}</span>
                </div>
                <div class="d-flex justify-content-between align-items-center py-3 border-bottom">
                  <span class="fs-5">Competition End</span>
                  <span class="fs-5 fw-medium" style="color: #dc2626;">{{ $competition->competition_end->format('d M Y') }}</span>
                </div>
                @if($competition->results_announcement_date)
                  <div class="d-flex justify-content-between align-items-center py-3">
                    <span class="fs-5">Results Announcement</span>
                    <span class="fs-5 fw-medium">{{ $competition->results_announcement_date->format('d M Y') }}</span>
                  </div>
                @endif
              </div>
            </div>
          </div>
        </div>

        <!-- Action Section -->
        <div class="row">
          <div class="col-12">
            <div class="p-5 rounded" data-aos="fade-up" data-aos-delay="600" data-aos-duration="1000" style="background: linear-gradient(135deg, #0077b6 0%, #00b4d8 100%);">
              @if($competition->isRegistrationOpen())
                <h3 class="text-white mb-3">Ready to Join?</h3>
                <p class="text-white text-opacity-90 fs-5 mb-4">
                  Sign in or register to join this competition with your school club.
                </p>
                <a href="{{ route('login') }}" class="btn bg-white text-dark fs-5 px-5 py-3">
                  Get Started <iconify-icon icon="lucide:arrow-right" class="ms-2"></iconify-icon>
                </a>
              @elseif($competition->status === 'ongoing')
                <h3 class="text-white mb-3">Competition in Progress</h3>
                <p class="text-white text-opacity-90 fs-5 mb-0">
                  This competition is currently ongoing. Check back for results!
                </p>
              @elseif($competition->status === 'completed')
                <h3 class="text-white mb-3">Competition Ended</h3>
                <p class="text-white text-opacity-90 fs-5 mb-0">
                  This competition has ended. View the winners above!
                </p>
              @else
                <h3 class="text-white mb-3">Registration Closed</h3>
                <p class="text-white text-opacity-90 fs-5 mb-4">
                  Stay tuned for upcoming competitions!
                </p>
                <a href="{{ route('competitions.public') }}" class="btn bg-white text-dark fs-5 px-5 py-3">
                  View Other Competitions <iconify-icon icon="lucide:arrow-right" class="ms-2"></iconify-icon>
                </a>
              @endif
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
</main>
@endsection

