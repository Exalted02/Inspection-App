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
					<h3 class="page-title">{{ __('customer') }}</h3>
					<ul class="breadcrumb">
						<li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('dashboard') }}</a></li>
						<li class="breadcrumb-item active">{{ __('customer') }}</li>
					</ul>
				</div>
				{{--<div class="col-md-8 float-end ms-auto">
					<div class="d-flex title-head">
						<div class="view-icons">
							<a href="#" class="grid-view btn btn-link"><i class="las la-redo-alt"></i></a>
							<a href="#" class="list-view btn btn-link" id="collapse-header"><i class="las la-expand-arrows-alt"></i></a>
							<a href="javascript:void(0);" class="list-view btn btn-link" id="filter_search"><i class="las la-filter"></i></a>
						</div>
						<a href="#" class="btn add-btn" data-bs-toggle="modal" data-bs-target="#add_edit_model"><i class="la la-plus-circle"></i> {{ __('add_customer') }}</a>
					</div>
				</div>--}}
			</div>
		</div>
		<!-- /Page Header -->
		
		<!-- Search Filter -->
		{{--<div class="filter-filelds" id="filter_inputs">
		<form name="search-frm" method="post" action="{{ route('user.category')}}" id="search-product-code-frm">
		@csrf
			<div class="row filter-row">
				<div class="col-xl-3">  
					 <div class="input-block">
						 <input type="text" class="form-control floating" name="search_name" placeholder="{{ __('category')}}">
					 </div>
				</div>
				<div class="col-xl-3">  
					 <div class="input-block">
						<input type="text" class="form-control  date-range bookingrange" name="date_range_phone" placeholder="{{ __('from_to_date')}}">
					 </div>
				</div>
				<div class="col-xl-3">  
					 <div class="input-block">
						 <select class="select" name="search_status">
							<option value="">{{ __('please_select') }}</option>
							<option value="1">{{ __('active') }}</option>
							<option value="0">{{ __('inactive') }}</option>
						</select>
					 </div>
				</div>
				<div class="col-xl-3">  
				<a href="javascript:void(0);" class="btn btn-success w-100 search-data"><i class="fa-solid fa-magnifying-glass"></i> {{ __('search') }} </a> 
				</div>
			</div>
			</form>
		</div>--}}
		 <hr>
		 <!-- /Search Filter -->
		 <div class="row">
		 @if($customers->count() > 0)
		 <div class="col-lg-6 mb-2">
				<div class="btn-group">
					<button type="button" class="btn action-btn add-btn dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">Action</button>
					<ul class="dropdown-menu">
						<li><a class="dropdown-item" onclick="change_multi_status('1','User','{{url('change-multi-status')}}')">Active</a></li>
						<li><a class="dropdown-item" onclick="change_multi_status('0','User','{{url('change-multi-status')}}')">Inactive</a></li>
						<li><a class="dropdown-item" onclick="delete_multi_data('User','{{url('delete-multi-data')}}')">Delete</a></li>
					</ul>
				</div>
			</div>
		@endif
		</div>

		<div class="row">
			<div class="col-md-12">
				<div class="table-responsive">
					<table class="table table-striped custom-table datatable">
						<thead>
							<tr>
							@if($customers->count() > 0)
							<th>
									<label class="form-check form-check-inline">
										<input class="form-check-input" type="checkbox" id="checkAll">
									</label>
								</th>
							@endif
								{{--<th>{{ __('sl_no') }}</th>--}}
								<th>{{ __('customer') }}</th>
								<th>{{ __('email') }}</th>
								<th>{{ __('phone') }}</th>
								<th>{{ __('city') }}</th>
								<th>{{ __('state') }}</th>
								<th>{{ __('zipcode') }}</th>
								<th>{{ __('created_date') }}</th>
								<th>{{ __('status') }}</th>
								<th class="text-end">Action</th>
							</tr>
						</thead>
						<tbody>
						@foreach($customers as $val)
						
						

							<tr>
								@if($customers->count() > 0)
								<td>
									<label class="form-check form-check-inline">
										<input class="form-check-input" type="checkbox" name="chk_id" data-emp-id="{{$val->id}}">
									</label>
								</td>
								@endif
								<td>{{ $val->name ?? ''}}</td>
								<td>{{ $val->email ?? ''}}</td>
								<td>{{ $val->phone_number ?? ''}}</td>
								<td>{{ $val->city ?? ''}}</td>
								<td>{{ $val->state ?? ''}}</td>
								<td>{{ $val->zipcode ?? ''}}</td>
								<td>{{ date('d/m/Y', strtotime($val->created_at)) ?? ''}}</td>
								<td>
								@if($val->status ==1)
									<div class="dropdown action-label">
										<a class="btn btn-white btn-sm badge-outline-success dropdown-toggle" href="#" data-bs-toggle="dropdown" aria-expanded="false">
											<i class="fa-regular fa-circle-dot text-success"></i> {{ __('active') }}
										</a>
										<div class="dropdown-menu dropdown-menu-right">
											<a class="dropdown-item update-status" href="javascript:void(0);" data-id="{{ $val->id }}" data-url="{{ route('customer-update-status') }}"><i class="fa-regular fa-circle-dot text-success"></i> {{ __('active') }}</a>
											<a class="dropdown-item update-status" href="javascript:void(0);" data-id="{{ $val->id }}" data-url="{{ route('customer-update-status') }}"><i class="fa-regular fa-circle-dot text-danger"></i> {{ __('inactive') }}</a>
										</div>
									</div>
								 @else
									<div class="dropdown action-label">
										<a class="btn btn-white btn-sm badge-outline-danger dropdown-toggle" href="#" data-bs-toggle="dropdown" aria-expanded="false">
											<i class="fa-regular fa-circle-dot text-danger"></i> {{ __('inactive') }}
										</a>
										<div class="dropdown-menu dropdown-menu-right">
											<a class="dropdown-item update-status" href="javascript:void(0);" data-id="{{ $val->id }}" data-url="{{ route('customer-update-status') }}"><i class="fa-regular fa-circle-dot text-success"></i> {{ __('active') }}</a>
											<a class="dropdown-item update-status" href="javascript:void(0);" data-id="{{ $val->id }}" data-url="{{ route('customer-update-status') }}"><i class="fa-regular fa-circle-dot text-danger"></i> {{ __('inactive') }}</a>
										</div>
									</div> 
								 
								 @endif
								</td>
									
								
								<td class="text-end">
									<div class="dropdown dropdown-action">
										<a href="#" class="action-icon dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false"><i class="material-icons">more_vert</i></a>
										<div class="dropdown-menu dropdown-menu-right">
										
										{{--<a class="dropdown-item" href="{{ route('view-dealer', ['id'=>$val->id]) }}"><i class="fa-regular fa-eye m-r-5"></i> {{ __('view') }}</a>--}}
											{{--<a class="dropdown-item edit-customer" href="javascript:void(0);" data-id="{{ $val->id ??''}}" data-url=""><i class="fa-solid fa-pencil m-r-5"></i> {{ __('edit') }}</a>--}}
											<a class="dropdown-item" href="{{ route('view-customer', $val->id) }}"><i class="fa-regular fa-eye m-r-5"></i> {{ __('view') }}</a>
											<a class="dropdown-item delete-customer" href="javascript:void(0);" data-id="{{ $val->id ?? '' }}" data-url="{{ route('getDeleteCustomer') }}"><i class="fa-regular fa-trash-can m-r-5"></i> {{ __('delete') }}</a>
										</div>
									</div>
								</td>
							</tr>
							@endforeach
						</tbody>
					</table>
				</div>
			</div>
		</div>
	</div>
</div>
	<!-- /Page Content -->
@include('modal.customer-modal')
@include('modal.common')
@endsection 
@section('scripts')
@include('_includes.footer')
<script src="{{ url('front-assets/js/page/customers.js') }}"></script>
@endsection
