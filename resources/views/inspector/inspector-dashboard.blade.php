@extends('layouts.app')
@section('content')
@php 
//echo "<pre>";print_r($userdata);die;
@endphp
    <!-- =-=-=-=-=-=-= Breadcrumb =-=-=-=-=-=-= -->
	<div class="profile-card">
		<div class="profile-banner" style="background-image: url( '{{url('uploads/profile/' .$userdata->id .'/inspector/'. $userdata->background_image )}} ')"></div>
		<div class="profile-info">
			<img class="profile-avatar" src="{{ url('uploads/profile/' .$userdata->id .'/inspector/'. $userdata->profile_image)}}" alt="Profile Picture">
			<h2 class="profile-name">{{ $userdata->name ?? ''}}</h2>
			<p class="profile-description">
				Inspector at {{ $userdata->get_company->company_name ?? '' }},<br>
			</p>
		</div>
	</div>
    <!-- =-=-=-=-=-=-= Breadcrumb End =-=-=-=-=-=-= --> 
    <!-- =-=-=-=-=-=-= Main Content Area =-=-=-=-=-=-= -->
    <div class="main-content-area clearfix">
        <section class="custom-padding gray">
            <div class="container">
               <div class="row">
					<!-- Heading Area -->
					<div class="heading-panel">
					   <div class="col-xs-12 col-md-7 col-sm-6 left-side">
						  <!-- Main Title -->
						  <h1>All your locations</h1>
					   </div>
					</div>
					<!-- Heading Area End -->        
					<div class="col-sm-12 col-xs-12 col-md-12">                     
                    <!-- Latest Featured Ads  -->
                    <div class="row ">
                     	<div class="grid-style-2">
						@foreach($userdata->get_user_location as $locations)
						@php
							$lacationData = App\Models\Manage_location::where('id',$locations->location_id)->first();
							$city = App\Models\Cities::where('id', $lacationData->city_id)->first()->name;
							$state = App\Models\States::where('id', $lacationData->state_id)->first()->name;
							$country = App\Models\Countries::where('id', $lacationData->country_id)->first()->name;
						@endphp
                            <div class="col-md-4 col-xs-12 col-sm-6">
								<div class="category-grid-box-1">
									<div class="image">
									{{--<img alt="Test" src="{{url('front-assets/images/posting/10.jpg')}}" class="img-responsive">--}}
										<img alt="Test" src="{{url('uploads/location/' .$lacationData->image)}}" class="img-responsive">
										<div class="ribbon popular"></div>
										<div class="price-tag">
											<div class="price"><span>4 pending tasks</span></div>
										</div>
									</div>
									<div class="short-description-1 clearfix">
										<h3><a title="" href="{{route('location-details', ['id' => $locations->location_id ])}}">{{ $lacationData->location_name ?? '' }}</a></h3>
										<div class="category-title"> <span>{{ $city ?? '' }}, {{ $state ?? '' }}, {{ $country ?? '' }}, {{ $lacationData->zipcode ?? '' }}</span> </div>
									</div>
								</div>
                            </div>
						@endforeach
                        </div>
                     </div>
                  </div>
               </div>
            </div>
        </section>
    </div>
@endsection 
@section('scripts')

@endsection

