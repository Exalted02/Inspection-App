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
					<h3 class="page-title">{{ __('notification') }}</h3>
					<ul class="breadcrumb">
						<li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('dashboard') }}</a></li>
						<li class="breadcrumb-item active">{{ __('notifications') }}</li>
					</ul>
				</div>
				{{--<div class="col-md-8 float-end ms-auto">
					<div class="d-flex title-head">
						<div class="view-icons">
							<a href="#" class="grid-view btn btn-link"><i class="las la-redo-alt"></i></a>
							<a href="#" class="list-view btn btn-link" id="collapse-header"><i class="las la-expand-arrows-alt"></i></a>
							<a href="javascript:void(0);" class="list-view btn btn-link" id="filter_search"><i class="las la-filter"></i></a>
						</div>
						<a href="#" class="btn add-btn add_color" data-bs-toggle="modal" data-bs-target="#add_color"><i class="la la-plus-circle"></i> {{ __('add_color') }}</a>
					</div>
				</div>--}}
			</div>
		</div>
		<!-- /Page Header -->
		
		<!-- Search Filter -->
		{{--<div class="filter-filelds" id="filter_inputs">
		<form name="search-frm" method="post" action="{{ route('user.color')}}" id="search-product-code-frm">
		@csrf
			<div class="row filter-row">
				<div class="col-xl-3">  
					 <div class="input-block">
						 <input type="text" class="form-control floating" name="search_name" placeholder="{{ __('color')}}">
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
			<div class="col-md-12">
				<div class="table-responsive">
					<table class="table table-striped custom-table datatable">
						<thead>
							<tr>
							
								<th>{{ __('order_id') }}</th>
								<th>{{ __('order_type') }}</th>
								<th>{{ __('message') }}</th>
								<th class="text-end">Action</th>
							</tr>
						</thead>
						<tbody>
						@foreach($notifications as $val)
						@php 
							$order_type = $val->wishlist_email !='' ? 'Wishlist' : 'Order';
						@endphp
							<tr>
								<td>{{ $val->order_id ?? ''}}</td>
								<td>{{ $order_type }}</td>
								<td>{{ $val->message ?? ''}}</td>
								<td class="text-end">
									<div class="dropdown dropdown-action">
										<a href="#" class="action-icon dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false"><i class="material-icons">more_vert</i></a>
										<div class="dropdown-menu dropdown-menu-right">
											<a class="dropdown-item" href="{{ route('notification-view', $val->id) }}"><i class="fa-regular fa-eye m-r-5"></i> {{ __('view') }}</a>
											
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
@include('modal.color-modal')
@include('modal.common')
@endsection 
@section('scripts')
@include('_includes.footer')
<script src="{{ url('front-assets/css/dropzone.css') }}"></script>
<link href="https://cdnjs.cloudflare.com/ajax/libs/dropzone/4.0.1/min/dropzone.min.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/dropzone/4.2.0/min/dropzone.min.js"></script>
<script src="{{ url('front-assets/js/page/color.js') }}"></script>

<script>
var remaining_upload = 12;
 Dropzone.options.imageUpload = {
        maxFiles: remaining_upload,
        acceptedFiles: ".jpeg,.jpg,.png,.gif",
        init: function() {
            this.on("maxfilesexceeded", function(file) {
                this.removeFile(file); // Remove the exceeded file
                //alert("Maximum upload limit reached!");
            });
        }
    };
	
	//var myDropzone = new Dropzone("#imageUpload", Dropzone.options.imageUpload);
</script>
@endsection
