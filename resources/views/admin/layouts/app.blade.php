<!DOCTYPE html>
<!--<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">-->
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-sidebar="dark" data-sidebar-size="lg" data-layout-mode="blue" data-layout-width="fluid" data-layout-position="fixed" data-layout-style="default" data-topbar="light">
	<head>
        <!--<meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
		<link href="{{ url('admin-assets/css/responsive-media.css') }}" rel="stylesheet">

        @vite(['resources/css/app.css', 'resources/js/app.js'])-->
		<meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta name="description" content="Smarthr - Bootstrap Admin Template">
		<meta name="keywords" content="admin, estimates, bootstrap, business, corporate, creative, management, minimal, modern, accounts, invoice, html5, responsive, CRM, Projects">
        <meta name="author" content="Dreamstechnologies - Bootstrap Admin Template">
        <title>{{ __('project_title') }}</title>
		
		<!-- Favicon -->
        <link rel="shortcut icon" type="image/x-icon" href="{{ url('admin-assets/img/favicon.png') }}">
		
		<!-- Bootstrap CSS -->
        <link rel="stylesheet" href="{{ url('admin-assets/css/bootstrap.min.css') }}">
		
		<!-- Fontawesome CSS -->
        <link rel="stylesheet" href="{{ url('admin-assets/plugins/fontawesome/css/fontawesome.min.css') }}">
    	<link rel="stylesheet" href="{{ url('admin-assets/plugins/fontawesome/css/all.min.css') }}">

		<!-- Lineawesome CSS -->
        <link rel="stylesheet" href="{{ url('admin-assets/css/line-awesome.min.css') }}">
		<link rel="stylesheet" href="{{ url('admin-assets/css/material.css') }}">

		<!-- Daterangepikcer CSS -->
		<link rel="stylesheet" href="{{ url('admin-assets/plugins/daterangepicker/daterangepicker.css') }}">

		<!-- Bootstrap Tagsinput CSS -->
		<link rel="stylesheet" href="{{ url('admin-assets/plugins/bootstrap-tagsinput/bootstrap-tagsinput.css') }}">

		<!-- Datatable CSS -->
		<link rel="stylesheet" href="{{ url('admin-assets/css/dataTables.bootstrap4.min.css') }}">
		
		<!-- Feather CSS -->
		<link rel="stylesheet" href="{{ url('admin-assets/css/feather.css') }}">
		
		<!-- Datetimepicker CSS -->
		<link rel="stylesheet" href="{{ url('admin-assets/css/bootstrap-datetimepicker.min.css') }}">
		
		<!-- Lineawesome CSS -->
		<link rel="stylesheet" href="{{ url('admin-assets/css/line-awesome.min.css') }}">
		
		<!-- Select2 CSS -->
		<link rel="stylesheet" href="{{ url('admin-assets/css/select2.min.css') }}">
		
		<!-- Main CSS -->
        <link rel="stylesheet" href="{{ url('admin-assets/css/style.css') }}">
		<link rel="stylesheet" href="{{ url('admin-assets/css/custom-css.css') }}">
		<link rel="stylesheet" href="{{ url('admin-assets/plugins/morris/morris.css') }}">
		
		<meta name="csrf-token" content="{{ csrf_token() }}">
		@yield('styles')
		<script>
        var csrfToken = "{{ csrf_token() }}"; // Declare the CSRF token
		</script>
    </head>
	<body>
		<div class="main-wrapper">
			@include('admin/_includes/header')
			@include('admin/_includes/sidebar')
			
				@yield('content')

			
		</div>
		<!-- jQuery -->
        <script src="{{ url('admin-assets/js/jquery-3.7.1.min.js') }}"></script>
		
		<!-- Bootstrap Core JS -->
        <script src="{{ url('admin-assets/js/bootstrap.bundle.min.js') }}"></script>
		
		<!-- Slimscroll JS -->
		<script src="{{ url('admin-assets/js/jquery.slimscroll.min.js') }}"></script>

		<!-- Datatable JS -->
		<script src="{{ url('admin-assets/js/jquery.dataTables.min.js') }}"></script>
		<script src="{{ url('admin-assets/js/dataTables.bootstrap4.min.js') }}"></script>

		<!-- Bootstrap Tagsinput JS -->
		<script src="{{ url('admin-assets/plugins/bootstrap-tagsinput/bootstrap-tagsinput.js') }}"></script>

		<!-- Datetimepicker JS -->
		<script src="{{ url('admin-assets/js/moment.min.js') }}"></script>
		<script src="{{ url('admin-assets/js/bootstrap-datetimepicker.min.js') }}"></script>

		<!-- Daterangepikcer JS -->
		<script src="{{ url('admin-assets/js/moment.min.js') }}"></script>
		<script src="{{ url('admin-assets/plugins/daterangepicker/daterangepicker.js') }}"></script>
		
		<!-- Select2 JS -->
		<script src="{{ url('admin-assets/js/select2.min.js') }}"></script>
		
		 <!-- Theme Settings JS -->
		<script src="{{ url('admin-assets/js/layout.js') }}"></script>
		<script src="{{ url('admin-assets/js/theme-settings.js') }}"></script>
		<script src="{{ url('admin-assets/js/greedynav.js') }}"></script>
		<!-- Custom JS -->
		<script src="{{ url('admin-assets/js/app.js') }}"></script>
		<script src="{{ url('admin-assets/js/page/multi-action.js') }}"></script>
		
		
		
		<script type="text/javascript">
			/*$(function(){
				var url = "{{ route('changeLang') }}";
				$(document).on("click", "ul.languageChange li a", function(e) {
					var languageCode = $(this).data('id');
					alert(languageCode);
					window.location.href = url + "?lang="+ $(this).data('id');
				});
			});*/		
		</script>
		<script>
			@if(Session::has('message'))
				var msg = "{{ session('message') }}";
				var type = 'success';
				toastr_msg(msg, type);
			@endif

			@if(Session::has('error'))
				var msg = "{{ session('error') }}";
				var type = 'error';
				toastr_msg(msg, type);
			@endif

			@if(Session::has('info'))
				var msg = "{{ session('info') }}";
				var type = 'info';
				toastr_msg(msg, type);
			@endif

			@if(Session::has('warning'))
				var msg = "{{ session('warning') }}";
				var type = 'warning';
				toastr_msg(msg, type);
			@endif
			function toastr_msg(msg, type){
				toastr.options =
				{
					"closeButton" : true,
					"progressBar" : true
				}
				toastr[type](msg);
			}
		</script>
		@yield('scripts')
		@yield('component-scripts')
	</body>
</html>