@extends('layouts.landing')

@section('title', config('app.name') . ' | Home')
@section('page', 'home')

@section('content')
<main>
    @include('client.front.partials.hero')
    @include('client.front.partials.features')
    @include('client.front.partials.about')
    @include('client.front.partials.subjects')
    @include('client.front.partials.learning-pathways')
    @include('client.front.partials.testimonials')
    @include('client.front.partials.stats')
    @include('client.front.partials.cta')
  </main>
@endsection
@push('scripts')
<script>
    //  Pricing Table
    const setup = () => {
      return {
        isNavOpen: false,

        billPlan: 'monthly',

        plans: [
          {
            name: 'Primary Explorer',
            price: {
              monthly: 0,
              annually: 0,
            },
            features: ['Interactive science storytelling', 'Foundational math fluency quests', 'Teacher facilitation guides'],
          },
          {
            name: 'Secondary Innovator',
            price: {
              monthly: 0,
              annually: 0,
            },
            features: ['Chemistry & physics simulations', 'Coding and robotics missions', 'STEM mentor circles'],
          },
          {
            name: 'Advanced Creator',
            price: {
              monthly: 0,
              annually: 0,
            },
            features: ['Aviation and engineering labs', 'Design thinking sprints', 'Career exploration resources'],
          },
        ],
      };
    };
  </script>
@endpush

