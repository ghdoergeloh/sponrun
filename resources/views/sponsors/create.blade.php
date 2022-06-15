@extends('layouts.app', [ 'bgWhite' => 'true'])
@section('title')
- Sponsor anlegen
@endsection
@section('content')
<div class="row">
	<div class="col-md-6 offset-md-2">
		@if (isset($runpart))
		<p class="lead">Ich möchte Sponsor für den Teilnehmer {{ $runpart->user->firstname }} {{ $runpart->user->lastname }} 
		sein{{ is_null( $runpart->project ) ? '.' : ', der für das Projekt "'. $runpart->project->name .'" läuft.' }}
		</p>
		@endif
		{{ Form::open([
			'url' => route($root_route.'sponsor.store', $root_route_params)]) }}
		@include('sponsors.sponsorForm')
		{{ Form::close() }}
	</div>
</div>
@endsection
