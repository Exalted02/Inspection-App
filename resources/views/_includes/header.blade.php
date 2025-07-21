@if(request()->routeIs('inspector-dashboard'))

@else
<div class="colored-header">
	<div class="clearfix"></div>
	<!-- menu start -->
	<nav id="menu-1" class="mega-menu">
		<!-- menu list items container -->
		<section class="menu-list-items">
		   <div class="container">
			  <div class="row">
				 <div class="col-lg-12 col-md-12">
					<!-- menu logo -->
					<ul class="menu-logo">
					   <li class="d-flex justify-between">
							{{--<a href="{{ route('inspector-dashboard')}}">
								<span>Inspection</span>
							</a>--}}
							<div>
							<a href="javascript:void(0);" onclick="goBackAndReload();">
								<span><i class="fa fa-angle-left"></i></span>
							</a>
							</div>
							<div class="corrective-message" style="display:none;"></div>
							<div class="header-icon">
							@if(auth()->user()->user_type == 4)
								<a href="{{ route('management-dashboard')}}"><i class="fa-solid fa-house"></i></a>
							@else
								<a href="{{ route('inspector-dashboard')}}"><i class="fa-solid fa-house"></i></a>
							@endif
								<a href="{{ route('logout')}}"><i class="fa-solid fa-right-from-bracket"></i></a>
							</div>
					   </li>
					</ul>
					<!-- menu links -->
					<ul class="menu-links">
						<li>
							<a href="{{ route('inspector-dashboard')}}">Dashboard</a>
						</li>
						<li>
							<a href="{{route('logout')}}">Logout</a>
						</li>
					</ul>
					{{--<ul class="menu-search-bar">
					   <li>
						  <a>
							 <div class="contact-in-header clearfix">
								<i class="flaticon-customer-service"></i>
								<span>
								Call Us Now
								<br>
								<strong>111 222 333 444</strong>
								</span>
							 </div>
						  </a>
					   </li>
					</ul>--}}
				 </div>
			  </div>
		   </div>
		</section>
	</nav>
    <!-- menu end -->
</div>
<!-- =-=-=-=-=-=-= Main Header End  =-=-=-=-=-=-= -->
@endif