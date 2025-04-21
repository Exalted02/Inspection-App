@extends('layouts.app')
@section('content')
@php
//echo "<pre>";print_r($delivery_address);die;
$retailer_name = App\Models\User::where('id', $retailer_id)->first()->name;
$customer_details = App\Models\User::where('id', $customer_id)->first();

$country = '';
$state = '';
$city = '';
if(!empty($customer_details->country))
{
	$country = App\Models\Countries::where('id',$customer_details->country)->first()->name;
}

if(!empty($customer_details->state))
{
	$state = App\Models\States::where('id',$customer_details->state)->first()->name;
}

if(!empty($customer_details->city))
{
	$city = App\Models\Cities::where('id',$customer_details->city)->first()->name;
}
@endphp

<!-- Page Wrapper -->
<div class="page-wrapper">
	<!-- Page Content -->
	<div class="content container-fluid">
	
		<!-- Page Header -->
		<div class="page-header">
			<div class="row align-items-center">
				<div class="col-md-4">
					<h3 class="page-title">{{ __('order_details') }}</h3>
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
				{{--<li class="nav-item"><a class="nav-link active" href="#all_orders" data-bs-toggle="tab" aria-expanded="true">{{ __('orders') }}</a></li>--}}
						{{--<li class="nav-item"><a class="nav-link active" href="#all_reviews" data-bs-toggle="tab" aria-expanded="true">{{ __('reviews') }}</a></li>--}}
							{{--<li class="nav-item"><a class="nav-link" href="#delivery_address" data-bs-toggle="tab" aria-expanded="false">{{ __('delivery_address') }}</a></li>--}}
								{{--<li class="nav-item"><a class="nav-link" href="#wistlist" data-bs-toggle="tab" aria-expanded="false">{{ __('wistlist') }}</a></li>--}}
				</ul>
				<div class="tab-content">
					<div class="tab-pane show active" id="all_orders">
						<div class="contact-tab-wrap">
							@if(!empty($order_dtls))
								@foreach($order_dtls as $details)
								<div class="multiadd d-flex flex-wrap">
									<div class="col-md-9 mt-3">
										  <strong>{{ __('final_amount') }}</strong>
										  <div>{{$details->final_amount ?? '' }}</div>
									</div>
									<div class="rowline"></div>
									@foreach($details->order_details as $key=>$val)
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
											  <div>{{ $val->color_id ?? '' }}</div>
										</div>
										<div class="col-md-3 mt-3">
											  <strong>{{ __('size') }}</strong>
											  <div>{{ $val->size_id ?? '' }}</div>
										</div>
									</div>
									@if($key < count($details->order_details)-1)
									<div class="rowline"></div>
									@endif
									@endforeach
									
									@if(!empty($delivery_address))
										<div class="rowline"></div>
								        <div class="row col-md-12 mt-4"><h4><strong>{{ __('delivery_address') }}</strong></h4></div>
										<div class="row col-md-12">
										{{--<div class="col-md-3 mt-3">
												<strong>{{ __('customer_name') }}</strong>
												<div>{{ $retailer_name ?? 'N/A' }}</div>
											</div>--}}
											
											<div class="col-md-3 mt-3">
												<strong>{{ __('address_type') }}</strong>
												<div>{{ $delivery_address->address_type ?? 'N/A' }}</div>
											</div>
											
											<div class="col-md-3 mt-3">
												  <strong>{{ __('phone_number') }}</strong>
												  <div>{{ $delivery_address->phone_number ?? 'N/A' }}</div>
											</div>
											<div class="col-md-3 mt-3">
												  <strong>{{ __('address') }}</strong>
												  <div>{{ $delivery_address->address ?? 'N/A' }}</div>
											</div>
											<div class="col-md-3 mt-3">
												  <strong>{{ __('date') }}</strong>
												  <div>{{ $delivery_address->date ?? 'N/A' }}</div>
											</div>
										</div>
									@endif
									
									@if(!empty($customer_details))
									{{--<div class="rowline"></div>
								        <div class="row col-md-12 mt-4"><h4><strong>{{ __('customer_details') }}</strong></h4></div>
										<div class="row col-md-12">
											<div class="col-md-3 mt-3">
												<strong>{{ __('customer_name') }}</strong>
												<div>{{ $customer_details->name ?? 'N/A' }}</div>
											</div>
											
											<div class="col-md-3 mt-3">
												<strong>{{ __('email') }}</strong>
												<div>{{ $customer_details->email ?? 'N/A' }}</div>
											</div>
											
											<div class="col-md-3 mt-3">
												  <strong>{{ __('phone_number') }}</strong>
												  <div>{{ $customer_details->phone_number ?? 'N/A' }}</div>
											</div>
											<div class="col-md-3 mt-3">
												  <strong>{{ __('address') }}</strong>
												  <div>{{ $customer_details->address ?? 'N/A' }}</div>
											</div>
											@if(!empty($country))
											<div class="col-md-3 mt-3">
												  <strong>{{ __('country') }}</strong>
												  <div>{{ $country ?? 'N/A' }}</div>
											</div>
											@endif
											
											@if(!empty($state))
											<div class="col-md-3 mt-3">
												  <strong>{{ __('state') }}</strong>
												  <div>{{ $state ?? 'N/A' }}</div>
											</div>
											@endif
											
											@if(!empty($city))
											<div class="col-md-3 mt-3">
												  <strong>{{ __('city') }}</strong>
												  <div>{{ $city ?? 'N/A' }}</div>
											</div>
											@endif
											
											@if(!empty($customer_details->zipcode))
											<div class="col-md-3 mt-3">
												  <strong>{{ __('pincode') }}</strong>
												  <div>{{ $customer_details->zipcode ?? 'N/A' }}</div>
											</div>
											@endif
										</div>--}}
									@endif
								</div>
								
								@endforeach
							@endif
							</div>
					</div>
					<div class="tab-pane" id="delivery_address">
						
					</div>
					<div class="tab-pane" id="wistlist">
						
					
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
