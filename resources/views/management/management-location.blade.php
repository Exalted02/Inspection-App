@extends('layouts.app')
@section('content')
@php 
//echo "<pre>";print_r($task_details); die;
use Carbon\Carbon;
 $startDate = Carbon::now()->subWeeks(4)->startOfDay(); // 4 weeks ago
 $endDate = Carbon::now()->endOfDay(); //upto today
 
 $today = Carbon::today(); //today date
 $futureDate = Carbon::today()->addWeeks(4); // next 4 weeks

 $loc_tot_no_of_obs = 0;
 $time_to_close_obs = 0;
 
 // no. of inspection 
 
  $allTaskLocationWise  = App\Models\Task_lists::where('location_id', $location_id)->pluck('id')->toArray();
  //echo "<pre>";print_r($allTaskLocationWise);die;
  $count_inspection = App\Models\Task_list_subcategories::whereIn('task_list_id', $allTaskLocationWise)->whereBetween('created_at', [$startDate, $endDate])->count();
  $tot_inspection = ceil($count_inspection / 4);
  
  $taskLocation = App\Models\Task_lists::where('location_id', $location_id)->pluck('id')->toArray();
		
		
		
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
		$no_of_repeated_obs = ceil($repeated_obs_count / 4);
		
		//------- for time to close observation count ---
		 
		$lo_direct_approve = App\Models\Task_list_corrective_action::whereIn('task_list_id', $taskLocation)->where('lo_direct_approve', 1)->whereBetween('lo_completed_by', [$today, $futureDate])->count();
		
		$lo_no_direct_approve = App\Models\Task_list_corrective_action::whereIn('task_list_id', $taskLocation)->where('lo_direct_approve', 0)->whereBetween('lo_completed_by', [$today, $futureDate])->count();
		$close_obs = $lo_direct_approve + $lo_no_direct_approve;
		
		$time_to_close_obs = $time_to_close_obs + $close_obs;
@endphp
    <div class="container location-details">
		<div class="d-flex align-items-center location-header mb-3">
			<img src="{{url('uploads/location/' . $location_details->image ?? '')}}" alt="Location" />
			<div>
				<div class="title">{{ $location_details->location_name ?? ''}}</div>
				<small class="text-muted"><i class="fa fa-location-dot mr-5px"></i>{{ $location_details->street ?? ''}}, {{ $location_details->zipcode ?? ''}}</small>
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
									<div class="small-card-counter">{{ $tot_inspection }}</div>
									<div class="small-card-counter-title">WEEKLY</div>
								</div>
							</div>
							<div class="col-md-6 col-sm-6 col-xs-6 small-card-even">
								<div class="bg small-card">
									<div class="small-card-title">No. of observations</div>
									<div class="small-card-counter">{{ $no_of_obs ?? '' }}</div>
									<div class="small-card-counter-title">WEEKLY</div>
								</div>
							</div>
							<div class="col-md-6 col-sm-6 col-xs-6 small-card-odd">
								<div class="bg small-card">
									<div class="small-card-title">Time to close observation</div>
									<div class="small-card-counter">{{ $close_obs }}</div>
									<div class="small-card-counter-title">DAYS</div>
								</div>
							</div>
							<div class="col-md-6 col-sm-6 col-xs-6 small-card-even">
								<div class="bg small-card">
									<div class="small-card-title">Repeated observations found</div>
									<div class="small-card-counter">{{ $no_of_repeated_obs }}</div>
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

