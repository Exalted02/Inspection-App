@extends('layouts.app')
@section('content')
@php 
 use Carbon\Carbon;
 $startDate = Carbon::now()->subWeeks(4)->startOfDay(); // 4 weeks ago
 $endDate = Carbon::now()->endOfDay(); //upto today
 
 //echo "<pre>";print_r($locations);die;
 //echo "<pre>";print_r($userLocationArr);die;
 $allTaskLocationWise  = App\Models\Task_lists::whereIn('location_id', $userLocationArr)->pluck('id')->toArray();
 //echo "<pre>";print_r($allTaskLocationWise);die;
 
 //-- total inspection
 $count_inspection = App\Models\Task_list_subcategories::whereIn('task_list_id', $allTaskLocationWise)->whereBetween('created_at', [$startDate, $endDate])->count();
 $tot_inspection = ceil($count_inspection / 4);
 //--- ---- 
 
 //$allTaskId = App\Models\Task_list_subcategories::whereIn('task_list_id', $allTaskLocationWise)->pluck('task_list_id')->toArray();
 //echo "<pre>";print_r($allTaskId);die;
 
@endphp
    <div class="container">
		<h2 class="page-title">Welcome to your overview</h2>
		<div class="page-subtitle">Check out how your factory is performing</div>
		<div class="pt-2 pb-2">
			<div class="row ">
				<div class="col-md-4 col-sm-4 col-xs-4 small-card-first">
					<div class="bg small-card">
						<div class="small-card-title">No. of inspections</div>
						<div class="small-card-counter">{{ $tot_inspection ?? ''}}</div>
						<div class="small-card-counter-title">WEEKLY</div>
					</div>
				</div>
				<div class="col-md-4 col-sm-4 col-xs-4 small-card-second">
					<div class="bg small-card">
						<div class="small-card-title">No. of observations</div>
						<div class="small-card-counter">8</div>
						<div class="small-card-counter-title">WEEKLY</div>
					</div>
				</div>
				<div class="col-md-4 col-sm-4 col-xs-4 small-card-third">
					<div class="bg small-card">
						<div class="small-card-title">Time to close observation</div>
						<div class="small-card-counter">2</div>
						<div class="small-card-counter-title">DAYS</div>
					</div>
				</div>
			</div>
		</div>
    </div>
	@foreach($locations as $location)
	@php 
		$taskLocation = App\Models\Task_lists::where('location_id', $location->id)->pluck('id')->toArray();
		
		$allTaskCompleted = App\Models\Task_list_subcategories::whereIn('task_list_id', $taskLocation)->pluck('task_list_id')->toArray();
		//echo "<pre>";print_r($allTaskCompleted);
		$totalRejectChecklistCount = App\Models\Task_list_checklists::whereIn('task_list_id', $allTaskCompleted)->where('approve', 0)->whereBetween('created_at', [$startDate, $endDate])->count();
		
		$totalRejectSubChecklistCount = App\Models\Task_list_subchecklists::whereIn('task_list_id', $allTaskCompleted)->where('approve', 0)->whereBetween('created_at', [$startDate, $endDate])->count();
		
		$total_needed = $totalRejectChecklistCount + $totalRejectSubChecklistCount;
		
		$no_of_obs = ceil($total_needed / 4);
		//$no_of_obs = $total_needed;
	@endphp
	<div class="management-location-card pt-2 pb-2">
		<div class="container">
			<a href="{{ route('management-location', ['id' => $location->id]) }}"><div class="d-flex align-items-center location-header mb-3">
			{{--<img src="{{url('front-assets/static-image/5.jpg')}}" alt="Location">--}}
				<img src="{{url('uploads/location/' . $location->image ?? '')}}" alt="Location">
				<div>
					<div class="title">{{ $location->location_name ?? '' }}</div>
					<small class="text-muted">{{ $location->address ?? ''}}, {{ $location->zipcode ?? ''}}</small>
				</div>
			</div></a>
			<div class="row ">
				<div class="col-md-4 col-sm-4 col-xs-4 small-card-first">
					<div class="small-card">
						<div class="small-card-title">No. of observations</div>
						<div class="small-card-counter">{{ $no_of_obs ?? '' }}</div>
						<div class="small-card-counter-title">WEEKLY</div>
					</div>
				</div>
				<div class="col-md-4 col-sm-4 col-xs-4 small-card-second">
					<div class="small-card">
						<div class="small-card-title">Repeat observations</div>
						<div class="small-card-counter">8</div>
						<div class="small-card-counter-title">WEEKLY</div>
					</div>
				</div>
				<div class="col-md-4 col-sm-4 col-xs-4 small-card-third">
					<div class="small-card">
						<div class="small-card-title">Time to close observations</div>
						<div class="small-card-counter">6</div>
						<div class="small-card-counter-title">DAYS</div>
					</div>
				</div>
			</div>
		</div>
	</div>
	@endforeach
	{{--<div class="management-location-card pt-2 pb-2">
		<div class="container">
			<div class="d-flex align-items-center location-header mb-3">
				<img src="{{url('front-assets/static-image/4.jpg')}}" alt="Location">
				<div>
					<div class="title">Fernavale</div>
					<small class="text-muted">Mandai Road 23, 532012</small>
				</div>
			</div>
			<div class="row ">
				<div class="col-md-4 col-sm-4 col-xs-4 small-card-first">
					<div class="small-card">
						<div class="small-card-title">No. of observations</div>
						<div class="small-card-counter">4</div>
						<div class="small-card-counter-title">WEEKLY</div>
					</div>
				</div>
				<div class="col-md-4 col-sm-4 col-xs-4 small-card-second">
					<div class="small-card">
						<div class="small-card-title">Repeat observations</div>
						<div class="small-card-counter">8</div>
						<div class="small-card-counter-title">WEEKLY</div>
					</div>
				</div>
				<div class="col-md-4 col-sm-4 col-xs-4 small-card-third">
					<div class="small-card">
						<div class="small-card-title">Time to close observations</div>
						<div class="small-card-counter">6</div>
						<div class="small-card-counter-title">DAYS</div>
					</div>
				</div>
			</div>
		</div>
	</div>--}}
@endsection 
@section('scripts')

@endsection

