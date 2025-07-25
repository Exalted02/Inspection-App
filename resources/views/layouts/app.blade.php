<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
	<head>
		<meta http-equiv="content-type" content="text/html;charset=UTF-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta name="description" content="{{ __('project_title') }}">
		<meta name="keywords" content="{{ __('project_title') }}">
        <meta name="author" content="Dreamstechnologies - Bootstrap Admin Template">
        <title>{{ __('project_title') }}</title>
		
		<!-- Favicon -->
        <link rel="shortcut icon" type="image/x-icon" href="{{ url('front-assets/img/favicon.png') }}">		
		<meta name="csrf-token" content="{{ csrf_token() }}">
		<!-- ==============> EXALTED SOLUTION CSS <====================== -->
		<link href="https://fonts.googleapis.com/css2?family=Open+Sans&display=swap" rel="stylesheet">
		<link rel="stylesheet" href="{{ url('front-assets/css/es.css') }}">
		<!-- =-=-=-=-=-=-= Bootstrap CSS Style =-=-=-=-=-=-= -->
		<link rel="stylesheet" href="{{ url('front-assets/css/bootstrap.css') }}">
		
		<!-- =-=-=-=-=-=-= Template CSS Style =-=-=-=-=-=-= -->
		<link rel="stylesheet" href="{{ url('front-assets/css/style.css') }}">
		
		<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" integrity="sha512-SnH5WK+bZxgPHs44uWIX+LLJAJ9/2PkPKZ5QiAj6Ta86w+fsb2TkcmfRyVX3pBnMFcV7oQPJkl9QevSCWr3W6A==" crossorigin="anonymous" referrerpolicy="no-referrer" />
		<!-- =-=-=-=-=-=-= Font Awesome =-=-=-=-=-=-= -->
		<link rel="stylesheet" href="{{ url('front-assets/css/font-awesome.css') }}" type="text/css">
		<!-- =-=-=-=-=-=-= Flat Icon =-=-=-=-=-=-= -->
		<link href="{{ url('front-assets/css/flaticon.css') }}" rel="stylesheet">
		<!-- =-=-=-=-=-=-= Et Line Fonts =-=-=-=-=-=-= -->
		<link rel="stylesheet" href="{{ url('front-assets/css/et-line-fonts.css') }}" type="text/css">
		<!-- =-=-=-=-=-=-= Menu Drop Down =-=-=-=-=-=-= -->
		<link rel="stylesheet" href="{{ url('front-assets/css/carspot-menu.css') }}" type="text/css">
		<!-- =-=-=-=-=-=-= Animation =-=-=-=-=-=-= -->
		<link rel="stylesheet" href="{{ url('front-assets/css/animate.min.css') }}" type="text/css">
		<link rel="stylesheet" href="{{ url('front-assets/css/dropzone1.css') }}" type="text/css">
		<!-- =-=-=-=-=-=-= Select Options =-=-=-=-=-=-= -->
		<link href="{{ url('front-assets/css/select2.min.css') }}" rel="stylesheet" />
		<!-- =-=-=-=-=-=-= noUiSlider =-=-=-=-=-=-= -->
		<link href="{{ url('front-assets/css/nouislider.min.css') }}" rel="stylesheet">
		<!-- =-=-=-=-=-=-= Listing Slider =-=-=-=-=-=-= -->
		<link href="{{ url('front-assets/css/slider.css') }}" rel="stylesheet">
		<!-- =-=-=-=-=-=-= Owl carousel =-=-=-=-=-=-= -->
		<link rel="stylesheet" type="text/css" href="{{ url('front-assets/css/owl.carousel.css') }}">
		<link rel="stylesheet" type="text/css" href="{{ url('front-assets/css/owl.theme.css') }}">
		<!-- =-=-=-=-=-=-= Check boxes =-=-=-=-=-=-= -->
		<link href="{{ url('front-assets/skins/minimal/minimal.css') }}" rel="stylesheet">
		<!-- =-=-=-=-=-=-= PrettyPhoto =-=-=-=-=-=-= -->
		<link rel="stylesheet" href="{{ url('front-assets/css/jquery.fancybox.min.css') }}" type="text/css" media="screen"/>
		<!-- =-=-=-=-=-=-= Responsive Media =-=-=-=-=-=-= -->
		<link href="{{ url('front-assets/css/responsive-media.css') }}" rel="stylesheet">
		<!-- =-=-=-=-=-=-= Template Color =-=-=-=-=-=-= -->
		<link rel="stylesheet" id="color" href="{{ url('front-assets/css/colors/defualt.css') }}">
		<link href="https://fonts.googleapis.com/css?family=Poppins:400,500,600%7CSource+Sans+Pro:400,400i,600" rel="stylesheet">
		
		<!-- Datetimepicker CSS -->
		<link rel="stylesheet" href="{{ url('front-assets/css/bootstrap-datetimepicker.min.css') }}">
		
		<link rel="stylesheet" href="https://unpkg.com/simplebar/dist/simplebar.min.css">
		<!--<link href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" rel="stylesheet">
		<link href="https://cdn.datatables.net/2.0.0/css/dataTables.bootstrap.css" rel="stylesheet">--> 
		
		
		<!-- JavaScripts -->
		<script src="{{ url('front-assets/js/modernizr.js') }}"></script>
		<!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
		<!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
		<!--[if lt IE 9]>
		<script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
		<script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
		<![endif]-->
		@yield('component-style')
		
		<script>
        var csrfToken = "{{ csrf_token() }}"; // Declare the CSRF token
		</script>
    </head>
	<body>
		@if(!request()->routeIs('login'))
			@include('_includes/header')
		@endif
		
			@yield('content')
			{{--	
		@if(!request()->routeIs('login'))
			@include('_includes/footer')
			@endif  --}}
		<!-- =-=-=-=-=-=-= JQUERY =-=-=-=-=-=-= -->
		<script src="{{ url('front-assets/js/jquery.min.js') }}"></script>
		<!-- Bootstrap Core Css  -->
		<script src="{{ url('front-assets/js/bootstrap.min.js') }}"></script>
		<!-- Jquery Easing -->
		<script src="{{ url('front-assets/js/easing.js') }}"></script>
		<!-- Menu Hover  -->
		<script src="{{ url('front-assets/js/carspot-menu.js') }}"></script>
		<!-- Jquery Appear Plugin -->
		<script src="{{ url('front-assets/js/jquery.appear.min.js') }}"></script>
		<!-- Numbers Animation   -->
		<script src="{{ url('front-assets/js/jquery.countTo.js') }}"></script>
		<!-- Jquery Select Options  -->
		<script src="{{ url('front-assets/js/select2.min.js') }}"></script>
		<!-- noUiSlider -->
		<script src="{{ url('front-assets/js/nouislider.all.min.js') }}"></script>
		<!-- Carousel Slider  -->
		<script src="{{ url('front-assets/js/carousel.min.js') }}"></script>
		<script src="{{ url('front-assets/js/slide.js') }}"></script>
		<!-- Image Loaded  -->
		<script src="{{ url('front-assets/js/imagesloaded.js') }}"></script>
		<script src="{{ url('front-assets/js/isotope.min.js') }}"></script>
		<!-- CheckBoxes  -->
		<script src="{{ url('front-assets/js/icheck.min.js') }}"></script>
		<!-- Jquery Migration  -->
		<script src="{{ url('front-assets/js/jquery-migrate.min.js') }}"></script>
		<!-- Style Switcher -->
		<script src="{{ url('front-assets/js/color-switcher.js') }}"></script>
		<!-- PrettyPhoto -->
		<script src="{{ url('front-assets/js/jquery.fancybox.min.js') }}"></script>
		<!-- Wow Animation -->
		<script src="{{ url('front-assets/js/wow.js') }}"></script>
		<!-- Template Core JS -->
		<script src="{{ url('front-assets/js/custom.js') }}"></script>
		
		<!-- Datetimepicker  JS -->
		<script src="{{url('front-assets/js/moment.min.js') }}"></script>
		<script src="{{url('front-assets/js/bootstrap-datetimepicker.min.js') }}"></script>
		<!-- /Datetimepicker  JS -->
		
		<script src="https://unpkg.com/simplebar/dist/simplebar.min.js"></script>
		
		<link rel="stylesheet" href="{{ url('common-assets/css/toastr.min.css') }}"/>
		<script src="{{ url('common-assets/js/toastr.min.js') }}"></script>
		
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
			
			function goBackAndReload() {
				@if(auth()->check() && (auth()->user()->user_type == 1))
				{
					var app_url =  "{{ env('APP_URL') }}";
					var page_url1 = app_url+'/category';
					var page_url2 = app_url+'/location-details';
					var page_url3 = app_url+'/checklist-question';
					var page_url4 = app_url+'/inspector-filter';
					var current_url = window.location.href;
					//alert(page_url1);alert(current_url);
					if(current_url.includes(page_url1))
					{
						setTimeout(function() {
							var location_id = $('#location_id').val();
							var baseUrl = "{{ url('/location-details') }}";
							var redirectUrl = baseUrl + '/' + location_id;
							window.location.href = redirectUrl;
						}, 100);
					}
					else if(current_url.includes(page_url2))
					{
						setTimeout(function() {
							var baseUrl = "{{ url('/inspector-dashboard') }}";
							var redirectUrl = baseUrl;
							window.location.href = redirectUrl;
						}, 100);
					}
					else if(current_url.includes(page_url3))
					{
						var location_id = $('#location_id').val();
						var task_id = $('#task_id').val();
						var category_id = $('#category_id').val();
						var active = 1;
						setTimeout(function() {
							var baseUrl = "{{ url('/category') }}";
							var redirectUrl = baseUrl + '/' + location_id + '/' + task_id + '/' + active;
							window.location.href = redirectUrl;
						}, 100);
					}
					else if(current_url.includes(page_url4))
					{
						setTimeout(function() {
							var location_id = $('#location_id').val();
							var baseUrl = "{{ url('/location-details') }}";
							var redirectUrl = baseUrl + '/' + location_id;
							window.location.href = redirectUrl;
						}, 100);
					}
					else{
						history.back();
					}
				}
		        @endif
				
				@if(auth()->check() && auth()->user()->user_type == 3)
				{
					var app_url =  "{{ env('APP_URL') }}";
					var page_url1 = app_url+'/los-task-status';
					var page_url2 = app_url+'/inspector-checklist-question-reply/';
					
					var page_url3 = app_url + '/inspector-subchecklist-question-reply/';
					var page_url4 = app_url + '/inspector-checklist-second-approve-by-lo/';
					var page_url5 = app_url + '/inspector-subchecklist-second-approve-plan-by-lo/';
					
					var page_url6 = app_url + '/ia-los-checklist-completed-approved-view/';
					
					var current_url = window.location.href;
					var active = 1;
					if(current_url.includes(page_url1))
					{
						setTimeout(function() {
							var location_id = $('#location_id').val();
							var baseUrl = "{{ url('/inspector-dashboard') }}";
							var redirectUrl = baseUrl;
							window.location.href = redirectUrl;
						}, 100);
					}
					
					if(current_url.includes(page_url2))
					{
						setTimeout(function() {
							var location_id = $('#location_id').val();
							var baseUrl = "{{ url('/los-task-status') }}";
							var redirectUrl = baseUrl + '/' + location_id + '/' + active;
							window.location.href = redirectUrl;
						}, 100);
					}
					
					if(current_url.includes(page_url3))
					{
						setTimeout(function() {
							var location_id = $('#location_id').val();
							var baseUrl = "{{ url('/los-task-status') }}";
							var redirectUrl = baseUrl + '/' + location_id + '/' + active;
							window.location.href = redirectUrl;
						}, 100);
					}
					if(current_url.includes(page_url4))
					{
						setTimeout(function() {
							var location_id = $('#location_id').val();
							var baseUrl = "{{ url('/los-task-status') }}";
							var redirectUrl = baseUrl + '/' + location_id + '/' + active;
							window.location.href = redirectUrl;
						}, 100);
					}
					if(current_url.includes(page_url5))
					{
						setTimeout(function() {
							var location_id = $('#location_id').val();
							var baseUrl = "{{ url('/los-task-status') }}";
							var redirectUrl = baseUrl + '/' + location_id + '/' + active;
							window.location.href = redirectUrl;
						}, 100);
					}
					if(current_url.includes(page_url6))
					{
						setTimeout(function() {
							var location_id = $('#location_id').val();
							var baseUrl = "{{ url('/los-task-status') }}";
							var redirectUrl = baseUrl + '/' + location_id + '/' + active;
							window.location.href = redirectUrl;
						}, 100);
					}

				}				
				@endif
				
				@if(auth()->check() && auth()->user()->user_type == 2)
				{
					var app_url =  "{{ env('APP_URL') }}";
					var page_url1 = app_url + '/lo-task-status';
					var page_url2 = app_url+'/location-owner-checklist-question-reply';
					var page_url3 =app_url +'/location-owner-subchecklist-question-reply';
					
					
					var page_url5 = app_url + '/location-owner-subchecklist-rejected-question-reply/';
					
					var current_url = window.location.href;
					//alert(page_url);alert(current_url);
					var active = 1;
					var location_id = $('#location_id').val();
					if(current_url.includes(page_url1))
					{
						var task_id = $('#task_id').val();
						setTimeout(function() {
							var baseUrl = "{{ url('/inspector-dashboard') }}";
							var redirectUrl = baseUrl; 
							window.location.href = redirectUrl;
						}, 100);
						/*setTimeout(function() {
							var baseUrl = "{{ url('/lo-task-status') }}";
							var redirectUrl = baseUrl + '/' + location_id + '/' +  active; 
							window.location.href = redirectUrl;
						}, 100);*/
					}
					else if(current_url.includes(page_url2))
					{
						setTimeout(function() {
							var baseUrl = "{{ url('/lo-task-status') }}";
							var redirectUrl = baseUrl + '/' + location_id + '/' +  active;
							window.location.href = redirectUrl;
						}, 100);
					}
					else if(current_url.includes(page_url3))
					{
						setTimeout(function() {
							var baseUrl = "{{ url('/lo-task-status') }}";
							var redirectUrl = baseUrl + '/' + location_id + '/' +  active;
							window.location.href = redirectUrl;
						}, 100);
					}
					else if(current_url.includes(page_url5))
					{
						setTimeout(function() {
							var baseUrl = "{{ url('/lo-task-status') }}";
							var redirectUrl = baseUrl + '/' + location_id + '/' +  active;
							window.location.href = redirectUrl;
						}, 100);
					}
					else{
						history.back();
					}
				}
		        @endif
				
				@if(auth()->check() && auth()->user()->user_type == 4)
				{
					var app_url =  "{{ env('APP_URL') }}";
					var page_url1 = app_url+'/management-location';
					var page_url2 = app_url+'/management-location-task-details/';
					
					//var page_url3 = app_url + '/inspector-subchecklist-question-reply/';
					//var page_url4 = app_url + '/inspector-checklist-second-approve-by-lo/';
					//var page_url5 = app_url + '/inspector-subchecklist-second-approve-plan-by-lo/';
					
					//var page_url6 = app_url + '/ia-los-checklist-completed-approved-view/';
					
					var current_url = window.location.href;
					var active = 1;
					if(current_url.includes(page_url1))
					{
						setTimeout(function() {
							var location_id = $('#location_id').val();
							var baseUrl = "{{ url('/management-dashboard') }}";
							var redirectUrl = baseUrl;
							window.location.href = redirectUrl;
						}, 100);
					}
					
					if(current_url.includes(page_url2))
					{
						setTimeout(function() {
							var location_id = $('#location_id').val();
							var baseUrl = "{{ url('/management-location') }}";
							var redirectUrl = baseUrl + '/' + location_id;
							window.location.href = redirectUrl;
						}, 100);
					}
					else{
						history.back();
					}
				}
				@endif
			}
		</script>
		@yield('scripts')
		@yield('component-scripts')
	</body>
</html>