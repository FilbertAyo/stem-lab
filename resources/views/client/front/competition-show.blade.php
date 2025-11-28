@extends('layouts.landing')

@section('title', $competition->name . ' | ' . config('app.name'))
@section('page', 'competitions')

@section('content')
<main>
  <!-- Hero Section -->
  <section class="i pg fh rm ji">
    <div class="bb ze ki xn">
      <div class="animate_top">
        <a href="{{ route('competitions.public') }}" class="ur mb-3 d-inline-flex align-items-center gap-2" style="color: #0077b6;">
          <i class="ti ti-arrow-left"></i> Back to Competitions
        </a>
        
        <div class="d-flex flex-wrap gap-2 mb-3">
          <span class="badge" style="background: #e0f2fe; color: #0077b6; padding: 6px 14px; border-radius: 20px;">
            {{ $competition->club->name }}
          </span>
          @if($competition->isRegistrationOpen())
            <span class="badge" style="background: #dcfce7; color: #16a34a; padding: 6px 14px; border-radius: 20px;">
              <i class="ti ti-circle-check"></i> Open for Registration
            </span>
          @elseif($competition->status === 'ongoing')
            <span class="badge" style="background: #dbeafe; color: #1d4ed8; padding: 6px 14px; border-radius: 20px;">
              <i class="ti ti-flame"></i> In Progress
            </span>
          @elseif($competition->status === 'completed')
            <span class="badge" style="background: #fef3c7; color: #92400e; padding: 6px 14px; border-radius: 20px;">
              <i class="ti ti-trophy"></i> Completed
            </span>
          @else
            <span class="badge" style="background: #fee2e2; color: #dc2626; padding: 6px 14px; border-radius: 20px;">
              Registration Closed
            </span>
          @endif
        </div>

        <h1 class="fk vj pr kk wm" style="font-size: 2.2rem;">{{ $competition->name }}</h1>
      </div>
    </div>
  </section>

  <section class="bb ze ki xn py-4">
    <div class="row" style="display: flex; flex-wrap: wrap; gap: 2rem;">
      <!-- Main Content -->
      <div style="flex: 2; min-width: 300px;">
        <!-- Cover Image -->
        @if($competition->cover_image)
          <div class="animate_top mb-4 rounded-lg overflow-hidden" style="height: 300px;">
            <img src="{{ Storage::url($competition->cover_image) }}" alt="{{ $competition->name }}" class="w-100 h-100" style="object-fit: cover;">
          </div>
        @endif

        <!-- Description -->
        <div class="animate_top i rj sg hh sm vk xm hi nj oj mb-4">
          <h3 class="wj kk wm fb">About This Competition</h3>
          <p class="ur dc">{{ $competition->description ?: 'No description provided.' }}</p>
        </div>

        <!-- Terms & Conditions -->
        @if($competition->terms_and_conditions)
          <div class="animate_top i rj sg hh sm vk xm hi nj oj mb-4">
            <h3 class="wj kk wm fb"><i class="ti ti-file-certificate mr-2"></i>Terms and Conditions</h3>
            <div class="ur dc" style="white-space: pre-line;">{{ $competition->terms_and_conditions }}</div>
          </div>
        @endif

        <!-- Instructions -->
        @if($competition->instructions)
          <div class="animate_top i rj sg hh sm vk xm hi nj oj mb-4">
            <h3 class="wj kk wm fb"><i class="ti ti-list-check mr-2"></i>How to Participate</h3>
            <div class="ur dc" style="white-space: pre-line;">{{ $competition->instructions }}</div>
          </div>
        @endif

        <!-- Awards -->
        @if($competition->awards->count() > 0)
          <div class="animate_top i rj sg hh sm vk xm hi nj oj mb-4">
            <h3 class="wj kk wm fb"><i class="ti ti-trophy mr-2"></i>Awards</h3>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 1rem; margin-top: 1rem;">
              @foreach($competition->awards as $award)
                <div class="text-center p-3 rounded" style="background: {{ $award->position <= 3 ? '#fef3c7' : '#f3f4f6' }};">
                  @if($award->prize_image)
                    <img src="{{ Storage::url($award->prize_image) }}" alt="{{ $award->title }}" style="max-height: 50px; margin-bottom: 8px;">
                  @else
                    <i class="ti ti-award" style="font-size: 2rem; color: {{ $award->position == 1 ? '#f59e0b' : ($award->position == 2 ? '#9ca3af' : '#b45309') }};"></i>
                  @endif
                  <h5 class="kk wm" style="font-size: 1rem;">{{ $award->title }}</h5>
                  <p class="ur" style="font-size: 0.85rem;">{{ $award->position_label }}</p>
                  @if($award->prize_value)
                    <p class="kk" style="color: #16a34a; font-size: 0.9rem;">{{ number_format($award->prize_value) }} TZS</p>
                  @endif
                </div>
              @endforeach
            </div>
          </div>
        @endif

        <!-- Winners (if completed) -->
        @if($competition->status === 'completed' && $winners->count() > 0)
          <div class="animate_top i rj sg hh sm vk xm hi nj oj mb-4" style="background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);">
            <h3 class="wj kk wm fb"><i class="ti ti-trophy mr-2" style="color: #f59e0b;"></i>Winners</h3>
            <div style="margin-top: 1rem;">
              @foreach($winners as $winner)
                <div class="d-flex align-items-center gap-3 p-3 mb-2 rounded" style="background: white;">
                  <div style="width: 50px; height: 50px; border-radius: 50%; background: {{ $winner->award->position == 1 ? '#f59e0b' : ($winner->award->position == 2 ? '#9ca3af' : '#b45309') }}; display: flex; align-items: center; justify-content: center; color: white; font-weight: bold;">
                    #{{ $winner->award->position }}
                  </div>
                  <div>
                    <h5 class="kk wm mb-0">{{ $winner->team_name ?: 'Team #'.$winner->id }}</h5>
                    <p class="ur mb-0" style="font-size: 0.85rem;">{{ $winner->teacherClub->school->name ?? '' }}</p>
                    <span class="badge" style="background: #fef3c7; color: #92400e; font-size: 0.75rem;">{{ $winner->award->title }}</span>
                  </div>
                </div>
              @endforeach
            </div>
          </div>
        @endif
      </div>

      <!-- Sidebar -->
      <div style="flex: 1; min-width: 280px;">
        <!-- Quick Info Card -->
        <div class="animate_top i rj sg hh sm vk xm hi nj oj mb-4">
          <h4 class="wj kk wm fb mb-3">Competition Details</h4>
          <ul style="list-style: none; padding: 0; margin: 0;">
            <li class="d-flex justify-content-between py-2" style="border-bottom: 1px solid #e5e7eb;">
              <span class="ur">Status</span>
              <span class="badge bg-{{ $competition->status_badge }}">{{ ucfirst($competition->status) }}</span>
            </li>
            <li class="d-flex justify-content-between py-2" style="border-bottom: 1px solid #e5e7eb;">
              <span class="ur">Club</span>
              <span class="kk">{{ $competition->club->name }}</span>
            </li>
            <li class="d-flex justify-content-between py-2" style="border-bottom: 1px solid #e5e7eb;">
              <span class="ur">Team Size</span>
              <span class="kk">{{ $competition->min_members }}-{{ $competition->max_members ?? '∞' }} members</span>
            </li>
            @if($competition->min_age || $competition->max_age)
              <li class="d-flex justify-content-between py-2" style="border-bottom: 1px solid #e5e7eb;">
                <span class="ur">Age Range</span>
                <span class="kk">{{ $competition->min_age ?? 'Any' }}-{{ $competition->max_age ?? 'Any' }} years</span>
              </li>
            @endif
            <li class="d-flex justify-content-between py-2" style="border-bottom: 1px solid #e5e7eb;">
              <span class="ur">Participants</span>
              <span class="kk">{{ $competition->participants_count }} teams</span>
            </li>
            <li class="d-flex justify-content-between py-2">
              <span class="ur">Awards</span>
              <span class="kk">{{ $competition->awards->count() }}</span>
            </li>
          </ul>
        </div>

        <!-- Timeline Card -->
        <div class="animate_top i rj sg hh sm vk xm hi nj oj mb-4">
          <h4 class="wj kk wm fb mb-3"><i class="ti ti-calendar mr-2"></i>Timeline</h4>
          <ul style="list-style: none; padding: 0; margin: 0;">
            @if($competition->registration_start)
              <li class="d-flex justify-content-between py-2" style="border-bottom: 1px solid #e5e7eb;">
                <span class="ur">Registration Opens</span>
                <span>{{ $competition->registration_start->format('d M Y') }}</span>
              </li>
            @endif
            @if($competition->registration_deadline)
              <li class="d-flex justify-content-between py-2" style="border-bottom: 1px solid #e5e7eb;">
                <span class="ur">Registration Closes</span>
                <span>{{ $competition->registration_deadline->format('d M Y') }}</span>
              </li>
            @endif
            <li class="d-flex justify-content-between py-2" style="border-bottom: 1px solid #e5e7eb;">
              <span class="ur">Competition Start</span>
              <span style="color: #16a34a; font-weight: 600;">{{ $competition->competition_start->format('d M Y') }}</span>
            </li>
            <li class="d-flex justify-content-between py-2" style="border-bottom: 1px solid #e5e7eb;">
              <span class="ur">Competition End</span>
              <span style="color: #dc2626; font-weight: 600;">{{ $competition->competition_end->format('d M Y') }}</span>
            </li>
            @if($competition->results_announcement_date)
              <li class="d-flex justify-content-between py-2">
                <span class="ur">Results</span>
                <span>{{ $competition->results_announcement_date->format('d M Y') }}</span>
              </li>
            @endif
          </ul>
        </div>

        <!-- Action Card -->
        <div class="animate_top i rj sg hh sm vk xm hi nj oj" style="background: linear-gradient(135deg, #0077b6 0%, #00b4d8 100%); color: white;">
          @if($competition->isRegistrationOpen())
            <h4 class="wj wm fb mb-3" style="color: white;">Ready to Join?</h4>
            <p style="color: rgba(255,255,255,0.9); font-size: 0.9rem;">
              Sign in or register to join this competition with your school club.
            </p>
            <a href="{{ route('login') }}" class="lk dk rg tc wf xf _l gi hi w-100 mt-3" style="padding: 12px; background: white; color: #0077b6; display: block; text-align: center;">
              Get Started <i class="ti ti-arrow-right ml-2"></i>
            </a>
          @elseif($competition->status === 'ongoing')
            <h4 class="wj wm fb mb-3" style="color: white;">Competition in Progress</h4>
            <p style="color: rgba(255,255,255,0.9); font-size: 0.9rem;">
              This competition is currently ongoing. Check back for results!
            </p>
          @elseif($competition->status === 'completed')
            <h4 class="wj wm fb mb-3" style="color: white;">Competition Ended</h4>
            <p style="color: rgba(255,255,255,0.9); font-size: 0.9rem;">
              This competition has ended. View the winners above!
            </p>
          @else
            <h4 class="wj wm fb mb-3" style="color: white;">Registration Closed</h4>
            <p style="color: rgba(255,255,255,0.9); font-size: 0.9rem;">
              Stay tuned for upcoming competitions!
            </p>
            <a href="{{ route('competitions.public') }}" class="lk dk rg tc wf xf _l gi hi w-100 mt-3" style="padding: 12px; background: white; color: #0077b6; display: block; text-align: center;">
              View Other Competitions
            </a>
          @endif
        </div>
      </div>
    </div>
  </section>
</main>
@endsection

