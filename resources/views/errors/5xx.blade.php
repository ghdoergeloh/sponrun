@extends('layouts.app')
@section('content')
<div class="card card-login mx-auto mt-5">
	@yield('error-message')
	<div class="media col-md-10 offset-md-1 mb-3">
		<img class="img-fluid" alt="client error"
			src="{{ url('/custom/' . env('CF_LOGO')) }}">
		<div class="fittext-container">
			<p class="fittext">Server-Fehler</p>
		</div>
	</div>
</div>
@endsection
