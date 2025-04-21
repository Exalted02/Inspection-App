@extends('layouts.app')
@section('content')
@php 
//echo "<pre>";print_r($contacts);die;
@endphp
<!-- Page Wrapper -->
<div class="page-wrapper">
	<!-- Page Content -->
	<div class="content container-fluid">
	
		<!-- Page Header -->
		<div class="page-header">
			<div class="row align-items-center">
				<div class="col-md-4">
					<h3 class="page-title">{{ __('contact') }}</h3>
					<ul class="breadcrumb">
						<li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('dashboard') }}</a></li>
						<li class="breadcrumb-item active">{{ __('contact') }}</li>
					</ul>
				</div>
				<div class="col-md-8 float-end ms-auto">
					<div class="d-flex title-head">
						<div class="view-icons">
							<a href="#" class="grid-view btn btn-link"><i class="las la-redo-alt"></i></a>
							<a href="#" class="list-view btn btn-link" id="collapse-header"><i class="las la-expand-arrows-alt"></i></a>
							<a href="javascript:void(0);" class="list-view btn btn-link" id="filter_search"><i class="las la-filter"></i></a>
						</div>
						{{--<div class="form-sort">
							<a href="javascript:void(0);" class="list-view btn btn-link" data-bs-toggle="modal" data-bs-target="#export"><i class="las la-file-export"></i>{{ __('export') }}</a>
						</div>--}}
						<a href="#" class="btn add-btn" data-bs-toggle="modal" data-bs-target="#add_contact"><i class="la la-plus-circle"></i> {{ __('add_contact') }}</a>
					</div>
				</div>
			</div>
		</div>
		<!-- /Page Header -->
		
		<!-- Search Filter -->
		<div class="filter-filelds" id="filter_inputs">
		<form id="search-cont-grid" action="{{ route('load-more-contact') }}">
			<div class="row filter-row">
				<div class="col-xl-2">  
					 <div class="input-block mb-3 form-focus">
						 <input type="text" class="form-control floating" name="search_name" id="search_name">
						 <label class="focus-label">{{ __('contact')}}  {{ __('name')}}</label>
					 </div>
				</div>
				<div class="col-xl-2">  
					<div class="input-block mb-3 form-focus">
						<input type="text" class="form-control floating" name="search_email" id="search_email">
						<label class="focus-label">{{ __('email')}}</label>
					</div>
			   </div>
			   <div class="col-xl-2">  
					<div class="input-block mb-3 form-focus">
						<input type="text" class="form-control floating" name="search_phone" id="search_phone">
						<label class="focus-label">{{ __('phone')}} {{ __('number') }}</label>
					</div>
				</div>
				<div class="col-xl-2">  
					 <div class="input-block mb-3 form-focus focused">
						<input type="text" class="form-control  date-range bookingrange" id="date_range_phone_s" name="date_range_phone">
						 <label class="focus-label">{{ __('from_to_date')}}</label>
					 </div>
				 </div>
				<div class="col-xl-2"> 
					<div class="input-block mb-3 form-focus select-focus">
						<select class="select floating" name="search_country" id="search_country"> 
							<option value="">--{{ __('select') }}--</option>
							<option value="81">Germany</option>
							{{--<option value="">USA</option>--}}
							<option value="34">Canada</option>
							<option value="99">India</option>
							<option value="44">China</option>
						</select>
						<label class="focus-label">{{ __('location') }}</label>
					</div>
				</div>
				<div class="col-xl-2">  
					 <a href="javascript:void(0);" class="btn btn-success w-100 search-contact-grid"> {{ __('search') }} </a>  
				</div>     
			</div>
		</form>
		</div>
		 <hr>
		 <!-- /Search Filter -->
		<div class="filter-section">
			<ul>
				<li>
					<div class="view-icons">
						<a href="{{ route('user.contact')}}" class="list-view btn btn-link {{ request()->routeIs('user.contact') ? 'active' : '' }}"><i class="las la-list"></i></a>
						<a href="{{ route('user.contact-grid')}}" class="grid-view btn btn-link {{ request()->routeIs('user.contact-grid') ? 'active' : '' }}"><i class="las la-th"></i></a>
					</div>
				</li>
				<li>
				<form id="search-sortby-cont-grid" action="{{ route('load-more-contact') }}">
					<div class="form-sort">
						<i class="las la-sort-alpha-up-alt"></i>
						<select class="select form-control search-grid-sort-by" id="search_grid_sort_by">
							<option value="">{{ __('select_sort_by') }}</option>
							<option value="ASC" {{ request('search_sort_by') == 'ASC' ? 'selected' : '' }}>{{ __('select_sort_by_a_z') }}</option>
							<option value="DESC" {{ request('search_sort_by') == 'DESC' ? 'selected' : '' }}>{{ __('select_sort_by_z_a') }}</option>
							<option value="3">{{ __('recently_viewed') }}</option>
							<option value="4">{{ __('recently_added') }}</option>
						</select>
				</form>
					</div>
				</li>
				{{--<li>
					<div class="form-sorts dropdown">
						<a href="javascript:void(0);" class="dropdown-toggle" id="table-filter"><i class="las la-filter me-2"></i>Filter</a>
						<div class="filter-dropdown-menu">
							<div class="filter-set-view">
								<div class="filter-set-head">
									<h4>Filter</h4>
								</div>
								<div class="accordion" id="accordionExample">
									<div class="filter-set-content">
										<div class="filter-set-content-head">
											<a href="#" data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">Rating<i class="las la-angle-right"></i></a>
										</div>
										<div class="filter-set-contents accordion-collapse collapse show" id="collapseOne" data-bs-parent="#accordionExample">
											<ul>
												<li>
													<div class="filter-checks">
														<label class="checkboxs">
															<input type="checkbox" checked>
															<span class="checkmarks"></span>
														</label>
													</div>
													<div class="rating">
														<i class="fa fa-star filled"></i>
														<i class="fa fa-star filled"></i>
														<i class="fa fa-star filled"></i>
														<i class="fa fa-star filled"></i>
														<i class="fa fa-star filled"></i>
														<span>5.0</span>
													</div>
												</li>
												<li>
													<div class="filter-checks">
														<label class="checkboxs">
															<input type="checkbox">
															<span class="checkmarks"></span>
														</label>
													</div>
													<div class="rating">
														<i class="fa fa-star filled"></i>
														<i class="fa fa-star filled"></i>
														<i class="fa fa-star filled"></i>
														<i class="fa fa-star filled"></i>
														<i class="fa fa-star"></i>
														<span>4.0</span>
													</div>
												</li>
												<li>
													<div class="filter-checks">
														<label class="checkboxs">
															<input type="checkbox">
															<span class="checkmarks"></span>
														</label>
													</div>
													<div class="rating">
														<i class="fa fa-star filled"></i>
														<i class="fa fa-star filled"></i>
														<i class="fa fa-star filled"></i>
														<i class="fa fa-star"></i>
														<i class="fa fa-star"></i>
														<span>3.0</span>
													</div>
												</li>
												<li>
													<div class="filter-checks">
														<label class="checkboxs">
															<input type="checkbox">
															<span class="checkmarks"></span>
														</label>
													</div>
													<div class="rating">
														<i class="fa fa-star filled"></i>
														<i class="fa fa-star filled"></i>
														<i class="fa fa-star"></i>
														<i class="fa fa-star"></i>
														<i class="fa fa-star"></i>
														<span>2.0</span>
													</div>
												</li>
												<li>
													<div class="filter-checks">
														<label class="checkboxs">
															<input type="checkbox">
															<span class="checkmarks"></span>
														</label>
													</div>
													<div class="rating">
														<i class="fa fa-star filled"></i>
														<i class="fa fa-star"></i>
														<i class="fa fa-star"></i>
														<i class="fa fa-star"></i>
														<i class="fa fa-star"></i>
														<span>1.0</span>
													</div>
												</li>
											</ul>
										</div>
									</div>
									<div class="filter-set-content">
										<div class="filter-set-content-head">
											<a href="#" data-bs-toggle="collapse" data-bs-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">Owner<i class="las la-angle-right"></i></a>
										</div>
										<div class="filter-set-contents accordion-collapse collapse" id="collapseTwo" data-bs-parent="#accordionExample">
											<ul>
												<li>
													<div class="filter-checks">
														<label class="checkboxs">
															<input type="checkbox" checked>
															<span class="checkmarks"></span>
														</label>
													</div>
													<div class="collapse-inside-text">
														<h5>Hendry</h5>
													</div>
												</li>
												<li>
													<div class="filter-checks">
														<label class="checkboxs">
															<input type="checkbox">
															<span class="checkmarks"></span>
														</label>
													</div>
													<div class="collapse-inside-text">
														<h5>Guillory</h5>
													</div>
												</li>
												<li>
													<div class="filter-checks">
														<label class="checkboxs">
															<input type="checkbox">
															<span class="checkmarks"></span>
														</label>
													</div>
													<div class="collapse-inside-text">
														<h5>Jami</h5>
													</div>
												</li>
												<li>
													<div class="filter-checks">
														<label class="checkboxs">
															<input type="checkbox">
															<span class="checkmarks"></span>
														</label>
													</div>
													<div class="collapse-inside-text">
														<h5>Theresa</h5>
													</div>
												</li>
												<li>
													<div class="filter-checks">
														<label class="checkboxs">
															<input type="checkbox">
															<span class="checkmarks"></span>
														</label>
													</div>
													<div class="collapse-inside-text">
														<h5>Espinosa</h5>
													</div>
												</li>
											</ul>
										</div>
									</div>
									<div class="filter-set-content">
										<div class="filter-set-content-head">
											<a href="#" data-bs-toggle="collapse" data-bs-target="#collapseThree" aria-expanded="false" aria-controls="collapseThree">Tags<i class="las la-angle-right"></i></a>
										</div>
										<div class="filter-set-contents accordion-collapse collapse" id="collapseThree" data-bs-parent="#accordionExample">
											<ul>
												<li>
													<div class="filter-checks">
														<label class="checkboxs">
															<input type="checkbox" checked>
															<span class="checkmarks"></span>
														</label>
													</div>
													<div class="collapse-inside-text">
														<h5>Promotion</h5>
													</div>
												</li>
												<li>
													<div class="filter-checks">
														<label class="checkboxs">
															<input type="checkbox">
															<span class="checkmarks"></span>
														</label>
													</div>
													<div class="collapse-inside-text">
														<h5>Rated</h5>
													</div>
												</li>
												<li>
													<div class="filter-checks">
														<label class="checkboxs">
															<input type="checkbox">
															<span class="checkmarks"></span>
														</label>
													</div>
													<div class="collapse-inside-text">
														<h5>Rejected</h5>
													</div>
												</li>
												<li>
													<div class="filter-checks">
														<label class="checkboxs">
															<input type="checkbox">
															<span class="checkmarks"></span>
														</label>
													</div>
													<div class="collapse-inside-text">
														<h5>Collab</h5>
													</div>
												</li>
												<li>
													<div class="filter-checks">
														<label class="checkboxs">
															<input type="checkbox">
															<span class="checkmarks"></span>
														</label>
													</div>
													<div class="collapse-inside-text">
														<h5>Calls</h5>
													</div>
												</li>
											</ul>
										</div>
									</div>
								</div>
								
								<div class="filter-reset-btns">
									<a href="#" class="btn btn-light">Reset</a>
									<a href="#" class="btn btn-primary">Filter</a>
								</div>
							</div>
						</div>
					</div>
				</li>--}}
				{{--<li>
					<div class="search-set">
						<div class="search-input">
							<a href="#" class="btn btn-searchset"><i class="las la-search"></i></a>
							<div class="dataTables_filter">
								<label> <input type="search" class="form-control form-control-sm" placeholder="Search"></label>
							</div>
						</div>
					</div>
				</li>--}}
			</ul>
		</div>
		<!--  List section start -->
		<div class="row mt-4">
			<div class="row mt-4" id="contact-list"></div>
			
			<div class="col-lg-12">
				<div class="load-more-btn text-center" style="display:none">
					<a href="javascript:void(0);" class="btn btn-primary load-more-contact" data-url="{{ route('load-more-contact') }}">Load More Contacts<i class="spinner-border_s"></i></a>
				</div>
			</div>
			<input type="hidden"  id="moreload">
		</div>
		
		<!-- List section end    --->
	</div>
</div>
	<!-- /Page Content -->
<!-- /Page Wrapper -->


@include('modal.contact-modal')
<!-- /Export -->

@endsection 
@section('scripts')
<script>
$(document).ready(function() {
	var url = "{{ route('load-more-contact') }}";
	contact_grid(url);
})
</script>
@endsection
