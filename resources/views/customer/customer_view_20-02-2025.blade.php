@extends('layouts.app')
@section('content')
@php
//echo "<pre>";print_r($order_dtls);die;
@endphp

<!-- Page Wrapper -->
<div class="page-wrapper">
	<!-- Page Content -->
	<div class="content container-fluid">
	
		<!-- Page Header -->
		<div class="page-header">
			<div class="row align-items-center">
				<div class="col-md-4">
					<h3 class="page-title">{{ __('customer_view') }}</h3>
					<ul class="breadcrumb">
						<li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('dashboard') }}</a></li>
						<li class="breadcrumb-item active">{{ __('view') }}</li>
					</ul>
				</div>
			</div>
		</div>
		<!-- /Page Header -->
		{{--<div class="card">
			<div class="card-body">
				<ul class="nav nav-tabs nav-tabs-solid nav-justified">
					<li class="nav-item"><a class="nav-link active" href="user-dashboard.html">Reviews</a></li>
					<li class="nav-item"><a class="nav-link" href="user-all-jobs.html">All </a></li>
					<li class="nav-item"><a class="nav-link" href="saved-jobs.html">Saved</a></li>
					<li class="nav-item"><a class="nav-link" href="applied-jobs.html">Applied</a></li>
					<li class="nav-item"><a class="nav-link" href="interviewing.html">Interviewing</a></li>
					<li class="nav-item"><a class="nav-link" href="offered-jobs.html">Offered</a></li>
					<li class="nav-item"><a class="nav-link" href="visited-jobs.html">Visitied </a></li>
					<li class="nav-item"><a class="nav-link" href="archived-jobs.html">Archived </a></li>
				</ul>
			</div>
		</div>--}}
		<div class="project-task">
				<ul class="nav nav-tabs nav-tabs-top nav-justified mb-0">
					<li class="nav-item"><a class="nav-link active" href="#all_details" data-bs-toggle="tab" aria-expanded="true">{{ __('details') }}</a></li>
					<li class="nav-item"><a class="nav-link" href="#all_orders" data-bs-toggle="tab" aria-expanded="true">{{ __('orders') }}</a></li>
						{{--<li class="nav-item"><a class="nav-link active" href="#all_reviews" data-bs-toggle="tab" aria-expanded="true">{{ __('reviews') }}</a></li>--}}
					<li class="nav-item"><a class="nav-link" href="#delivery_address" data-bs-toggle="tab" aria-expanded="false">{{ __('delivery_address') }}</a></li>
					<li class="nav-item"><a class="nav-link" href="#wistlist" data-bs-toggle="tab" aria-expanded="false">{{ __('wistlist') }}</a></li>
				</ul>
				<div class="tab-content">
					<div class="tab-pane show active" id="all_details">
					 dsdsdsdsd
					</div>
					<div class="tab-pane show active" id="all_orders">
						<div class="contact-tab-wrap">
							@if($order_dtls->count() > 0)
								@foreach($order_dtls as $details)
								<div class="multiadd d-flex flex-wrap">
									<div class="col-md-9 mt-3">
										  <strong>{{ __('final_amount') }}</strong>
										  <div>{{$details->final_amount ?? '' }}</div>
									</div>
									{{--<div class="col-md-3 mt-3">
										 <strong>{{ __('order_status') }}</strong>
											<select class="select form-control order-status" id="order_status" style="height:40px;" data-id="{{ $details->id }}" data-url="{{ route('change.order.status')}}">
												<option value="">{{ __('please_select') }}</option>
												<option value="1" {{ $details->status ==1 ? 'selected' : ''; }}>{{ __('pending') }}</option>
												<option value="2" {{ $details->status ==2 ? 'selected' : ''; }}>{{ __('shipped') }}</option>
												<option value="3" {{ $details->status ==3 ? 'selected' : ''; }}>{{ __('cancel') }}</option>
												<option value="4" {{ $details->status ==4 ? 'selected' : ''; }}>{{ __('deliver') }}</option>
											</select>
										 
									</div>--}}
									<div class="rowline"></div>
									@foreach($details->order_details as $key=>$val)
									
									@php 
									$color = '';
									$size = '';
									$existscolor = 	App\Models\Color::where('id',$val->color_id)->exists();
									if($existscolor)
									{
										$color = 	App\Models\Color::where('id',$val->color_id)->first()->color;
									}
									
									$existssize =  App\Models\Size::where('id',$val->size_id)->exists();
									if($existssize)
									{
									$size = 	App\Models\Size::where('id',$val->size_id)->first()->size;
									}
									
									@endphp
									<div class="row col-md-12">
										<div class="col-md-3 mt-3">
											<strong>{{ __('product_name') }}</strong>
											<div>{{ $val->product_name ?? '' }}</div>
										</div>
										
										<div class="col-md-3 mt-3">
											  <strong>{{ __('product_code') }}</strong>
											  <div>{{ $val->product_code ?? '' }}</div>
										</div>
										<div class="col-md-3 mt-3">
											  <strong>{{ __('quantity') }}</strong>
											  <div>{{ $val->quantity ?? '' }}</div>
										</div>
										<div class="col-md-3 mt-3">
											  <strong>{{ __('price') }}</strong>
											  <div>{{ $val->price ?? '' }}</div>
										</div>
										<div class="col-md-3 mt-3">
											  <strong>{{ __('color') }}</strong>
											  <div>{{ $color ?? '' }}</div>
										</div>
										<div class="col-md-3 mt-3">
											  <strong>{{ __('size') }}</strong>
											  <div>{{ $size ?? '' }}</div>
										</div>
									</div>
									@if($key < count($details->order_details)-1)
									<div class="rowline"></div>
									@endif
									@endforeach
									
								</div>
								
								@endforeach
							@endif
							</div>
					</div>
					<div class="tab-pane" id="delivery_address">
						<div class="contact-tab-wrap">
						@if($delivery_address->isNotEmpty())
							@foreach($delivery_address as $addr)
							<div class="multiadd d-flex flex-wrap">
								<div class="col-md-4 mt-3">
									  <strong>{{ __('address_type') }}</strong>
									  <div>{{$addr->address_type ?? '' }}</div>
								</div>
								<div class="col-md-4 mt-3">
									  <strong>{{ __('phone_number') }}</strong>
									  <div>{{$addr->phone_number ?? '' }}</div>
								</div>
								<div class="col-md-4 mt-3">
									  <strong>{{ __('address') }}</strong>
									  <div>{{$addr->address ?? '' }}</div>
								</div>
								<div class="col-md-4 mt-3">
									  <strong>{{ __('date') }}</strong>
									  <div>{{ $addr->created_at ? \Carbon\Carbon::parse($addr->created_at)->format('d/m/Y') : '' }}</div>
								</div>
							
							</div>
							@endforeach
						@endif
						</div>
					</div>
					<div class="tab-pane" id="wistlist">
						<div class="contact-tab-wrap">
						@if($wistlists->isNotEmpty())
							@foreach($wistlists as $wistlist)
							<div class="multiadd d-flex flex-wrap">
								<div class="col-md-4 mt-3">
									  <strong>{{ __('email_address') }}</strong>
									  <div>{{$wistlist->email_address ?? '' }}</div>
								</div>
								<div class="col-md-4 mt-3">
									  <strong>{{ __('relationship') }}</strong>
									  <div>{{$wistlist->relationship ?? '' }}</div>
								</div>
								<div class="col-md-4 mt-3">
									  <strong>{{ __('birthdate') }}</strong>
									  <div>{{ $wistlist->birthdate ? \Carbon\Carbon::parse($addr->birthdate)->format('d/m/Y') : '' }}</div>
								</div>
								<div class="col-md-4 mt-3">
									  <strong>{{ __('aniversary') }}</strong>
									  <div>{{ $wistlist->aniversary ? \Carbon\Carbon::parse($addr->aniversary)->format('d/m/Y') : '' }}</div>
								</div>
							
							</div>
							@endforeach
						@endif
						</div>
					
					</div>
				</div>
		</div>
		 

		
	</div>
</div>
	<!-- /Page Content -->

@include('modal.common')
@endsection 
@section('scripts')
@include('_includes.footer')
<script src="{{ url('front-assets/css/dropzone.css') }}"></script>
<link href="https://cdnjs.cloudflare.com/ajax/libs/dropzone/4.0.1/min/dropzone.min.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/dropzone/4.2.0/min/dropzone.min.js"></script>
<script src="{{ url('front-assets/js/page/product.js') }}"></script>
<link href="{{ url('front-assets/summernote/summernote-lite.min.css') }}" rel="stylesheet">
<script src="{{ url('front-assets/summernote/summernote-lite.min.js') }}"></script>

<script>



</script>
@endsection
