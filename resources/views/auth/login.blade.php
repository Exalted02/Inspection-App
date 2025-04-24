@extends('layouts.app')
@section('content')
	<!-- =-=-=-=-=-=-= Breadcrumb =-=-=-=-=-=-= -->
	{{--<div class="page-header-area-2 gray">
	 <div class="container">
		<div class="row">
		   <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
			  <div class="small-breadcrumb">
				 <div class=" breadcrumb-link">
					<ul>
					   <li><a href="javascript:void(0)">Login</a></li>
					</ul>
				 </div>
				 <div class="header-page">
					<h1>Sign In to your account</h1>
				 </div>
			  </div>
		   </div>
		</div>
	 </div>
  </div>--}}
  <!-- =-=-=-=-=-=-= Breadcrumb End =-=-=-=-=-=-= -->
  <!-- =-=-=-=-=-=-= Main Content Area =-=-=-=-=-=-= -->
  <div class="main-content-area clearfix">
	 <!-- =-=-=-=-=-=-= Latest Ads =-=-=-=-=-=-= -->
	 <section class="section-padding no-top1 gray">
		<!-- Main Container -->
		<div class="container">
		   <!-- Row -->
		   <div class="row">
			  <!-- Middle Content Area -->
			  <div class="col-md-6 col-md-offset-3 col-sm-8 col-sm-offset-2">
				 <!--  Form -->
				 <div class=" card login-card">
				 <div class="form-grid">
					<h2 class="text-center">Sign In to your account</h2>
					<form method="POST" action="{{ route('login') }}" id="loginForm">
					@csrf
					{{--<a class="btn btn-lg btn-block btn-social btn-facebook">
							<span class="fa fa-facebook"></span> Sign in with Facebook
					  </a>
					  
					  <a class="btn btn-lg btn-block btn-social btn-google">
							<span class="fa fa-google"></span> Sign in with Facebook
					  </a>
					  
					  <h2 class="no-span"><b>(OR)</b></h2>--}}
					
						<div class="form-group">
							<label>Email</label>
							<input type="email" id="email" name="email"  placeholder="Email" class="form-control" :value="old('email')" autofocus>
							<x-input-error :messages="$errors->get('email')" class="mt-2 text-danger" />
							@if (session('error'))
								<ul class="text-sm text-red-600 space-y-1 mt-2 text-danger">
									<li>{{ session('error') }}</li>
								</ul>
							@endif
						</div>
					   <div class="form-group">
						  <label>Password</label>
						  <input type="password" id="password" name="password" placeholder="Password" class="form-control">
						  <x-input-error :messages="$errors->get('password')" class="mt-2 text-danger" />
					   </div>
					   <div class="form-group">						  
						  <x-input-error :messages="$errors->get('g-recaptcha-response')" class="mt-2 text-danger" />
					   </div>
					   <div class="form-group">
						  <div class="row">
							 <div class="col-xs-12 col-sm-4">
								<div class="skin-minimal">
								   <ul class="list">
									  <li>
										 <input  type="checkbox" id="remember_me" name="remember">
										 <label for="remember_me">Remember me</label>
									  </li>
								   </ul>
								</div>
							 </div>
							 @if (Route::has('password.request'))
							 <div class="col-xs-12 col-sm-8 text-right">
								{{--<p class="help-block"><a data-target="#forgotPasswordModal" data-toggle="modal">Forgot password?</a>--}}
								<p class="help-block"><a href="{{ route('password.request') }}">Forgot password?</a>
								</p>
							 </div>
							 @endif
						  </div>
					   </div>
					   <button class="btn btn-primary btn-lg btn-block g-recaptcha" data-sitekey="{{ config('services.recaptcha.site_key') }}" data-callback='onSubmit' data-action='submit'>Login with us</button>
					</form>
				 </div>
				 </div>
				 <!-- Form -->
			  </div>
			  <!-- Middle Content Area  End -->
		   </div>
		   <!-- Row End -->
		</div>
		<!-- Main Container End -->
	 </section>
	</div>
@endsection
@section('component-scripts')
<script>
	function onSubmit(token) {
		document.getElementById("loginForm").submit();
	}
 </script>
@endsection
