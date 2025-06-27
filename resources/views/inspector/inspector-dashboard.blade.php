@extends('layouts.app')
@section('content')
@php 
//echo "<pre>";print_r($userdata);die;
$path = '';
if(auth()->user()->user_type == 1)
{
	$path = 'inspector';
	$user_type_name = 'Inspector';
}

if(auth()->user()->user_type == 2)
{
	$path = 'locationowner';
	$user_type_name = 'Location owner';
}

if(auth()->user()->user_type == 3)
{
	$path = 'locationownersupervisor';
	$user_type_name = 'Location owner supervisor';
}

$backgroung_img = url('images/noimages/noimage_background_avatar.png');
$profile_img = url('images/noimages/noimage_avatar.png');

if(!empty($userdata->background_image))
{
	$backgroung_img = url('uploads/profile/' .$userdata->id .'/'. $path .'/'. $userdata->background_image);
}

if(!empty($userdata->profile_image))
{
	$profile_img = url('uploads/profile/' .$userdata->id .'/'. $path  . '/'. $userdata->profile_image);
}

@endphp
    <!-- =-=-=-=-=-=-= Breadcrumb =-=-=-=-=-=-= -->
	<div class="profile-card">
		<div class="profile-banner" style="background-image: url( '{{ $backgroung_img ?? '' }} ')">
			<div class="mega-menu">
				<ul class="menu-logo">
					<li>
						<div class="menu-mobile-collapse-trigger"><span></span></div>
					</li>
				</ul>
				<ul class="menu-links" style="display: none !important; max-height: 400px; overflow: auto;">
					<li>
						<a href="{{ route('inspector-dashboard')}}">Dashboard</a>
					</li>
					<li>
						<a href="{{route('logout')}}">Logout</a>
					</li>
				</ul>
			</div>
		</div>
		<div class="profile-info">
			<img class="profile-avatar" src="{{ $profile_img ?? '' }}" alt="Profile Picture">
			<h2 class="profile-name">{{ $userdata->name ?? ''}}</h2>
			<p class="profile-description">
			{{$user_type_name ?? ''}} at {{ $userdata->get_company->company_name ?? '' }}<br>
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
							$loc_image = $lacationData && $lacationData->image != null ? url('uploads/location/' .$lacationData->image) : url('images/noimages/noimage_region.png');
							
							$total_task = App\Models\Task_lists::where('inspector_id',  auth()->user()->id)->where('location_id', $locations->location_id)->count();
						@endphp
                            <div class="col-md-4 col-xs-6 col-sm-6">
								<div class="category-grid-box-1">
								@if(auth()->user()->user_type == 1)
								<a title="" href="{{route('location-details', ['id' => $locations->location_id ])}}">
									<div class="image" style="background-image: url('{{ $loc_image }}');">
										<img alt="Test" src="{{ $loc_image  }}" class="img-responsive d-none">
										<div class="ribbon popular"></div>
										<div class="price-tag">
											<div class="price"><span>{{ $total_task ?? '0'}} pending tasks</span></div>
										</div>
									</div>
									<div class="short-description-1 clearfix">
										<h3>{{ $lacationData->location_name ?? '' }}</h3>
									</a>
								@elseif(auth()->user()->user_type == 3)
									<a title="" href="{{route('los-task-status', ['id' => $locations->location_id, 'active'=>1])}}">
									<div class="image" style="background-image: url('{{ $loc_image }}');">
										<img alt="Test" src="{{ $loc_image  }}" class="img-responsive d-none">
										<div class="ribbon popular"></div>
										{{--<div class="price-tag">
										<div class="price"><span>4 pending tasks</span></div>
										</div>--}}
									</div>
									<div class="short-description-1 clearfix">
										<h3>{{ $lacationData->location_name ?? '' }}</h3>
									</a>
								
								@elseif(auth()->user()->user_type == 2)
									<a title="" href="{{ route('lo-task-status', ['id' => $locations->location_id, 'active'=>1 ])}}">
									<div class="image" style="background-image: url('{{ $loc_image }}');">
										<img alt="Test" src="{{ $loc_image  }}" class="img-responsive d-none">
										<div class="ribbon popular"></div>
										{{--<div class="price-tag">
											<div class="price"><span>4 pending tasks</span></div>
										</div>--}}
									</div>
									<div class="short-description-1 clearfix">
										<h3>{{ $lacationData->location_name ?? '' }}</h3>
								</a>
								
								@endif
								
								{{--<div class="category-title"> <span>{{ $city ?? '' }}, {{ $state ?? '' }}, {{ $country ?? '' }}, {{ $lacationData->zipcode ?? '' }}</span> </div>--}}
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

