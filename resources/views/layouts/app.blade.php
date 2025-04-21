<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta name="description" content="Smarthr - Bootstrap Admin Template">
		<meta name="keywords" content="admin, estimates, bootstrap, business, corporate, creative, management, minimal, modern, accounts, invoice, html5, responsive, CRM, Projects">
        <meta name="author" content="Dreamstechnologies - Bootstrap Admin Template">
        <title>{{ __('project_title') }}</title>
		
		<!-- Favicon -->
        <link rel="shortcut icon" type="image/x-icon" href="{{ url('front-assets/img/favicon.png') }}">
		
		
		<meta name="csrf-token" content="{{ csrf_token() }}">
		@yield('styles')
		<script>
        var csrfToken = "{{ csrf_token() }}"; // Declare the CSRF token
		</script>
    </head>
	<body>
		<div class="main-wrapper">
			@include('_includes/header')
			@include('_includes/sidebar')
			
				@yield('content')

			
		</div>
		<!-- jQuery -->
        <script src="{{ url('front-assets/js/jquery-3.7.1.min.js') }}"></script>
		
		<script type="text/javascript">
		$(function(){
			var url = "{{ route('changeLang') }}";
			$(document).on("click", ".languageChange a", function(e) {
				e.preventDefault();  // Prevent default action for anchor
				var languageCode = $(this).data('id');
				var selectedText = $(this).text().trim();
				$('#selectedLang').data('id', languageCode);  // Update the data-id
				$('#selectedLang img').attr('src', $(this).find('img').attr('src'));  // Update the flag icon
				$('#selectedLangText').text(selectedText); 
				//alert(languageCode);
				window.location.href = url + "?lang=" + languageCode;
			});
		});
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