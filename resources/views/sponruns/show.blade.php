@extends('layouts.app') @section('title') - Sponsorenlauf anzeigen
@endsection @section('content')
<div class="row">
	<div class="col-md-12">
		<div class="card mb-3">
			<div class="card-header">Statistik</div>
			<div class="card-body">
				<dl>
					<dt>Anzahl der Teilnehmer:</dt>
					<dd>{{ $sponrun->participants_count }}</dd>
				</dl>
				@if ($sponrun->participants_count > 0 && $sponrun->totalLaps() > 0)
				<dl>
					<dt>Runden insgesamt:</dt>
					<dd>{{ $sponrun->totalLaps() }}</dd>
				</dl>
				<dl>
					<dt>Betrag insgesamt:</dt>
					<dd>{{ number_format($sponrun->totalDonationSum(),2,',','.') }} €</dd>
				</dl>
				<dl>
					<dt>Teilnehmer mit meisten Runden ({{
						$sponrun->participantionsMostLaps()[0]->laps }} Runden):</dt>
					@foreach ($sponrun->participantionsMostLaps() as $runpart)
					<dd>{{ $runpart->user->firstname }} {{ $runpart->user->lastname }}</dd>
					@endforeach
				</dl>
				<dl>
					<dt>Teilnehmer mit meisten Sponsoren ({{
						$sponrun->participantionsMostSponsors()[0]->sponsors->count() }}
						Sponsoren):</dt>
					@foreach ($sponrun->participantionsMostLaps() as $runpart)
					<dd>{{ $runpart->user->firstname }} {{ $runpart->user->lastname }}</dd>
					@endforeach
				</dl>
				<dl>
					<dt>Teilnehmer mit größtem Betrag ({{
						number_format($sponrun->participantionsHighestDonation()[0]->calculateDonationSum(),2,',','.')
						}} €):</dt>
					@foreach ($sponrun->participantionsHighestDonation() as $runpart)
					<dd>{{ $runpart->user->firstname }} {{ $runpart->user->lastname }}</dd>
					@endforeach
				</dl>
				<dl>
					<dt>Jüngster Teilnehmer ({{
						$sponrun->youngestParticipants()[0]->birthday->format('d.m.Y')
						}}):</dt>
					@foreach ($sponrun->youngestParticipants() as $participant)
					<dd>{{ $participant->firstname }} {{ $participant->lastname }}</dd>
					@endforeach
				</dl>
				<dl>
					<dt>Ältester Teilnehmer ({{
						$sponrun->oldestParticipants()[0]->birthday->format('d.m.Y') }}):</dt>
					@foreach ($sponrun->oldestParticipants() as $participant)
					<dd>{{ $participant->firstname }} {{ $participant->lastname }}</dd>
					@endforeach
				</dl>
				@endif
			</div>
		</div>
		<div class="card mb-3">
			<div class="card-header">Teilnehmer</div>
			<div class="card-body">
				<div class="table table-responsive">
					<table
						class="table table-striped table-hover table-condensed dataTable"
						cellspacing="0">
						<thead>
							<tr>
								<th>Nr.</th>
								<th>Nachname</th>
								<th>Vorname</th>
								<th class="d-none d-lg-table-cell">Straße, Nr.</th>
								<th class="d-none d-lg-table-cell">PLZ, Ort</th>
								<th class="d-none d-xl-table-cell">Telefon</th>
								<th class="d-none d-xl-table-cell">E-Mail</th>
								<th class="d-none d-md-table-cell">Projekt</th>
								<th>Runden</th>
								<th class="d-none d-sm-table-cell">Betrag</th>
								<th class="d-none d-md-table-cell">Sponsoren</th>
								<th></th>
							</tr>
						</thead>
						<tbody>
							@foreach ($sponrun->runParticipations as $runpart)
							<tr row_id="{{ $runpart->id }}">
								<td name="id">{{ $runpart->user->id }}</td>
								<td name="lastname">{{ $runpart->user->lastname }}</td>
								<td name="firstname">{{ $runpart->user->firstname }}</td>
								<td name="street_housenumber" class="d-none d-lg-table-cell">{{ $runpart->user->street }} {{
									$runpart->user->housenumber }}</td>
								<td name="postcode_city" class="d-none d-lg-table-cell">{{ $runpart->user->postcode }} {{
									$runpart->user->city }}</td>
								<td name="phone" class="d-none d-xl-table-cell">{{ $runpart->user->phone }}</td>
								<td name="email" class="d-none d-xl-table-cell">{{ $runpart->user->email }}</td>
								<td name="project" class="text-right d-none d-md-table-cell">{{
									is_null($runpart->project) ? "Kein Projekt" :
									$runpart->project->name }}</td>
								<td name="laps" class="text-right editableLaps">{{ $runpart->laps }}</td>
								<td name="sum" class="text-right d-none d-sm-table-cell">{{
									number_format($runpart->calculateDonationSum(),2,',','.') }} €</td>
								<td name="sponsors" class="text-right d-none d-md-table-cell">{{
									$runpart->sponsors->count() }}</td>
								<td name="actions"><a class="btn btn-success"
									href="{{route($root_route.'sponrun.runpart.edit', array_merge($root_route_params,[$runpart->id])) }}"
									data-toggle="tooltip" title="Bearbeiten"> <span
										class="fa fa-pencil" /></a></td>
							</tr>
							@endforeach
						</tbody>
					</table>
				</div>
			</div>
		</div>
	</div>
</div>
<script>
	var indexRunPartURL = "{{ route($root_route.'sponrun.runpart.index',$sponrun) }}";
</script>
@endsection
