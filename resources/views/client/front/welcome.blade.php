@extends('layouts.landing')

@section('title', config('app.name') . ' | Home')
@section('page', 'home')

@section('content')
<main>

    @include('client.front.partials.hero')

    
  
  </main>
@endsection
