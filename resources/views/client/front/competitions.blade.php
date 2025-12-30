@extends('layouts.landing')

@section('title', 'Competitions | ' . config('app.name'))
@section('page', 'competitions')

@section('content')
<main>
  <!-- Hero Section -->
  <section class="i pg fh rm ji">
    <div class="bb ze ki xn">
      <div class="animate_top text-center">
      
        <h1 class="fk vj pr kk wm bb _b" style="font-size: 2.5rem;">
          
        </h1>
        <p class="bb on/5 wo/5 hq mt-4" style="max-width: 600px; margin: 0 auto;">
          Join exciting STEM competitions designed for school clubs across Tanzania. Showcase your innovations, collaborate with peers, and win amazing prizes.
        </p>
      </div>
    </div>
  </section>



  <!-- Active Competitions -->
  @if($activeCompetitions->count() > 0)
  <section class="i pg fh rm ji gp" style="background: linear-gradient(180deg, #f0f9ff 0%, #ffffff 100%);">
    <div class="bb ze ki xn">
      <div class="animate_top mb-5">
        <span class="rc ie ld lk ic yb ek xj" style="background: #16a34a;">
          <i class="ti ti-flame"></i> Open for Registration
        </span>
        <h2 class="fk vj pr kk wm mt-3">Active Competitions</h2>
      </div>

      <div class="wc qf pn xo jg">
        @foreach($activeCompetitions as $competition)
          <div class="animate_top i rj sg hh sm vk xm hi nj oj">
            <span class="rc je md/2 gh xg h q r"></span>
            <span class="rc je md/2 mh yg h q p"></span>

            @if($competition->cover_image)
              <div class="mb-4 rounded-lg overflow-hidden" style="height: 180px;">
                <img src="{{ Storage::url($competition->cover_image) }}" alt="{{ $competition->name }}" class="w-100 h-100" style="object-fit: cover;">
              </div>
            @else
              <div class="mb-4 rounded-lg d-flex align-items-center justify-content-center" style="height: 180px; background: linear-gradient(135deg, #0077b6 0%, #00b4d8 100%);">
                <i class="ti ti-trophy" style="font-size: 3rem; color: white;"></i>
              </div>
            @endif

            <div class="mb-3">
              <span class="badge" style="background: #dcfce7; color: #16a34a; padding: 4px 12px; border-radius: 20px; font-size: 0.75rem;">
                {{ $competition->club->name }}
              </span>
              @if($competition->registration_deadline)
                <span class="badge" style="background: #fef3c7; color: #92400e; padding: 4px 12px; border-radius: 20px; font-size: 0.75rem; margin-left: 4px;">
                  Deadline: {{ $competition->registration_deadline->format('d M') }}
                </span>
              @endif
            </div>

            <h4 class="wj kk wm fb">{{ $competition->name }}</h4>
            <p class="ur dc">{{ Str::limit($competition->description, 100) }}</p>

            <ul class="tc sf bg ob fb" style="font-size: 0.85rem;">
              <li><i class="ti ti-calendar mr-2"></i>{{ $competition->competition_start->format('d M') }} - {{ $competition->competition_end->format('d M Y') }}</li>
              <li><i class="ti ti-users mr-2"></i>{{ $competition->min_members }}-{{ $competition->max_members ?? '∞' }} members per team</li>
              @if($competition->min_age || $competition->max_age)
                <li><i class="ti ti-user mr-2"></i>Age: {{ $competition->min_age ?? 'Any' }}-{{ $competition->max_age ?? 'Any' }} years</li>
              @endif
              <li><i class="ti ti-award mr-2"></i>{{ $competition->awards->count() }} award(s)</li>
            </ul>

            <div class="mt-4 d-flex gap-2">
              <a href="{{ route('competitions.public.show', $competition) }}" class="lk dk rg tc wf xf _l gi hi" style="padding: 8px 16px; background: #e5e7eb; color: #374151;">
                Details
              </a>
              <a href="{{ route('login') }}" class="lk gh dk rg tc wf xf _l gi hi" style="padding: 8px 16px;">
                Join Now <i class="ti ti-arrow-right ml-1"></i>
              </a>
            </div>
          </div>
        @endforeach
      </div>
    </div>
  </section>
  @endif

  <!-- Upcoming Competitions -->
  @if($upcomingCompetitions->count() > 0)
  <section class="i pg fh rm ji gp">
    <div class="bb ze ki xn">
      <div class="animate_top mb-5">
        <span class="rc ie ld lk ic yb ek xj" style="background: #f59e0b;">
          <i class="ti ti-clock"></i> Coming Soon
        </span>
        <h2 class="fk vj pr kk wm mt-3">Upcoming Competitions</h2>
      </div>

      <div class="wc qf pn xo jg">
        @foreach($upcomingCompetitions as $competition)
          <div class="animate_top i rj sg hh sm vk xm hi nj oj" style="opacity: 0.9;">
            <span class="rc je md/2 xg h q r" style="background: #f59e0b;"></span>
            <span class="rc je md/2 mh yg h q p"></span>

            @if($competition->cover_image)
              <div class="mb-4 rounded-lg overflow-hidden" style="height: 160px;">
                <img src="{{ Storage::url($competition->cover_image) }}" alt="{{ $competition->name }}" class="w-100 h-100" style="object-fit: cover; filter: grayscale(30%);">
              </div>
            @endif

            <div class="mb-3">
              <span class="badge" style="background: #fef3c7; color: #92400e; padding: 4px 12px; border-radius: 20px; font-size: 0.75rem;">
                Starts {{ $competition->competition_start->format('d M Y') }}
              </span>
            </div>

            <h4 class="wj kk wm fb">{{ $competition->name }}</h4>
            <p class="ur dc">{{ Str::limit($competition->description, 80) }}</p>

            <div class="mt-4">
              <a href="{{ route('competitions.public.show', $competition) }}" class="lk dk rg tc wf xf _l gi hi" style="padding: 8px 16px; background: #fef3c7; color: #92400e;">
                Learn More <i class="ti ti-arrow-right ml-1"></i>
              </a>
            </div>
          </div>
        @endforeach
      </div>
    </div>
  </section>
  @endif

  <!-- Completed Competitions -->
  @if($completedCompetitions->count() > 0)
  <section class="i pg fh rm ji gp" style="background: #f9fafb;">
    <div class="bb ze ki xn">
      <div class="animate_top mb-5">
        <span class="rc ie ld lk ic yb ek xj" style="background: #6b7280;">
          <i class="ti ti-check"></i> Completed
        </span>
        <h2 class="fk vj pr kk wm mt-3">Past Competitions</h2>
        <p class="ur mt-2">View results and winners from previous competitions</p>
      </div>

      <div class="wc qf pn xo jg">
        @foreach($completedCompetitions as $competition)
          <div class="animate_top i rj sg hh sm vk xm hi nj oj" style="opacity: 0.85;">
            <span class="rc je md/2 xg h q r" style="background: #6b7280;"></span>
            <span class="rc je md/2 mh yg h q p"></span>

            <div class="mb-3">
              <span class="badge" style="background: #f3f4f6; color: #6b7280; padding: 4px 12px; border-radius: 20px; font-size: 0.75rem;">
                {{ $competition->club->name }}
              </span>
              <span class="badge" style="background: #fef3c7; color: #92400e; padding: 4px 12px; border-radius: 20px; font-size: 0.75rem; margin-left: 4px;">
                <i class="ti ti-trophy"></i> Results Available
              </span>
            </div>

            <h4 class="wj kk wm fb">{{ $competition->name }}</h4>
            <p class="ur dc">Ended {{ $competition->competition_end->format('d M Y') }}</p>
            <p class="ur dc">{{ $competition->participants_count }} teams participated</p>

            <div class="mt-4">
              <a href="{{ route('competitions.public.show', $competition) }}" class="lk dk rg tc wf xf _l gi hi" style="padding: 8px 16px; background: #e5e7eb; color: #374151;">
                View Results <i class="ti ti-trophy ml-1"></i>
              </a>
            </div>
          </div>
        @endforeach
      </div>
    </div>
  </section>
  @endif

  <!-- No Competitions -->
  @if($activeCompetitions->isEmpty() && $upcomingCompetitions->isEmpty() && $completedCompetitions->isEmpty())
  <section class="i pg fh rm ji gp">
    <div class="bb ze ki xn text-center">
      <div class="animate_top">
        <i class="ti ti-trophy" style="font-size: 5rem; color: #d1d5db;"></i>
        <h3 class="fk vj kk wm mt-4">No Competitions Yet</h3>
      </div>
    </div>
  </section>
  @endif

  <!-- CTA -->
  @include('client.front.partials.cta')
</main>
@endsection

