<!DOCTYPE html>
<html lang="de">
<head>
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1">

<title>SponRun - @yield('title')</title>

<!-- Fonts -->
<link rel="stylesheet"
	href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.5.0/css/font-awesome.min.css"
	integrity="sha384-XdYbMnZ/QjLh6iI4ogqCTaIjrFk87ip+ekIjefZch0Y+PvJ8CDYtEs1ipDmPorQ+"
	crossorigin="anonymous">
<link rel="stylesheet"
	href="https://fonts.googleapis.com/css?family=Lato:100,300,400,700">

<!-- Styles -->

<link rel="stylesheet" href="{{url('/css/bootstrap.min.css')}}">
<style>
body {
	font-family: 'Lato';
}

.fa-btn {
	margin-right: 6px;
}
</style>
</head>
<body id="app-layout">
	@include('layouts.menu')
	<div class="container">
		<div class="content">@yield('content')</div>
	</div>

	<!-- JavaScripts -->
	<script src="{{url('/js/jquery-1.12.4.min.js')}}"></script>
	<script src="{{url('/js/bootstrap.min.js')}}"
		integrity="sha384-0mSbJDEHialfmuBBQP6A4Qrprq5OVfW37PRR3j5ELqxss1yVqOtnepnHVP9aJ7xS"
		crossorigin="anonymous"></script>
</body>
</html>