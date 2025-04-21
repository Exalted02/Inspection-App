@extends('layouts.app')
@section('content')
<!-- Page Wrapper -->
<div class="page-wrapper">
	<!-- Page Content -->
	<div class="content container-fluid">
	
		<!-- Page Header -->
		<div class="page-header">
			<div class="row align-items-center">
				<div class="col-md-4">
					<h3 class="page-title">User Accounts</h3>
					<ul class="breadcrumb">
						<li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('dashboard') }}</a></li>
						<li class="breadcrumb-item active">{{ __('customer') }}</li>
					</ul>
				</div>
				{{--<div class="col-md-8 float-end ms-auto">
					<div class="d-flex title-head">
						<div class="view-icons">
							<a href="javascript:void(0);" class="list-view btn btn-link" id="filter_search"><i class="las la-filter"></i></a>
						</div>
					</div>
				</div>--}}
			</div>
		</div>
		<!-- /Page Header -->
		
		<!-- Search Filter -->
		{{--<div class="filter-filelds" id="filter_inputs">
			<form name="search-frm" method="post" action="" id="search-product-code-frm">
				@csrf
				<div class="row filter-row">
					<div class="col-xl-2">  
						 <div class="input-block">
							 <select class="select" name="search_status">
								<option value="">{{ __('please_select') }}</option>
								<option value="1">Last 30 Days</option>
								<option value="0">Last 2 Months</option>
							</select>
						 </div>
					</div>
					<div class="col-xl-2">  
						 <div class="input-block">
							 <input type="search" class="form-control floating" name="search_name" placeholder="Search by email">
						 </div>
					</div>
					<div class="col-xl-2">  
					<a href="javascript:void(0);" class="btn btn-success w-100 search-data"><i class="fa-solid fa-magnifying-glass"></i> {{ __('search') }} </a> 
					</div>
					<div class="col-xl-2 p-r-0">
						<button type="reset" class="btn custom-reset w-100 reset-button" data-id="1">
							<i class="fa-solid fa-rotate-left"></i> Reset
						</button>
					</div>
				</div>
			</form>
		</div>--}}
		<hr>
		<div class="filter-filelds">
			<div class="row filter-row">
				<div class="col-xl-2">  
					 <div class="input-block">
						 <select class="select" name="search_status">
							<option value="">{{ __('please_select') }}</option>
							<option value="1">Last 30 Days</option>
							<option value="0">Last 2 Months</option>
						</select>
					 </div>
				</div>
				<div class="col-xl-2">  
					 <div class="input-block">
						 <input type="search" class="form-control floating" name="search_name" placeholder="Search by email">
					 </div>
				</div>
				<div class="col-xl-2">  
				<a href="javascript:void(0);" class="btn btn-success w-100 search-data"><i class="fa-solid fa-magnifying-glass"></i> {{ __('search') }} </a> 
				</div>
				<div class="col-xl-2 p-r-0">
					<button type="reset" class="btn custom-reset w-100 reset-button" data-id="1">
						<i class="fa-solid fa-rotate-left"></i> Reset
					</button>
				</div>
			</div>
		</div>
		<div class="row">
			<div class="col-md-12">
				<div class="table-responsive">
					<table class="table table-striped custom-table datatable">
						<thead>
							<tr>
								<th>Name</th>
								<th>Email</th>
								<th>Phone</th>
								<th>Country</th>
								<th>Address</th>
								<th>Created At</th>
								<th class="text-end">Actions</th>
							</tr>
						</thead>
						<tbody>
							<tr>
								<td>Sebastian Arango</td>
								<td>sarango@gmail.com</td>
								<td>1234567897</td>
								<td>IN</td>
								<td>Delhi</td>
								<td>18 Aug 23</td>
								<td class="text-end">
									<div class="dropdown dropdown-action">
										<a href="#" class="action-icon dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false"><i class="material-icons">more_vert</i></a>
										<div class="dropdown-menu dropdown-menu-right">
											<a class="dropdown-item" href="javascript:void(0);" data-id="" data-url=""><i class="fa-solid fa-pencil m-r-5"></i> {{ __('edit') }}</a>
											<a class="dropdown-item" href=""><i class="fa-regular fa-eye m-r-5"></i> {{ __('view') }}</a>
											<a class="dropdown-item" href="javascript:void(0);" data-id="" data-url=""><i class="fa-regular fa-trash-can m-r-5"></i> {{ __('delete') }}</a>
										</div>
									</div>
								</td>
							</tr>
							<tr>
								<td>Devyan Johnson</td>
								<td>decyanjohnson@gmail.com</td>
								<td>--</td>
								<td>--</td>
								<td>--</td>
								<td>18 Sept 23</td>
								<td class="text-end">
									<div class="dropdown dropdown-action">
										<a href="#" class="action-icon dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false"><i class="material-icons">more_vert</i></a>
										<div class="dropdown-menu dropdown-menu-right">										
											<a class="dropdown-item" href="javascript:void(0);" data-id="" data-url=""><i class="fa-solid fa-pencil m-r-5"></i> {{ __('edit') }}</a>
											<a class="dropdown-item" href=""><i class="fa-regular fa-eye m-r-5"></i> {{ __('view') }}</a>
											<a class="dropdown-item" href="javascript:void(0);" data-id="" data-url=""><i class="fa-regular fa-trash-can m-r-5"></i> {{ __('delete') }}</a>
										</div>
									</div>
								</td>
							</tr>
						</tbody>
					</table>
				</div>
			</div>
		</div>
	</div>
</div>
	<!-- /Page Content -->
@include('modal.user-modal')
@include('modal.common')
@endsection 
@section('scripts')
@include('_includes.footer')
<script src="{{ url('front-assets/js/page/user.js') }}"></script>
@endsection
