<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0" name="viewport">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Prigo - Intranet</title>

	<!--     Fonts and icons     -->
	<link rel="stylesheet" type="text/css" href="https://fonts.googleapis.com/css?family=Roboto:300,400,500,700|Roboto+Slab:400,700|Material+Icons">
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/latest/css/font-awesome.min.css">

	<link rel="stylesheet" href="{{ asset('MaterialBS/css/material-dashboard.min.css') }}">

	<!-- Documentation extras -->
	<!-- CSS Just for demo purpose, don't include it in your project -->
	<link href="{{ asset('MaterialBS/assets-for-demo/demo.css') }}" rel="stylesheet">
</head>
<body class="off-canvas-sidebar login-page" cz-shortcut-listen="true">

	@yield('content')
	
    <!--   Core JS Files   -->
	<script src="{{ asset('MaterialBS/js/core/jquery.min.js') }}"></script>
	<script src="{{ asset('MaterialBS/js/core/popper.min.js') }}"></script>

	<script src="{{ asset('MaterialBS/js/bootstrap-material-design.min.js') }}"></script>
	<script src="{{ asset('MaterialBS/js/plugins/perfect-scrollbar.jquery.min.js') }}"></script>

	<!--  Plugin for Date Time Picker and Full Calendar Plugin  -->
	<script src="{{ asset('MaterialBS/js/plugins/moment.min.js') }}"></script>

	<!--	Plugin for the Datepicker, full documentation here: https://github.com/Eonasdan/bootstrap-datetimepicker -->
	<script src="{{ asset('MaterialBS/js/plugins/bootstrap-datetimepicker.min.js') }}"></script>

	<!--	Plugin for the Sliders, full documentation here: https://refreshless.com/nouislider/ -->
	<script src="{{ asset('MaterialBS/js/plugins/nouislider.min.js') }}"></script>
	<!--	Plugin for Select, full documentation here: https://silviomoreto.github.io/bootstrap-select -->
	<script src="{{ asset('MaterialBS/js/plugins/bootstrap-selectpicker.js') }}"></script>

	<!--	Plugin for Tags, full documentation here: https://xoxco.com/projects/code/tagsinput/  -->
	<script src="{{ asset('MaterialBS/js/plugins/bootstrap-tagsinput.js') }}"></script>

	<!--	Plugin for Fileupload, full documentation here: https://www.jasny.net/bootstrap/javascript/#fileinput -->
	<script src="{{ asset('MaterialBS/js/plugins/jasny-bootstrap.min.js') }}"></script>

	<!-- Plugins for presentation and navigation  -->
	<script src="{{ asset('MaterialBS/assets-for-demo/js/modernizr.js') }}"></script>

	<!-- Material Kit Core initialisations of plugins and Bootstrap Material Design Library -->

	<script src="{{ asset('MaterialBS/js/material-dashboard.js') }}"></script>
	<!-- Include a polyfill for ES6 Promises (optional) for IE11, UC Browser and Android browser support SweetAlert -->
	<script src="{{ asset('https://cdnjs.cloudflare.com/ajax/libs/core-js/2.4.1/core.js') }}"></script>

	<!-- Library for adding dinamically elements -->
	<script src="{{ asset('MaterialBS/js/plugins/arrive.min.js') }}" type="text/javascript') }}"></script>

	<!-- Forms Validations Plugin -->
	<script src="{{ asset('MaterialBS/js/plugins/jquery.validate.min.js') }}"></script>

	<!--  Charts Plugin, full documentation here: https://gionkunz.github.io/chartist-js/ -->
	<script src="{{ asset('MaterialBS/js/plugins/chartist.min.js') }}"></script>

	<!--  Plugin for the Wizard, full documentation here: https://github.com/VinceG/twitter-bootstrap-wizard -->
	<script src="{{ asset('MaterialBS/js/plugins/jquery.bootstrap-wizard.js') }}"></script>

	<!--  Notifications Plugin, full documentation here: https://bootstrap-notify.remabledesigns.com/    -->
	<script src="{{ asset('MaterialBS/js/plugins/bootstrap-notify.js') }}"></script>

	<!-- Vector Map plugin, full documentation here: https://jvectormap.com/documentation/ -->
	<script src="{{ asset('MaterialBS/js/plugins/jquery-jvectormap.js') }}"></script>

	<!-- Sliders Plugin, full documentation here: https://refreshless.com/nouislider/ -->
	<script src="{{ asset('MaterialBS/js/plugins/nouislider.min.js') }}"></script>

	<!--  Plugin for Select, full documentation here: https://silviomoreto.github.io/bootstrap-select -->
	<script src="{{ asset('MaterialBS/js/plugins/jquery.select-bootstrap.js') }}"></script>

	<!--  DataTables.net Plugin, full documentation here: https://datatables.net/    -->
	<script src="{{ asset('MaterialBS/js/plugins/jquery.datatables.js') }}"></script>

	<!-- Sweet Alert 2 plugin, full documentation here: https://limonte.github.io/sweetalert2/ -->
	<script src="{{ asset('MaterialBS/js/plugins/sweetalert2.js') }}"></script>

	<!-- Plugin for Fileupload, full documentation here: https://www.jasny.net/bootstrap/javascript/#fileinput -->
	<script src="{{ asset('MaterialBS/js/plugins/jasny-bootstrap.min.js') }}"></script>

	<!--  Full Calendar Plugin, full documentation here: https://github.com/fullcalendar/fullcalendar    -->
	<script src="{{ asset('MaterialBS/js/plugins/fullcalendar.min.js') }}"></script>

	<!-- demo init -->
	<script src="{{ asset('MaterialBS/js/plugins/demo.js') }}"></script>
	  <script type="text/javascript">
		$().ready(function(){
			demo.checkFullPageBackgroundImage();

			setTimeout(function(){
				// after 1000 ms we add the class animated to the login/register card
				$('.card').removeClass('card-hidden');
			}, 700)
		});
	</script>
</body>
</html>