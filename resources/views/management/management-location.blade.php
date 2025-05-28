@extends('layouts.app')
@section('content')
@php 
//echo "<pre>";print_r($task_details); die;
use Carbon\Carbon;
@endphp
    <div class="container location-details">
		<div class="d-flex align-items-center location-header mb-3">
			<img src="{{url('uploads/location/' . $location_details->image ?? '')}}" alt="Location" />
			<div>
				<div class="title">{{ $location_details->location_name ?? ''}}</div>
				<small class="text-muted"><i class="fa fa-location-dot mr-5px"></i>{{ $location_details->address ?? ''}}, {{ $location_details->zipcode ?? ''}}</small>
			</div>
		</div>
		<!-- =-=-=-=-=-=-= Breadcrumb End =-=-=-=-=-=-= --> 
		<!-- =-=-=-=-=-=-= Main Content Area =-=-=-=-=-=-= -->
		<div class="main-content-area clearfix">
			<section class="custom-padding1">
					<div class="pt-2 pb-2">
						<div class="row ">
							<div class="col-md-6 col-sm-6 col-xs-6 small-card-odd">
								<div class="bg small-card">
									<div class="small-card-title">No. of inspections</div>
									<div class="small-card-counter">4</div>
									<div class="small-card-counter-title">WEEKLY</div>
								</div>
							</div>
							<div class="col-md-6 col-sm-6 col-xs-6 small-card-even">
								<div class="bg small-card">
									<div class="small-card-title">No. of observations</div>
									<div class="small-card-counter">8</div>
									<div class="small-card-counter-title">WEEKLY</div>
								</div>
							</div>
							<div class="col-md-6 col-sm-6 col-xs-6 small-card-odd">
								<div class="bg small-card">
									<div class="small-card-title">Time to close observation</div>
									<div class="small-card-counter">6</div>
									<div class="small-card-counter-title">DAYS</div>
								</div>
							</div>
							<div class="col-md-6 col-sm-6 col-xs-6 small-card-even">
								<div class="bg small-card">
									<div class="small-card-title">Repeated observations found</div>
									<div class="small-card-counter">2</div>
									<div class="small-card-counter-title">WEEKLY</div>
								</div>
							</div>
						</div>
					</div>
				<div class="container">
					<div class="row">
					@if($task_details->isNotEmpty())
						@foreach($task_details as $tasks)
						@php 
							$month = Carbon::parse($tasks->created_at)->format('M');
							$day =   Carbon::parse($tasks->created_at)->format('d');
							$week= strtoupper(Carbon::parse($tasks->created_at)->format('D'));
							
							$inspector = App\Models\Task_lists::with('get_user')->where('id', $tasks->id)->first();
							//echo "<pre>";print_r($inspector);die;
						@endphp
							<div class="d-flex mb-3 task">
								<div class="date-box">
									<div class="date">
										<div class="day">{{ $month ?? ''}}</div>
										<div class="dow">{{ $day ?? ''}}</div>
										<div class="dod">{{ $week ?? ''}}</div>
									</div>
								</div>
								<div class="flex-grow-1">
									<a href="{{ route('management-location-task-details', ['task_id'=> $tasks->id ]) }}">
										<img src="{{url('uploads/task/' . $tasks->image  )}}" alt="Task" />
										<h6 class="location-observation-title">{{ $tasks->task_title ?? '' }}</h6>
										<p class="text-muted location-observation-title mb-0">Pending LOS to approve <img src="{{url('uploads/profile/' .$inspector->get_user->id .'/inspector/'. $inspector->get_user->profile_image)}}" class="rounded-profile-img" alt="Profile image">{{ $inspector->get_user->name ?? ''}}</p>
									</a>
								</div>
							</div>
							@endforeach
						@else
							<div class="text-center"><strong><h3>No record found</h3></strong></div>
						@endif
						{{--<div class="d-flex mb-3 task">
							<div class="date-box">
								<div class="date">
									<div class="day">FEB</div>
									<div class="dow">11</div>
									<div class="dod">FRI</div>
								</div>
							</div>
							<div class="flex-grow-1">
								<a href="javascript:void(0);">
									<img src="{{url('front-assets/static-image/2.jpg')}}" alt="Task" />
									<h6 class="location-observation-title">Respirator user has a training sticker on employee badge</h6>
									<p class="text-muted location-observation-title mb-0">Pending LOS to approve</p>
								</a>
							</div>
						</div>--}}
					</div>
				</div>
			</section>
		</div>
    </div>
@endsection 
@section('scripts')

@endsection

