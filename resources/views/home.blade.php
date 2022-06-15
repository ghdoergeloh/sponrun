@extends('layouts.app')
@section('title')
- Home
@endsection
@section('content')
<div class="row">
	<div class="media col-md-2 offset-md-1 mt-3 mb-3 d-none d-md-flex">
		<img class="img-fluid" alt="Logo" src="{{ url('/custom/' . env('CF_LOGO')) }}">
	</div>
	<div class="col-md-7">
		<div class="card mb-3">
			<div class="card-header">Hallo {{ Auth::user()->firstname }} </div>

			<div class="card-body">
				<b>Herzlich willkommen!</b> Schön, dass du am Sponsorenlauf teilnimmst.
			</div>
		</div>

		<div class="card mb-3">
			<div class="card-header">Die nächsten Sponsorenläufe</div>

			<div class="card-body">
				<div class="table-responsive">
					<table class="table table-striped">
						<tr>
							<th class="d-none d-sm-table-cell">Datum</th>
							<th>Name</th>
							<th class="d-none d-lg-table-cell">Teilnehmer</th>
							<th></th>
						</tr>
						@foreach ($sponruns as $sponrun)
						<tr>
							<td class="d-none d-sm-table-cell">{{ $sponrun->begin->format('d.m.Y') }}</td>
							<td>{{ $sponrun->name }}</td>
							<td class="d-none d-lg-table-cell">{{ $sponrun->participants_count }}</td>
							<td>
							@if ( !$sponrun->participants->contains(Auth::user()) )
								{{ Form::open(['route' => 'runpart.store']) }}
								{{ Form::hidden('run_id', $sponrun->id) }}
								{{ Form::submit('Teilnehmen', [ 'class' => "btn btn-secondary btn-block"]) }}
								{{ Form::close() }}
							@else
								<a class="btn btn-primary btn-block" href="{{ route('runpart.edit',
												$sponrun->runParticipations
												->filter(function($runpart) {
													return $runpart->user == Auth::user();
												})->first()->id) }}">Meine Sponsoren</a>
							@endif
							</td>
						</tr>
					@endforeach
					</table>
				</div>
				@if ( $sponruns->isEmpty() )
				<p>Es gibt noch keine neuen Sponsorenläufe</p>
				@endif
			</div>
		</div>
	</div>
</div>
@if ( config('app.newsletter_optional') && is_null(Auth::user()->wants_newsletter) )
	@include('general_dialogs.newsletter_dlg')
@endif
@endsection
