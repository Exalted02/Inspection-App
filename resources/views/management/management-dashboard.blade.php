@extends('layouts.app')
@section('content')
@php 
 use Carbon\Carbon;
 $startDate = Carbon::now()->subWeeks(4)->startOfDay(); // 4 weeks ago
 $endDate = Carbon::now()->endOfDay(); //upto today
 
 $today = Carbon::today(); //today date
 $futureDate = Carbon::today()->addWeeks(4); // next 4 weeks
 
 
 //echo "<pre>";print_r($locations);die;
 //echo "<pre>";print_r($userLocationArr);die;
 $allTaskLocationWise  = App\Models\Task_lists::whereIn('location_id', $userLocationArr)->pluck('id')->toArray();
 //echo "<pre>";print_r($allTaskLocationWise);die;
 
 //-- total inspection
 $count_inspection = App\Models\Task_list_subcategories::whereIn('task_list_id', $allTaskLocationWise)->whereBetween('created_at', [$startDate, $endDate])->count();
 //echo "<pre>";print_r($count_inspection);die;
 $tot_inspection = ceil($count_inspection / 4);
 //--- ---- 
 
 //$allTaskId = App\Models\Task_list_subcategories::whereIn('task_list_id', $allTaskLocationWise)->pluck('task_list_id')->toArray();
 //echo "<pre>";print_r($allTaskId);die;
 
 $loc_tot_no_of_obs = 0;
 $time_to_close_obs = 0;
@endphp
	<!-- =-=-=-=-=-=-= Breadcrumb =-=-=-=-=-=-= -->
	
	<div class="profile-card">
		<div class="profile-banner" style="background-image: url( '{{url('uploads/profile/' .$userdata->id .'/management/'. $userdata->background_image )}} ')"></div>
		<div class="profile-info">
			<img class="profile-avatar" src="{{ url('uploads/profile/' .$userdata->id .'/management/'. $userdata->profile_image)}}" alt="Profile Picture">
			<h2 class="profile-name">{{ $userdata->name ?? ''}}</h2>
			<p class="profile-description">
				{{--Management at {{ $userdata->get_company->company_name ?? '' }},<br> {{ $location_name ?? '' }}--}}
				Management at {{ $userdata->get_company->company_name ?? '' }}
					
			</p>
		</div>
	</div>
    <!-- =-=-=-=-=-=-= Breadcrumb End =-=-=-=-=-=-= --> 
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
						<div class="small-card-counter"><span id="tot_no_of_obs">0</span></div>
						<div class="small-card-counter-title">WEEKLY</div>
					</div>
				</div>
				<div class="col-md-4 col-sm-4 col-xs-4 small-card-third">
					<div class="bg small-card">
						<div class="small-card-title">Time to close observation</div>
						<div class="small-card-counter"><span id="tot_close_obs">0</span></div>
						<div class="small-card-counter-title">DAYS</div>
					</div>
				</div>
			</div>
		</div>
    </div>
	@foreach($locations as $location)
	@php 
		$taskLocation = App\Models\Task_lists::where('location_id', $location->id)->pluck('id')->toArray();
		
		//echo "<pre>";print_r($taskLocation);die;
		
		//$taskLocation = App\Models\Task_lists::where('location_id', $location->id)->get();
		
		//$allTaskCompleted = App\Models\Task_list_subcategories::whereIn('task_list_id', $taskLocation)->pluck('task_list_id')->toArray();
		//echo "<pre>";print_r($allTaskCompleted);
		
		$correctiveNeddedChecklistArray = [];
		$correctiveNeddedSubchecklistArray = [];
		
		
		foreach($taskLocation as $val)
		{
			$ifTaskRxists = App\Models\Task_list_subcategories::where('task_list_id', $val)->exists();
			if($ifTaskRxists)
			{
				// checklist and  respective files approve=0 
				
				
				$categoriesChecklistArr = App\Models\Task_list_subcategories::where('task_list_id', $val)->pluck('task_list_category_id')->toArray();
				
				//----------------------12-06-2025----------------------------
				// checklist and  respective files approve=1 
				$taskChklist = App\Models\Task_list_checklists::where('task_list_id', $val)->whereIn('category_id', $categoriesChecklistArr)->whereBetween('created_at', [$startDate, $endDate])->get();
				if($taskChklist->isNotEmpty())
				{
					foreach($taskChklist as $task)
					{
						
						$task_list_checklist_corrective_needed = App\Models\Task_list_corrective_action::where('task_list_id', $val)
						->where('checklist_id', $task->checklist_id)
						->first();
						if($task->approve == 0)
						{
							if(!$task_list_checklist_corrective_needed)
							{								
								
								$correctiveNeddedChecklistArray[] = [
										'type' => 'checklist',
										'task_id' => $val,
										'checklist_id' => $task->checklist_id,
										'rejected_region' => $task->rejected_region,
										'inspector_action' => '',
										'los_action' => '',
									];
							}
							else
							{
								$correctiveNeddedChecklistArray[] = [
										'type' => 'checklist',
										'task_id' => $val,
										'checklist_id' => $task->checklist_id,
										'rejected_region' => $task->rejected_region,
										'inspector_action'=> $task_list_checklist_corrective_needed->inspector_action,
										'los_action'=> $task_list_checklist_corrective_needed->los_action,
									];
								//------
							}
							
						}
					}
				}
				
				// subchecklist and respective files
				$taskSubChklist = App\Models\Task_list_subchecklists::where('task_list_id', $val)->whereIn('category_id', $categoriesChecklistArr)->whereBetween('created_at', [$startDate, $endDate])->get();
				if($taskSubChklist->isNotEmpty())
				{
					foreach($taskSubChklist as $subtask)
					{
						$task_list_subchecklist_corrective_needed = App\Models\Task_list_corrective_action::where('task_list_id', $val)
						->where('checklist_id', $subtask->task_list_checklist_id)
						->where('subchecklist_id', $subtask->subchecklist_id)
						->first();
						
						if($subtask->approve == 0)
						{
							if(!$task_list_subchecklist_corrective_needed)
							{
								
								$correctiveNeddedSubchecklistArray[] = [
											'type' => 'subchecklist',
											'task_id' => $val,
											'checklist_id' => $subtask->task_list_checklist_id,
											'subchecklist_id'=>$subtask->subchecklist_id,
											'rejected_region' => $subtask->rejected_region,
											'inspector_action' => '',
											'los_action' => '',
										];
							}
							else
							{
								// new implement 
								$correctiveNeddedSubchecklistArray[] = [
										'type' => 'subchecklist',
										'task_id' => $val,
										'checklist_id' => $subtask->task_list_checklist_id,
										'subchecklist_id'=>$subtask->subchecklist_id,
										'rejected_region' => $subtask->rejected_region,
										'inspector_action'=> $task_list_subchecklist_corrective_needed->inspector_action,
										'los_action'=> $task_list_subchecklist_corrective_needed->los_action,
									];
							}
						}
						
					}
					
				}
			}
		}
		
		$countNedded = 0;
		$correctiveNeeded = array_merge($correctiveNeddedChecklistArray, $correctiveNeddedSubchecklistArray);
		//echo "<pre>";print_r($correctiveNeeded); die;
		foreach($correctiveNeeded as $result)
		{
			if(($result['inspector_action']=='' && $result['inspector_action']=='') || ($result['inspector_action']== 2 && $result['inspector_action']==2))
			{
				$countNedded++;
			}
		}
		
		
		$no_of_obs = ceil($countNedded / 4);
		//$no_of_obs = $countNedded;
		$loc_tot_no_of_obs = $loc_tot_no_of_obs + $no_of_obs;
		
		//------- for rejected repeated count ---
		$repeated_obs_count = App\Models\Task_list_corrective_action::whereIn('task_list_id', $taskLocation)->where('rejected_repeated', 1)->whereBetween('created_at', [$startDate, $endDate])->count();
		//echo $repeated_obs_count;die;
		$no_of_repeated_obs = ceil($repeated_obs_count / 4);
		
		//------- for time to close observation count ---
		 
		$lo_direct_approve = App\Models\Task_list_corrective_action::whereIn('task_list_id', $taskLocation)->where('lo_direct_approve', 1)->whereBetween('lo_completed_by', [$today, $futureDate])->count();
		
		$lo_no_direct_approve = App\Models\Task_list_corrective_action::whereIn('task_list_id', $taskLocation)->where('lo_direct_approve', 0)->whereBetween('lo_completed_by', [$today, $futureDate])->count();
		$close_obs = $lo_direct_approve + $lo_no_direct_approve;
		
		$time_to_close_obs = $time_to_close_obs + $close_obs;
		
		
	@endphp
	<div class="management-location-card pt-2 pb-2">
		<div class="container">
			<a href="{{ route('management-location', ['id' => $location->id]) }}"><div class="d-flex align-items-center location-header mb-3">
			{{--<img src="{{url('front-assets/static-image/5.jpg')}}" alt="Location">--}}
				<img src="{{url('uploads/location/' . $location->image ?? '')}}" alt="Location">
				<div>
					<div class="title">{{ $location->location_name ?? '' }}</div>
					{{--<small class="text-muted">{{ !empty($location->street) ?  $location->street.' ,':''}}  {{ $location->zipcode ?? ''}}</small>--}}
					<small class="text-muted"><i class="fa fa-location-dot"></i>@if(!empty($location->unit_floor))
					{{ $location->unit_floor ?? ''}}</br>
					@endif
					{{ $location->street ?? ''}}</br>
					{{ $location->zipcode ?? ''}}
					</small>
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
						<div class="small-card-counter">{{ $no_of_repeated_obs }}</div>
						<div class="small-card-counter-title">WEEKLY</div>
					</div>
				</div>
				<div class="col-md-4 col-sm-4 col-xs-4 small-card-third">
					<div class="small-card">
						<div class="small-card-title">Time to close observations</div>
						<div class="small-card-counter">{{ $close_obs }}</div>
						<div class="small-card-counter-title">DAYS</div>
					</div>
				</div>
			</div>
		</div>
	</div>
	@endforeach
	<input type="hidden" id="loc_tot_no_of_obs" value="{{ $loc_tot_no_of_obs ?? ''}}">
	<input type="hidden" id="time_to_close_obs" value="{{ $time_to_close_obs ?? ''}}">
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
<script>
$(document).ready(function() {
	var tot_obs = $('#loc_tot_no_of_obs').val();
	var close_obs = $('#time_to_close_obs').val();
	$('#tot_no_of_obs').text(tot_obs);
	$('#tot_close_obs').text(close_obs);
});
</script>
@endsection

