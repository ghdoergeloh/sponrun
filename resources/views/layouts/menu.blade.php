<nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top" id="mainNav">
	<a class="navbar-brand" href="{{ url('/') }}">
		@if (env('SHOW_LOGO_IN_MENU'))
		<img class="img-fluid" alt="Logo" src="{{ url('/custom/' . env('CF_LOGO')) }}" width="30" height="30">
		@endif
		<strong>{{ config('app.name') }}</strong></a>
	<button class="navbar-toggler navbar-toggler-right" type="button"
			data-toggle="collapse" data-target="#bs-navbar"
			aria-controls="bs-navbar" aria-expanded="false" aria-label="Navigation">
		<span class="navbar-toggler-icon"></span>
	</button>

	<div class="collapse navbar-collapse" id="bs-navbar">
	<!-- left navigation bar -->
		@if (!(isset($withoutLeftBar) && $withoutLeftBar))
		<ul class="navbar-nav navbar-sidenav" id="actionsAccordion">
			@if (Auth::check())
			<li class="nav-item"><a class="nav-link" href="{{ url('/home') }}">
				<i class="fa fa-fw fa-home"></i>
				<span class="nav-link-text">Home</span></a>
			</li>
			<li class="nav-item"><a class="nav-link" href="{{ route('runpart.index') }}">
				<i class="fa fa-fw fa-flag-checkered"></i>
				<span class="nav-link-text">Meine Sponsorenläufe</span></a>
			</li>
			@if ( Entrust::hasRole('admin') )
			<li class="nav-item">
				<a href="#collapseVerwaltung" class="nav-link nav-link-collapse collapsed"
				   	data-toggle="collapse">
				   	<i class="fa fa-fw fa-wrench"></i>
					<span class="nav-link-text">Verwaltung</span>
				</a>
				<ul class="sidenav-second-level collapse" id="collapseVerwaltung" >
					<li>
						<a href="{{ route('project.index') }}">Projekte</a>
					</li>
					<li>
						<a href="{{ route('projectlist.index') }}">Projektlisten</a>
					</li>
					<li>
						<a href="{{ route('sponrun.index') }}">Sponsorenläufe</a>
					</li>
				</ul>
			</li>
			@endif
			@endif
		</ul>
		<ul class="navbar-nav sidenav-toggler">
			<li class="nav-item"><a class="nav-link text-center" id="sidenavToggler">
				<i class="fa fa-fw fa-angle-left"></i>
			</a></li>
		</ul>
		@endif
	<!-- top navigation bar -->
		<ul class="navbar-nav ml-auto">
			<!-- Authentication Links -->
			@if (!empty(env('URL_IMPRESSUM')))
			<li class="nav-item">
				<a class="nav-link" href="{{ env('URL_IMPRESSUM') }}" target="_blank">
					<i class="fa fa-fw fa-info"></i>
					<span class="nav-link-text">Impressum</span>
				</a>
			</li>
			@endif
			@if (!empty(env('URL_PRIVACY_STATEMENT')))
			<li class="nav-item">
				<a class="nav-link" href="{{ env('URL_PRIVACY_STATEMENT') }}" target="_blank">
					<i class="fa fa-fw fa-lock"></i>
					<span class="nav-link-text">Datenschutz</span>
				</a>
			</li>
			@endif
			@if (!Auth::guest())
			<li class="nav-item dropdown">
				<a href="#" class="nav-link dropdown-toggle mr-lg-2" id="accountDropdown"
				   data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">
					{{Auth::user()->lastname}}, {{Auth::user()->firstname}}
					<span class="caret"></span>
				</a>
				<ul class="dropdown-menu"  aria-labelledby="accountDropdown">
					<li><a class="dropdown-item" href="{{ route('account.edit') }}">
						<i class="fa fa-fw fa-user"></i>
						<span class="nav-link-text">Mein Account</span></a>
					</li>
					<li>
						<a href="" class="dropdown-item"
						   onclick="event.preventDefault();
								   document.getElementById('logout-form').submit();">
							<i class="fa fa-fw fa-sign-out"></i>Logout</a>
						{{ Form::open([
								'method' => 'POST',
								'route' => 'logout',
								'class' => "hidden",
								'id' => 'logout-form']) }}
						{{ Form::close() }}
					</li>
				</ul>
			</li>
			@endif
		</ul>
	</div>
</nav>