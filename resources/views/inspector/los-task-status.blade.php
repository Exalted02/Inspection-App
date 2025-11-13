@extends('layouts.app')
@section('content')
@php 
//echo "<pre>";print_r($categoryData);die;
//echo "<pre>";print_r($correctiveAction);die;
//echo "<pre>";print_r($correctiveNeddedArray);die;
//echo "<pre>";print_r($approvedCompletedArray);die;
$location_name = App\Models\Manage_location::where('id', $location_id)->first()->location_name;
use Carbon\Carbon;
$taskData  = App\Models\Task_lists::where('id', $task_id)->first();
$location_details = $taskData ? $taskData->location_details : '';
//echo $task_id; die;
$j = 0;
$k = 0;
$l = 0;
$m = 0;


/*$countNedded = 0;
$countAction = 0;
$countPlan = 0;
$countCompleted = 0;
foreach($correctiveNeeded as $result)
{
	if(($result['inspector_action']=='' && $result['los_action']=='') || ($result['inspector_action']== 2 && $result['los_action']==2))
	{
		$countNedded++;
	}
}

foreach($correctiveAction as $result)
{
	if($result['lo_direct_approve'] == 1 && ($result['inspector_action'] == 0 || $result['los_action'] == 0))
	{
		$countAction++;
	}
	
	if($result['lo_direct_approve'] == 0 && ($result['inspector_action'] == 0 || $result['los_action'] == 0))
	{
		$countPlan++;
	}
}

foreach($approvedCompleted as $result)
{
	if($result['inspector_action'] == 1 && $result['los_action'] == 1)
	{
		$countCompleted++;
	}
}

//$correctiveNeededArray = [];
$correctiveActionArray = [];
$correctivePlanArray = [];
$approvedCompletedArray = [];

//echo "<pre>";print_r($correctiveNeeded);die;
//$correctiveNeededArray = array_slice($correctiveNeeded, 0, config('custom.LOAD_MORE_LIST_SHOW'));


foreach($correctiveAction as $action)
{
	if($action['lo_direct_approve'] == 1 && ($action['inspector_action'] == 0 || $action['los_action'] == 0))
	{
		$correctiveActionArray[] = [
			'type' =>  $action['type'],
			'task_id' =>  $action['task_id'],
			'checklist_id' =>  $action['checklist_id'],
			'subchecklist_id' =>  $action['subchecklist_id'],
			'rejected_region' =>  $action['rejected_region'],
			'inspector_action' =>  $action['inspector_action'],
			'los_action' =>  $action['los_action'],
			'second_checked' =>  $action['second_checked'],
			'lo_direct_approve' =>  $action['lo_direct_approve'],
			'image' =>  $action['image']
		];
	}
	
	if($action['lo_direct_approve'] == 0 && ($action['inspector_action'] == 0 || $action['los_action'] == 0))
	{
		$correctivePlanArray[] = [
			'type' =>  $action['type'],
			'task_id' =>  $action['task_id'],
			'checklist_id' =>  $action['checklist_id'],
			'subchecklist_id' =>  $action['subchecklist_id'],
			'rejected_region' =>  $action['rejected_region'],
			'inspector_action' =>  $action['inspector_action'],
			'los_action' =>  $action['los_action'],
			'second_checked' =>  $action['second_checked'],
			'lo_direct_approve' =>  $action['lo_direct_approve'],
			'image' =>  $action['image']
		];
	}
	
}
$correctiveActionArray = array_slice($correctiveActionArray, 0, config('custom.LOAD_MORE_LIST_SHOW'));
$correctivePlanArray = array_slice($correctivePlanArray, 0, config('custom.LOAD_MORE_LIST_SHOW'));

//echo "<pre>";print_r($approvedCompleted);die;
usort($approvedCompleted, function ($a, $b) {
	return strtotime($b['updated_at']) <=> strtotime($a['updated_at']);
});

foreach($approvedCompleted as $appr)
{
	if($appr['inspector_action'] == 1 && $appr['los_action'] == 1)
	{
		if($appr['type'] == 'checklist')
		{
			if(isset($appr['image']))
			{
				$approvedCompletedArray[] = [
					'type' => $appr['type'],
					'task_id' => $appr['task_id'],
					'checklist_id' => $appr['checklist_id'],
					'rejected_region' => $appr['rejected_region'],
					'image' => $appr['image'],
					'inspector_action' => $appr['inspector_action'],
					'los_action' => $appr['los_action']
				];
			}
			else{
				$approvedCompletedArray[] = [
					'type' => $appr['type'],
					'task_id' => $appr['task_id'],
					'checklist_id' => $appr['checklist_id'],
					'rejected_region' => $appr['rejected_region'],
					'inspector_action' => $appr['inspector_action'],
					'los_action' => $appr['los_action']
				];
			}
		}
		else{
			if(isset($appr['image']))
			{
				$approvedCompletedArray[] = [
					'type' => $appr['type'],
					'task_id' => $appr['task_id'],
					'checklist_id' => $appr['checklist_id'],
					'subchecklist_id' => $appr['subchecklist_id'],
					'rejected_region' => $appr['rejected_region'],
					'image' => $appr['image'],
					'inspector_action' => $appr['inspector_action'],
					'los_action' => $appr['los_action']
				];
			}
			else{
				$approvedCompletedArray[] = [
					'type' => $appr['type'],
					'task_id' => $appr['task_id'],
					'checklist_id' => $appr['checklist_id'],
					'subchecklist_id' => $appr['subchecklist_id'],
					'rejected_region' => $appr['rejected_region'],
					'inspector_action' => $appr['inspector_action'],
					'los_action' => $appr['los_action']
				];
			}
		}
	}
}

$approvedCompletedArray = array_slice($approvedCompletedArray, 0, config('custom.LOAD_MORE_LIST_SHOW'));*/

$action_show = config('custom.LOAD_MORE_LIST_SHOW');
$totalNeeded = $countNedded;
$totalAction = $countAction;
$totalPlan = $countPlan;
$totalapprcompleted = $countCompleted;

//echo "<pre>";print_r($approvedCompletedArray);

$tabname = Session::get('tabname');
if($tabname == 'los-action-plan-needed')
{
	$dashboard_notification  = App\Models\Dashboard_notification::where('user_type', 3)->where('location_id', $location_id)->where('user_id', auth()->user()->id)->update(['total_action_plan'=> 0, 'read_action_plan'=> 0]);
	
	App\Models\Dashboard_notification::where('user_type', 3)->where('location_id', $location_id)->where('user_id', auth()->user()->id)->update(['pending_closure'=> 0]);
	
	Session::forget('tabname');
}

@endphp
	<!-- =-=-=-=-=-=-= Breadcrumb =-=-=-=-=-=-= -->
	
		<div class="profile-card">
		<div class="profile-banner" style="background-image: url( '{{url('uploads/profile/' .$userdata->id .'/locationownersupervisor/'. $userdata->background_image )}} ')"></div>
			<div class="profile-info">
				<img class="profile-avatar" src="{{ url('uploads/profile/' .$userdata->id .'/locationownersupervisor/'. $userdata->profile_image)}}" alt="Profile Picture">
				<h2 class="profile-name">{{ $userdata->name ?? ''}}</h2>
				<p class="profile-description">
					Location owner supervisor at {{ $userdata->get_company->company_name ?? '' }},<br>
						{{ $location_name ?? '' }}
						
				</p>
			</div>
		</div>
    <!-- =-=-=-=-=-=-= Breadcrumb End =-=-=-=-=-=-= --> 
    <!-- =-=-=-=-=-=-= Main Content Area =-=-=-=-=-=-= -->
	<div class="container checklist">
		{{--<h2 class="checklist-title">{{ $task_name ?? '' }}</h2>--}}
		@if(auth()->user()->user_type == 1)
		{{--<div class="location-section">
			<div class="location-label">Location details</div>
			<div class="location-input" id="displayBox">
			{{ $location_details ?? 'Tap to add address' }}
				<span><i class="fa-solid fa-pen"></i></span>
			</div>
			<span id="successMessage" class="task-location-msg">
				Details saved successfully!
			</span>
			
				
			<div class="location-edit" id="editBox">
				<input type="text" id="addressInput" placeholder="Add location" value="{{ $location_details ?? ''}}"/>
				<button id="doneBtn" class="donesubmit">Done</button>
			</div>
			<input type="hidden" id="location_id" value="{{ $location_id ?? ''}}">
				
			<input type="hidden" id="task_id" value="{{ $task_id ?? '' }}">
			<input type="hidden" id="taskid">
		</div>--}}
		@endif
		<input type="hidden" id="location_id" value="{{ $location_id ?? ''}}">
		<input type="hidden" id="task_id" value="{{ $task_id ?? '' }}">
		<input type="hidden" id="taskid">
		<!-- =-=-=-=-=-=-= Breadcrumb End =-=-=-=-=-=-= --> 
		<!-- =-=-=-=-=-=-= Main Content Area =-=-=-=-=-=-= -->
		<div class="main-content-area clearfix corrective-checked">
			<section class="custom-padding1">
				<div class="container1">
					<div class="custom-tab">
						<!-- Tabs -->
						{{--<div class="tab-scroll-container">
						<div class="scroll-arrow left-arrow" id="scrollLeft"><i class="fa fa-chevron-left"></i></div>
							<div class="tab-scroll-wrapper" id="tabScrollWrapper">--}}
								<ul class="nav nav-tabs custom-tab-style" role="tablist">
								@if(auth()->user()->user_type == 1)
								{{--<li role="presentation" class=""><a class="unCompletetab" href="#uncomplete_tab" aria-controls="uncomplete_tab" role="tab"><span class="counter_s"></span>Uncompleted</a></li>
									<li role="presentation"><a class="correctiveChecked" href="#corrective_checked_tab" aria-controls="reject_tab" role="tab"> Corrective checked</a></li>--}}
								@endif
								@if(auth()->user()->user_type == 3)
								{{--<li role="presentation"><a class="correctiveChecked" href="#corrective_checked_tab" aria-controls="reject_tab" role="tab"> Corrective checked</a></li>--}}
								@endif
								{{--<li role="presentation"><a class="finalChecked" href="#process_final_checked_tab" aria-controls="reject_tab" role="tab">Final checks</a></li> 
									<li role="presentation"><a class="approvedFinalChecked" href="#approved_final_checked_tab" aria-controls="reject_tab" role="tab">Approved final checked</a></li>--}}
									@if(auth()->user()->user_type == 1)
									{{--<li role="presentation" class=""><a class="unCompletetab" href="#uncomplete_tab" aria-controls="uncomplete_tab" role="tab"><span class="counter_s"></span>Uncompleted</a></li>--}}
									@endif
									<li role="presentation"><a class="correctiveNeeded" href="#corrective_needed_tab" aria-controls="reject_tab" role="tab"><span class="counter-grey">{{ $countNedded }}</span> Corrective Needed</a></li>
									<li role="presentation"><a class="correctiveChecked" href="#corrective_checked_tab" aria-controls="reject_tab" role="tab"><span class="counter-red">{{ $countAction }}</span> Corrective Action</a></li>
									<li role="presentation"><a class="finalChecked" href="#process_final_checked_tab" aria-controls="reject_tab" role="tab"><span class="counter-red">{{ $countPlan}}</span> Corrective Plan</a></li> 
									<li role="presentation"><a class="approvedFinalChecked" href="#approved_final_checked_tab" aria-controls="reject_tab" role="tab"><span class="counter-grey">{{ $countCompleted }}</span> Completed/Approved</a></li>									
								</ul>
							{{--</div>
							<div class="scroll-arrow right-arrow" id="scrollRight"><i class="fa fa-chevron-right"></i></div>
						</div>--}}
						<!-- Tab panes -->
						<div class="tab-content">
						@if(auth()->user()->user_type == 1)
						{{--<div role="tabpanel" class="tab-pane" id="uncomplete_tab">
							@if($categoryData->isNotEmpty())
								@foreach($categoryData as $categories)
								@php 
									$tot_checklist = App\Models\Checklist::where('category_id', $categories->id)->count();
									$tot_checklist_completed = App\Models\Task_list_checklists::where('task_list_id',$task_id)->where('category_id', $categories->id)->count();
									$tot_subchecklist_completed = App\Models\Task_list_subchecklists::where('task_list_id', $task_id)->where('category_id', $categories->id)->distinct('task_list_checklist_id')->count();
									$tot_completed_task = $tot_checklist_completed+$tot_subchecklist_completed;
									
									$ifSubmitted = App\Models\Task_list_subcategories::where('task_list_id', $task_id)->where('task_list_category_id', $categories->id)->exists();

								@endphp
								
									@if(!$ifSubmitted)
									<div class="checklist-item">
										<div class="text">
											<div class="title">{{ $categories->name ?? ''}}</div>
											<div class="subtitle">Completed {{ $tot_completed_task ?? ''}} of {{ $tot_checklist ?? ''}}</div>
										</div>
										
										<a href="#" class="chk-task-id" data-cat="{{ $categories->id ?? ''}}" data-location="{{ $location_id ?? ''}}" data-taskid="{{ $task_id }}"><div class="arrow"><i class="fa-solid fa-arrow-right"></i></div></a>
									</div>
									@endif
								@endforeach
							@else	
								<div class="text-center"><strong><h3>No record founds</h3></strong></div>
							@endif
							</div>--}}
						@endif
							<div role="tabpanel" class="tab-pane" id="reject_tab">
								Not have any data
							</div>
							<div class="tab-pane corrective_needed_tab" id="corrective_needed_tab">
							<span id="neddedDiv">
								@foreach($correctiveNeddedArray as $result)
									@php 
										$j++;
										$arrSubchecklist = [];
										$checklistData = App\Models\Checklist::where('id', $result['checklist_id'])->first();
										
										/*$subchecklistData = App\Models\Subchecklist::where('id', $result['checklist_id'])->first();*/
										
										/*$checklistName = $checklistData ? $checklistData->name : ($subchecklistData ?  $subchecklistData->name : '');*/
										
										$checklistName = $checklistData ? $checklistData->name : '';
										
										$rejectedRegionData = $result['type'] == 'checklist'
										? App\Models\Task_list_checklists::where('task_list_id',$result['task_id'])->where('checklist_id', $result['checklist_id'])->first()
										: App\Models\Task_list_subchecklists::where('task_list_id',$result['task_id'])->where('task_list_checklist_id', $result['checklist_id'])->where('subchecklist_id', $result['subchecklist_id'])->first();

										if($result['image'] != '')
										{
											$images = $result['type'] == 'checklist' ?  url('uploads/reject-files/' . $result['image']) :  url('uploads/reject-files/subchecklist/' . $result['image']);
										}
										else{
											$images = url('images/noimages/corrective-needed.png');
										}
										
										
										if($result['type'] == 'subchecklist')
										{
											$subchecklistData = App\Models\Task_list_subchecklists::where('task_list_id',$result['task_id'])->where('task_list_checklist_id',$result['checklist_id'])->where('subchecklist_id',$result['subchecklist_id'])->where('approve',0)->first();
											if($subchecklistData)
											{
												//foreach($subchecklistData as $subcheck)
												//{
													//$arrSubchecklist = [];
													$subchecklistName = App\Models\Subchecklist::where('id', $subchecklistData->subchecklist_id)->first();
													
													$filedata = App\Models\Task_list_subchecklist_rejected_files::where('task_list_subchecklist_id', $subchecklistData->id)->first();
													
													$images = $filedata && $filedata->file != '' ? url('uploads/reject-files/subchecklist/' . $filedata->file) : url('images/noimages/corrective-needed.png');
													
													$arrSubchecklist[] = [
														'id' => $subchecklistData->id,
														'name' => $subchecklistName ? $subchecklistName->name : '',
														'image' => $images,
														'subchecklist_id' => $subchecklistData->subchecklist_id,
													];
												//}
											}
										}
										
									@endphp
									@if(!empty($arrSubchecklist))
										@foreach($arrSubchecklist as $val)
											@php 
												$url = $val['image'] ?? '';
												$extension = pathinfo($url, PATHINFO_EXTENSION);
												$extension = strtolower($extension);
											@endphp
									<div class="d-flex mb-3 task">
										<div class="date-box">
										@if (in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp']))
											<img src="{{ $val['image'] }}">
										@elseif(in_array($extension, ['mp4', 'webm', 'ogg']))
											<video controls src="{{ $val['image'] }}"></video>
										@endif
										</div>
										<div class="flex-grow-1">
										{{--<a href="{{ route('inspector-subchecklist-question-reply',['location_id'=>$location_id,'task_id'=>$result['task_id'], 'checklist_id'=> $result['checklist_id'],'subchecklist_id'=>$val['subchecklist_id'],'type' => $result['type'],'tab'=>'corrective-action']) }}">--}}
												<a href="{{ route('inspector-subchecklist-question-reply',['location_id'=>$location_id,'task_id'=>$result['task_id'], 'checklist_id'=> $result['checklist_id'],'subchecklist_id'=>$val['subchecklist_id'],'type' => $result['type'],'tab'=>'corrective-action']) }}">
											<h6>{{ $checklistName ?? '' }} 
											@if($val!='')
												-> {{$val['name'] ?? ''}}
											@endif
											</h6>
												<p class="text-muted mb-0">
												{{ \Illuminate\Support\Str::words($rejectedRegionData->rejected_region ?? '', 30, '...') }}
												</p>
												@if($rejectedRegionData)	
												<p class="text-muted mb-0">
												<i class="fa fa-clock"></i> {{ change_date_format($rejectedRegionData->created_at, 'Y-m-d H:i:s', 'd M Y, h.i A') }}
												</p>
												@endif
												<p class="text-muted mb-0" >
												<i class="fa fa-map-marker"></i> {{ $location_name ?? ''}}
												@if($result['rejected_status']==1)
												<button type="button" class="btn btn-outline-success status-outline-common-btn ml-10px"  style="border-color: red; color: red;">Rejected Inspector</button>
												@elseif($result['rejected_status']==2)
												<button type="button" class="btn btn-outline-success status-outline-common-btn ml-10px"  style="border-color: red; color: red;">Rejected LOS</button>
												@else
													<button type="button" class="btn btn-warning status-outline-common-btn ml-10px" style="border-color: #ffc107; color: #ffc107;">Pending</button>
												@endif
												</p>
											</a>
										</div>
									</div>
									@endforeach
									
									@else 
										@php 
											$url = $images ?? '';
											$extension = pathinfo($url, PATHINFO_EXTENSION);
											$extension = strtolower($extension);
										@endphp
										<div class="d-flex mb-3 task">
										<div class="date-box">
										@if (in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp']))
											<img src="{{ $images }}">
										@elseif(in_array($extension, ['mp4', 'webm', 'ogg']))
											<video controls src="{{ $images }}"></video>
										@endif
										</div>
										<div class="flex-grow-1">
											<a href="{{ route('inspector-checklist-question-reply',['location_id'=>$location_id,'task_id'=>$result['task_id'], 'checklist_id'=> $result['checklist_id'],'type' => $result['type'],'tab'=>'corrective-action']) }}">
											<h6>{{ $checklistName ?? '' }} 
											</h6>
												<p class="text-muted mb-0">
												{{ \Illuminate\Support\Str::words($rejectedRegionData->rejected_region ?? '', 30, '...') }}
												</p>
												@if($rejectedRegionData)
												<p class="text-muted mb-0">
												<i class="fa fa-clock"></i> {{ change_date_format($rejectedRegionData->created_at, 'Y-m-d H:i:s', 'd M Y, h.i A') }}
												</p>
												@endif
												<p class="text-muted mb-0">
												<i class="fa fa-map-marker"></i> {{ $location_name ?? ''}}
												@if($result['rejected_status']==1)
												<button type="button" class="btn btn-outline-success status-outline-common-btn ml-10px"  style="border-color: red; color: red;">Rejected Inspector</button>
												@elseif($result['rejected_status']==2)
												<button type="button" class="btn btn-outline-success status-outline-common-btn ml-10px"  style="border-color: red; color: red;">Rejected LOS</button>
												@else
													<button type="button" class="btn btn-warning status-outline-common-btn ml-10px" style="border-color: #ffc107; color: #ffc107;">Pending</button>
												@endif
												
												
												</p>
											</a>
										</div>
									</div>
									@endif
								
								<hr class="horizontal-line-list-page">
								@endforeach
							</span>
							<div class="load-more-needed" id="showloadneeded">Load more</div>
								<input type="hidden" value="{{ $moreloadneeded ?? '' }}" id="moreloadneeded">
							</div>
							<div class="tab-pane corrective_checked_tab" id="corrective_checked_tab">
							<span id="actionDiv">
								@foreach($correctiveActionArray as $result)
									@php 
										$k++;
										$arrSubchecklist = [];
										$checklistData = App\Models\Checklist::where('id', $result['checklist_id'])->first();
										
										/*$subchecklistData = App\Models\Subchecklist::where('id', $result['checklist_id'])->first();*/
										
										/*$checklistName = $checklistData ? $checklistData->name : ($subchecklistData ?  $subchecklistData->name : '');*/
										
										$checklistName = $checklistData ? $checklistData->name : '';
										
										$rejectedRegionData = $result['type'] == 'checklist'
										? App\Models\Task_list_checklists::where('task_list_id',$result['task_id'])->where('checklist_id', $result['checklist_id'])->first()
										: App\Models\Task_list_subchecklists::where('task_list_id',$result['task_id'])->where('task_list_checklist_id', $result['checklist_id'])->where('subchecklist_id', $result['subchecklist_id'])->first();

										if($result['image'] != '')
										{
											$images = $result['type'] == 'checklist' ?  url('uploads/reject-files/' . $result['image']) :  url('uploads/reject-files/subchecklist/' . $result['image']);
										}
										else{
											$images = url('images/noimages/corrective-action.png');
										}
										
										
										if($result['type'] == 'subchecklist')
										{
											$subchecklistData = App\Models\Task_list_subchecklists::where('task_list_id',$result['task_id'])->where('task_list_checklist_id',$result['checklist_id'])->where('subchecklist_id',$result['subchecklist_id'])->where('approve',0)->first();
											if($subchecklistData)
											{
												//foreach($subchecklistData as $subcheck)
												//{
													//$arrSubchecklist = [];
													$subchecklistName = App\Models\Subchecklist::where('id', $subchecklistData->subchecklist_id)->first();
													
													$filedata = App\Models\Task_list_subchecklist_rejected_files::where('task_list_subchecklist_id', $subchecklistData->id)->first();
													
													$images = $filedata && $filedata->file != '' ? url('uploads/reject-files/subchecklist/' . $filedata->file) : url('images/noimages/corrective-action.png');
													
													$arrSubchecklist[] = [
														'id' => $subchecklistData->id,
														'name' => $subchecklistName ? $subchecklistName->name : '',
														'image' => $images,
														'subchecklist_id' => $subchecklistData->subchecklist_id,
													];
												//}
											}
										}
										
									@endphp
									@if(!empty($arrSubchecklist))
										@foreach($arrSubchecklist as $val)
									
										@php 
											$url = $val['image'] ?? '';
											$extension = pathinfo($url, PATHINFO_EXTENSION);
											$extension = strtolower($extension);
										@endphp
									<div class="d-flex mb-3 task">
										<div class="date-box">
											@if (in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp']))
											<img src="{{ $val['image'] }}">
											@elseif(in_array($extension, ['mp4', 'webm', 'ogg']))
											<video controls src="{{ $val['image'] }}"></video>
											@endif
										</div>
										<div class="flex-grow-1">
											@if($result['second_checked'] == '')
											<a href="{{ route('inspector-subchecklist-question-reply',['location_id'=>$location_id,'task_id'=>$result['task_id'], 'checklist_id'=> $result['checklist_id'],'subchecklist_id'=>$val['subchecklist_id'],'type' => $result['type'],'tab'=>'corrective-action']) }}">
											@else
											<a href="{{ route('inspector-subchecklist-second-approve-by-lo',['location_id'=>$location_id,'task_id'=>$result['task_id'], 'checklist_id'=> $result['checklist_id'],'subchecklist_id'=>$val['subchecklist_id'],'type' => $result['type'],'tab'=>'corrective-action']) }}">
											@endif
											<h6>{{ $checklistName ?? '' }} 
											@if($val!='')
												-> {{$val['name'] ?? ''}}
											@endif
											</h6>
												<p class="text-muted mb-0">
												{{ \Illuminate\Support\Str::words($rejectedRegionData->rejected_region ?? '', 30, '...') }}
												</p>
												@if($rejectedRegionData)	
												<p class="text-muted mb-0">
												<i class="fa fa-clock"></i> {{ change_date_format($rejectedRegionData->created_at, 'Y-m-d H:i:s', 'd M Y, h.i A') }}
												</p>
												@endif
												<p class="text-muted mb-0" >
												<i class="fa fa-map-marker"></i> {{ $location_name ?? ''}}
												@if(auth()->user()->user_type == 1)
													@if($result['inspector_action'] == 1)
														<button type="button" class="btn btn-outline-success status-outline-common-btn ml-10px"  style="border-color: #198754; color: #198754;">Agree</button>
													@elseif($result['inspector_action'] == 0)
														<button type="button" class="btn btn-warning status-outline-common-btn ml-10px" style="border-color: #ffc107; color: #ffc107;">Pending</button>
													@endif
												@endif
												
												@if(auth()->user()->user_type == 3)
													@if($result['los_action'] == 1)
														<button type="button" class="btn btn-outline-success status-outline-common-btn ml-10px"  style="border-color: #198754; color: #198754;">Agree</button>
													@elseif($result['los_action'] == 0)
														<button type="button" class="btn btn-warning status-outline-common-btn ml-10px" style="border-color: #ffc107; color: #ffc107;">Pending</button>
													@endif
												@endif
												</p>
											</a>
										</div>
									</div>
									@endforeach
									
									@else
										@php 
											$url = $images ?? '';
											$extension = pathinfo($url, PATHINFO_EXTENSION);
											$extension = strtolower($extension);
										@endphp
										<div class="d-flex mb-3 task">
										<div class="date-box">
										@if (in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp']))
											<img src="{{ $images }}">
										@elseif(in_array($extension, ['mp4', 'webm', 'ogg']))
											<video controls src="{{ $images }}"></video>
										@endif
										</div>
										<div class="flex-grow-1">
											@if($result['second_checked'] == '')
											<a href="{{ route('inspector-checklist-question-reply',['location_id'=>$location_id,'task_id'=>$result['task_id'], 'checklist_id'=> $result['checklist_id'],'type' => $result['type'],'tab'=>'corrective-action']) }}">
											@else
											<a href="{{ route('inspector-checklist-second-approve-by-lo',['location_id'=>$location_id,'task_id'=>$result['task_id'], 'checklist_id'=> $result['checklist_id'],'type' => $result['type'],'tab'=>'corrective-action']) }}">
											@endif
											<h6>{{ $checklistName ?? '' }} 
											</h6>
												<p class="text-muted mb-0">
												{{ \Illuminate\Support\Str::words($rejectedRegionData->rejected_region ?? '', 30, '...') }}
												</p>
												@if($rejectedRegionData)
												<p class="text-muted mb-0">
												<i class="fa fa-clock"></i> {{ change_date_format($rejectedRegionData->created_at, 'Y-m-d H:i:s', 'd M Y, h.i A') }}
												</p>
												@endif
												<p class="text-muted mb-0">
												<i class="fa fa-map-marker"></i> {{ $location_name ?? ''}}
												@if(auth()->user()->user_type == 1)
													@if($result['inspector_action'] == 1)
														<button type="button" class="btn btn-outline-success status-outline-common-btn ml-10px"  style="border-color: #198754; color: #198754;">Agree</button>
													@elseif($result['inspector_action'] == 0)
														<button type="button" class="btn btn-warning status-outline-common-btn ml-10px" style="border-color: #ffc107; color: #ffc107;">Pending</button>
													@endif
												@endif
												
												@if(auth()->user()->user_type == 3)
													@if($result['los_action'] == 1)
														<button type="button" class="btn btn-outline-success status-outline-common-btn ml-10px"  style="border-color: #198754; color: #198754;">Agree</button>
													@elseif($result['los_action'] == 0)
														<button type="button" class="btn btn-warning status-outline-common-btn ml-10px" style="border-color: #ffc107; color: #ffc107;">Pending</button>
													@endif
												@endif
												</p>
											</a>
										</div>
									</div>
									@endif
								<hr class="horizontal-line-list-page">
								@endforeach
							</span>
							<div class="load-more-action" id="showloadaction">Load more</div>
								<input type="hidden" value="{{ $moreloadaction ?? '' }}" id="moreloadaction">
							</div>
							<div role="tabpanel" class="tab-pane" id="process_final_checked_tab">
							<span id="planDiv">
								@foreach($correctivePlanArray as $result)
									@php 
										$l++;
										$arrSubchecklist = [];
										$checklistData = App\Models\Checklist::where('id', $result['checklist_id'])->first();
										
										/*$subchecklistData = App\Models\Subchecklist::where('id', $result['checklist_id'])->first();*/
										
										/*$checklistName = $checklistData ? $checklistData->name : ($subchecklistData ?  $subchecklistData->name : '');*/
										
										$checklistName = $checklistData ? $checklistData->name : '';
										
										$rejectedRegionData = $result['type'] == 'checklist'
										? App\Models\Task_list_checklists::where('task_list_id',$result['task_id'])->where('checklist_id', $result['checklist_id'])->first()
										: App\Models\Task_list_subchecklists::where('task_list_id',$result['task_id'])->where('task_list_checklist_id', $result['checklist_id'])->where('subchecklist_id', $result['subchecklist_id'])->first();

										if($result['image'] != '')
										{
											$images = $result['type'] == 'checklist' ?  url('uploads/reject-files/' . $result['image']) :  url('uploads/reject-files/subchecklist/' . $result['image']);
										}
										else{
											$images = url('images/noimages/corrective-plan.png');
										}
										
										
										if($result['type'] == 'subchecklist')
										{
											$subchecklistData = App\Models\Task_list_subchecklists::where('task_list_id',$result['task_id'])->where('task_list_checklist_id',$result['checklist_id'])->where('subchecklist_id',$result['subchecklist_id'])->where('approve',0)->first();
											if($subchecklistData)
											{
												//foreach($subchecklistData as $subcheck)
												//{
													//$arrSubchecklist = [];
													$subchecklistName = App\Models\Subchecklist::where('id', $subchecklistData->subchecklist_id)->first();
													
													$filedata = App\Models\Task_list_subchecklist_rejected_files::where('task_list_subchecklist_id', $subchecklistData->id)->first();
													
													$images = $filedata && $filedata->file != '' ? url('uploads/reject-files/subchecklist/' . $filedata->file) : url('images/noimages/corrective-plan.png');
													
													$arrSubchecklist[] = [
														'id' => $subchecklistData->id,
														'name' => $subchecklistName ? $subchecklistName->name : '',
														'image' => $images,
														'subchecklist_id' => $subchecklistData->subchecklist_id,
													];
												//}
											}
										}
										
									@endphp
									@if(!empty($arrSubchecklist))
										@foreach($arrSubchecklist as $val)
											@php 
												$url = $val['image'] ?? '';
												$extension = pathinfo($url, PATHINFO_EXTENSION);
												$extension = strtolower($extension);
											@endphp
									<div class="d-flex mb-3 task">
										<div class="date-box">
										@if (in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp']))
											<img src="{{ $val['image'] }}">
										@elseif(in_array($extension, ['mp4', 'webm', 'ogg']))
											<video controls src="{{ $val['image'] }}"></video>
										@endif
										</div>
										<div class="flex-grow-1">
											@if($result['second_checked'] == '')
											<a href="{{ route('inspector-subchecklist-question-reply',['location_id'=>$location_id,'task_id'=>$result['task_id'], 'checklist_id'=> $result['checklist_id'],'subchecklist_id'=>$val['subchecklist_id'],'type' => $result['type'],'tab'=>'corrective-action']) }}">
											@else
											<a href="{{ route('inspector-subchecklist-second-approve-by-lo',['location_id'=>$location_id,'task_id'=>$result['task_id'], 'checklist_id'=> $result['checklist_id'],'subchecklist_id'=>$val['subchecklist_id'],'type' => $result['type'],'tab'=>'corrective-action']) }}">
											@endif
											<h6>{{ $checklistName ?? '' }} 
											@if($val!='')
												-> {{$val['name'] ?? ''}}
											@endif
											</h6>
												<p class="text-muted mb-0">
												{{ \Illuminate\Support\Str::words($rejectedRegionData->rejected_region ?? '', 30, '...') }}
												</p>
												@if($rejectedRegionData)	
												<p class="text-muted mb-0">
												<i class="fa fa-clock"></i> {{ change_date_format($rejectedRegionData->created_at, 'Y-m-d H:i:s', 'd M Y, h.i A') }}
												</p>
												@endif
												<p class="text-muted mb-0" >
												<i class="fa fa-map-marker"></i> {{ $location_name ?? ''}}
												@if(auth()->user()->user_type == 1)
													@if($result['inspector_action'] == 1)
														<button type="button" class="btn btn-outline-success status-outline-common-btn ml-10px"  style="border-color: #198754; color: #198754;">Agree</button>
													@elseif($result['inspector_action'] == 0)
														<button type="button" class="btn btn-warning status-outline-common-btn ml-10px" style="border-color: #ffc107; color: #ffc107;">Pending</button>
													@endif
												@endif
												
												@if(auth()->user()->user_type == 3)
													@if($result['los_action'] == 1)
														<button type="button" class="btn btn-outline-success status-outline-common-btn ml-10px"  style="border-color: #198754; color: #198754;">Agree</button>
													@elseif($result['los_action'] == 0)
														<button type="button" class="btn btn-warning status-outline-common-btn ml-10px" style="border-color: #ffc107; color: #ffc107;">Pending</button>
													@endif
												@endif
												</p>
											</a>
										</div>
									</div>
									@endforeach
									
									@else
										@php 
											$url = $images ?? '';
											$extension = pathinfo($url, PATHINFO_EXTENSION);
											$extension = strtolower($extension);
										@endphp
										<div class="d-flex mb-3 task">
										<div class="date-box">
										@if (in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp']))
											<img src="{{ $images }}">
										@elseif(in_array($extension, ['mp4', 'webm', 'ogg']))
											<video controls src="{{ $images }}"></video>
										@endif
										</div>
										<div class="flex-grow-1">
											@if($result['second_checked'] == '')
											<a href="{{ route('inspector-checklist-question-reply',['location_id'=>$location_id,'task_id'=>$result['task_id'], 'checklist_id'=> $result['checklist_id'],'type' => $result['type'],'tab'=>'corrective-action']) }}">
											@else
											<a href="{{ route('inspector-checklist-second-approve-by-lo',['location_id'=>$location_id,'task_id'=>$result['task_id'], 'checklist_id'=> $result['checklist_id'],'type' => $result['type'],'tab'=>'corrective-action']) }}">
											@endif
											<h6>{{ $checklistName ?? '' }} 
											</h6>
												<p class="text-muted mb-0">
												{{ \Illuminate\Support\Str::words($rejectedRegionData->rejected_region ?? '', 30, '...') }}
												</p>
												@if($rejectedRegionData)
												<p class="text-muted mb-0">
												<i class="fa fa-clock"></i> {{ change_date_format($rejectedRegionData->created_at, 'Y-m-d H:i:s', 'd M Y, h.i A') }}
												</p>
												@endif
												<p class="text-muted mb-0">
												<i class="fa fa-map-marker"></i> {{ $location_name ?? ''}}
												@if(auth()->user()->user_type == 1)
													@if($result['inspector_action'] == 1)
														<button type="button" class="btn btn-outline-success status-outline-common-btn ml-10px"  style="border-color: #198754; color: #198754;">Agree</button>
													@elseif($result['inspector_action'] == 0)
														<button type="button" class="btn btn-warning status-outline-common-btn ml-10px" style="border-color: #ffc107; color: #ffc107;">Pending</button>
													@endif
												@endif
												
												@if(auth()->user()->user_type == 3)
													@if($result['los_action'] == 1)
														<button type="button" class="btn btn-outline-success status-outline-common-btn ml-10px"  style="border-color: #198754; color: #198754;">Agree</button>
													@elseif($result['los_action'] == 0)
														<button type="button" class="btn btn-warning status-outline-common-btn ml-10px" style="border-color: #ffc107; color: #ffc107;">Pending</button>
													@endif
												@endif
												</p>
											</a>
										</div>
									</div>
									@endif
								<hr class="horizontal-line-list-page">
								@endforeach
								</span>
							
								<div class="load-more-plan" id="showloadplan">Load more</div>
								<input type="hidden" value="{{ $moreloadplan ?? '' }}" id="moreloadplan">
							</div>
							<div role="tabpanel" class="tab-pane" id="approved_final_checked_tab">
							<span id="appCompletedDiv">
							@foreach($approvedCompletedArray as $result)
									@php 
										$m++;
										$arrSubchecklist = [];
										$checklistData = App\Models\Checklist::where('id', $result['checklist_id'])->first();
										
										/*$subchecklistData = App\Models\Subchecklist::where('id', $result['checklist_id'])->first();*/
										
										/*$checklistName = $checklistData ? $checklistData->name : ($subchecklistData ?  $subchecklistData->name : '');*/
										
										$checklistName = $checklistData ? $checklistData->name : '';
										
										$rejectedRegionData = $result['type'] == 'checklist'
										? App\Models\Task_list_checklists::where('task_list_id',$result['task_id'])->where('checklist_id', $result['checklist_id'])->first()
										: App\Models\Task_list_subchecklists::where('task_list_id',$result['task_id'])->where('task_list_checklist_id', $result['checklist_id'])->where('subchecklist_id', $result['subchecklist_id'])->first();

										
										//$images =url('images/noimages/noimage_region.png');
										if (isset($result['image']) && $result['image'] != '') {
											$images = $result['type'] == 'checklist'
												? url('uploads/reject-files/' . $result['image'])
												: url('uploads/reject-files/subchecklist/' . $result['image']);
										} else {
											$images = url('images/noimages/corrective-completed.png');
										}
										
										$userData = App\Models\Task_lists::with('get_user')->where('id', $result['task_id'])->first();
										
										if($result['type'] == 'subchecklist')
										{
											$subchecklistData = App\Models\Task_list_subchecklists::where('task_list_id',$result['task_id'])->where('task_list_checklist_id',$result['checklist_id'])->where('subchecklist_id',$result['subchecklist_id'])->first();
											if($subchecklistData)
											{
												//foreach($subchecklistData as $subcheck)
												//{
													//$arrSubchecklist = [];
													$subchecklistName = App\Models\Subchecklist::where('id', $subchecklistData->subchecklist_id)->first();
													
													$filedata = App\Models\Task_list_subchecklist_rejected_files::where('task_list_subchecklist_id', $subchecklistData->id)->first();
													
													$images = $filedata && $filedata->file !=''  ? url('uploads/reject-files/subchecklist/' . $filedata->file) : url('images/noimages/corrective-completed.png');
													//$images =url('images/noimages/noimage_region.png');
													
													$arrSubchecklist[] = [
														'id' => $subchecklistData->id,
														'name' => $subchecklistName ? $subchecklistName->name : '',
														'image' => $images,
														'subchecklist_id' => $subchecklistData->subchecklist_id,

													];
												//}
											}
										}
										//echo "<pre>";print_r($arrSubchecklist);
									@endphp
									@if(!empty($arrSubchecklist))
										@foreach($arrSubchecklist as $val)
										@php 
											$url = $val['image'] ?? '';
											$extension = pathinfo($url, PATHINFO_EXTENSION);
											$extension = strtolower($extension);
											
											//if (!empty($result['image']))
												
											if(array_key_exists('image', $result))
												{
													$route = route('ia-los-subchecklist-completed-approved-view',['location_id'=>$location_id,'task_id'=>$result['task_id'], 'checklist_id'=> $result['checklist_id'],'subchecklist_id'=>$val['subchecklist_id'],'type' => $result['type'],'tab'=>'corrective-action']);
													$class = '';
												} else {
													$route = "javascript:void(0)";
													$class = 'list-approved-filter';
												}
										@endphp
									<div class="d-flex mb-3 task">
										<div class="date-box">
										@if (in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp']))
											<img src="{{ $val['image'] }}">
										@elseif(in_array($extension, ['mp4', 'webm', 'ogg']))
											<video controls src="{{ $val['image'] }}"></video>
										@endif
										</div>
										<div class="flex-grow-1">
											<a href="{{ $route }}">
											<h6>{{ $checklistName ?? '' }} 
											@if($val['name']!='')
												-> {{$val['name'] ?? ''}}
											@endif
											</h6>
												<p class="text-muted mb-0">
												{{ \Illuminate\Support\Str::words($rejectedRegionData->rejected_region ?? '', 30, '...') }}
												</p>
												@if($rejectedRegionData)	
												<p class="text-muted mb-0">
												<i class="fa fa-clock"></i> {{ change_date_format($rejectedRegionData->created_at, 'Y-m-d H:i:s', 'd M Y, h.i A') }}
												</p>
												@endif
												<p class="text-muted mb-0">
												<i class="fa fa-map-marker"></i> {{ $location_name ?? ''}}<button type="button" class="btn btn-outline-success status-outline-common-btn ml-10px"  style="border-color: #198754; color: #198754;margin-left: 8px;">By {{ $userData->get_user->name ?? ''}}</button>
												</p>
											</a>
										</div>
									</div>
									@endforeach
									
									@else
										@php 
											$url = $images ?? '';
											$extension = pathinfo($url, PATHINFO_EXTENSION);
											$extension = strtolower($extension);
											
											//if (!empty($result['image'])) 
												
											if(array_key_exists('image', $result))
											{
													$route = route('ia-los-checklist-completed-approved-view',['location_id'=>$location_id,'task_id'=>$result['task_id'], 'checklist_id'=> $result['checklist_id'],'type' => $result['type'],'tab'=>'corrective-action']);
													$class = '';
												} else {
													$route = "javascript:void(0)";
													$class = 'list-approved-filter';
												}
										@endphp
										<div class="d-flex mb-3 task">
										<div class="date-box">
											@if (in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp']))
											<img src="{{ $images }}">
											@elseif(in_array($extension, ['mp4', 'webm', 'ogg']))
											<video controls src="{{ $images }}"></video>
											@endif
										</div>
										<div class="flex-grow-1">
											<a href="{{ $route }}">
											<h6>{{ $checklistName ?? '' }} 
											</h6>
												<p class="text-muted mb-0">
												{{ \Illuminate\Support\Str::words($rejectedRegionData->rejected_region ?? '', 30, '...') }}
												</p>
												@if($rejectedRegionData)
												<p class="text-muted mb-0">
												<i class="fa fa-clock"></i> {{ change_date_format($rejectedRegionData->created_at, 'Y-m-d H:i:s', 'd M Y, h.i A') }}
												</p>
												@endif
												<p class="text-muted mb-0">
												<i class="fa fa-map-marker"></i> {{ $location_name ?? ''}}<button type="button" class="btn btn-outline-success status-outline-common-btn ml-10px"  style="border-color: #198754; color: #198754;margin-left: 8px;">By {{ $userData->get_user->name ?? ''}}</button>
												</p>
											</a>
										</div>
									</div>
									
									@endif
								<hr class="horizontal-line-list-page">
							@endforeach
							</span>
							<div class="load-more-appr" id="showloadappr">Load more</div>
								<input type="hidden" value="{{ $moreloadappr ?? '' }}" id="moreloadappr">
						</div>
						<div class="text-left" style="display:none" id="no_record"><strong><h3>No record found</h3></strong></div>	
					</div>
				</div>
			</section>
		</div>
	</div>
	
	<!----------- delete modal -------------->
	{{--<div class="modal fade" id="error-msg-modal" tabindex="-1" role="dialog" aria-hidden="true">
	  <div class="modal-dialog">
		<div class="modal-content" style="height: 180px;">
		  <div class="modal-header">
			<button type="button" class="close" data-dismiss="modal">
			  <span aria-hidden="true">×</span><span class="sr-only">Close</span>
			</button>
			<h3 class="modal-title" id="lineModalLabel"></h3>
		  </div>
		  <div class="modal-body d-flex justify-content-center align-items-center text-center">
		 <span id="successMessage" class="text-success d-none">Details saved successfully!</span>
			<span id="errorMessage" class="text-danger font-weight-bold"></span>
		  </div>
		</div>
	  </div>
	</div>--}}
	
	<!-- Error Message Modal -->
	<div class="modal fade" id="error-msg-modal" tabindex="-1" aria-labelledby="errorMsgLabel" aria-hidden="true">
	  <div class="modal-dialog modal-dialog-centered">
		<div class="modal-content border-danger shadow-lg" style="border-left: 5px solid #dc3545;border-radius: 8px;">
		  <div class="modal-header bg-danger text-white">
			<button type="button" class="close" data-dismiss="modal">
			  <span aria-hidden="true">×</span><span class="sr-only">Close</span>
			</button>
		  </div>
		  <div class="modal-body text-center">
			<p id="errorMessage" class="mb-0 fw-semibold text-danger fs-6"></p>
		  </div>
		</div>
	  </div>
	</div>
	<input type="hidden" value="{{ $isactive ?? ''}}" id="isactive">
	<input type="hidden" value="{{ auth()->user()->user_type ?? ''}}" id="user_id">
	<input type="hidden" value="{{ $location_id ?? ''}}" id="location_id">
	<input type="hidden" value="{{ $task_id ?? ''}}" id="task_id">
	<input type="hidden" value="{{ $j ?? ''}}" id="norecord_j">
	<input type="hidden" value="{{ $k ?? ''}}" id="norecord_k">
	<input type="hidden" value="{{ $l ?? ''}}" id="norecord_l">
	<input type="hidden" value="{{ $m ?? ''}}" id="norecord_m">
	
	<input type="hidden" value="{{ $action_show ?? ''}}" id="action_show">
	<input type="hidden" value="{{ $totalNeeded ?? ''}}" id="totalNeeded">
	<input type="hidden" value="{{ $totalAction ?? ''}}" id="totalAction">
	<input type="hidden" value="{{ $totalPlan ?? ''}}" id="totalPlan">
	<input type="hidden" value="{{ 
$totalapprcompleted ?? ''}}" id="totalapprcompleted">
	
@endsection 
@section('scripts')
<script>
	const displayBox = document.getElementById("displayBox");
	const editBox = document.getElementById("editBox");
	const addressInput = document.getElementById("addressInput");
	const doneBtn = document.getElementById("doneBtn");

	displayBox.addEventListener("click", () => {
		displayBox.style.display = "none";
		editBox.style.display = "flex";
		addressInput.focus();
	});

	doneBtn.addEventListener("click", () => {
		const value = addressInput.value.trim();
		if (value !== "") {
			displayBox.innerHTML = `
				${value}
				<span class="add_address"><i class="fa-solid fa-pen"></i></span>
			`;
		} else {
			displayBox.innerHTML = `
				Tap to add address
				<span class="add_address"><i class="fa-solid fa-pen"></i></span>
			`;
		}
		displayBox.style.display = "flex";
		editBox.style.display = "none";
	});
</script>

<script>
$(document ).ready(function() {
	var isactive = $('#isactive').val();
	var user_id = $('#user_id').val();
	//alert(isactive);
	if(isactive == 0)
	{
		const selectedTab = localStorage.getItem('selectedTab');
		//alert(selectedTab);
		if (selectedTab) {
			$('a[href="' + selectedTab + '"]').tab('show');
			
			if(selectedTab == '#corrective_needed_tab')
			{
				var norecord_j = $('#norecord_j').val();
				if(norecord_j==0)
				{
					$('#no_record').show();
				}
				
				//$('#uncomplete_tab').hide();
			}
			
			if(selectedTab == '#corrective_checked_tab')
			{
				var norecord_k = $('#norecord_k').val();
				if(norecord_k==0)
				{
					$('#no_record').show();
				}
				
				//$('#uncomplete_tab').hide();
			}
			
			if(selectedTab == '#process_final_checked_tab')
			{
				var norecord_l = $('#norecord_l').val();
				
				if(norecord_l==0)
				{
					$('#no_record').show();
				}
				//$('#uncomplete_tab').hide();
			}
			
			if(selectedTab == '#approved_final_checked_tab')
			{
				var norecord_m = $('#norecord_m').val();
				if(norecord_m==0)
				{
					$('#no_record').show();
				}
				//$('#uncomplete_tab').hide();
			}
			
			if(selectedTab == '#uncomplete_tab')
			{
				
				$('#uncomplete_tab').show();
			}
		}
	}
	
	/*if(isactive == 1 && user_id == 1)
	{
		//$('a[href="#uncomplete_tab"]').tab('show');
		//$('#uncomplete_tab').show();
		$('a[href="#corrective_needed_tab"]').tab('show');
		$('#isactive').val(0);
	}*/
	
	
	if(isactive == 1 && user_id == 3)
	{
		/*$('a[href="#corrective_needed_tab"]').tab('show');
		$('#isactive').val(0);
		
		var norecord_j = $('#norecord_j').val();
		if(norecord_j==0)
		{
			$('#no_record').show();
		}*/
		
		var totalAction = $('#totalAction').val();
		var totalPlan = $('#totalPlan').val();
		if(totalAction > 0 && totalPlan > 0)
		{
			$('a[href="#corrective_checked_tab"]').tab('show');
			$('#isactive').val(0); 
		}
		else if(totalAction == 0 && totalPlan == 0) 
		{
			$('a[href="#corrective_checked_tab"]').tab('show');
		    $('#isactive').val(0); 
		}
		else if(totalAction == 0 && totalPlan > 0) 
		{
			$('a[href="#process_final_checked_tab"]').tab('show');
		    $('#isactive').val(0); 
		}
		
		var norecord_k = $('#norecord_k').val();
		if(norecord_k==0)
		{
			$('#no_record').show();
			$('#showloadaction').hide();
		}
	}
	
	//alert(isactive);
	
  
  
   $(document).on('click','.donesubmit', function(){
		var location_id = $('#location_id').val();
		//var category_id = $('#category_id').val();
		var details  = $('#addressInput').val();
		var task_id  = $('#task_id').val();
		if(details=='')
		{
			$('#errorMessage').fadeIn().delay(2000).fadeOut();
			return false;
		}
	    //alert(location_id);alert(category_id);alert(details);
		var URL = "{{ route('send-location-details') }}";
		
		$.ajax({
			url: URL,
			type: "POST",
			data: {location_id:location_id,task_id:task_id,details:details, _token: csrfToken},
			dataType: 'json',
			success: function(response) {
				//alert(response);
				$('#addressInput').val('');
				$('#successMessage').fadeIn().delay(2000).fadeOut();
			},
		});
   });
   
   
   
   $(document).on('click','.chk-task-id', function(){
	   var cat_id = $(this).data('cat');
	   //var subcat_id = $(this).data('subcat');
	   var location_id = $(this).data('location');
	   var task_id = $(this).data('taskid');
	   //alert(task_id);
	   var URL = "{{ route('check-task-id') }}";
	   //alert(cat_id);alert(task_id);
	   
	   $.ajax({
			url: URL,
			type: "POST",
			data: {cat_id:cat_id,location_id:location_id,task_id:task_id, _token: csrfToken},
			dataType: 'json',
			success: function(response) {
				//alert(response.hasData);
				$('#taskid').val(response.taskid);
				/*if(!response.hasData)
				{
					$('#error-msg-modal').modal('show');
					//$('#errorMessage').text(response.message).fadeIn().delay(8000).fadeOut();
					$('#errorMessage')
					  .text(response.message)
					  .fadeIn()
					  .delay(6000)
					  .fadeOut(400, function () {
						$('#error-msg-modal').modal('hide');
					  });
				}
				else {*/
					var mode = 'no';
					if(response.finalEditPage==1)
					{
						mode = 'yes';
					}
					
					//var taskid = $('#taskid').val();
					//alert(taskid);
					var taskid = task_id;
					var baseUrl = "{{ url('/checklist-question') }}";
					var redirectUrl = baseUrl + '/'+ taskid + '/' + cat_id + '/' + mode;
					window.location.href = redirectUrl;
					
				//}
			},
		});
	   
   });
   
   $(document).on('click','.unCompletetab, .correctiveNeeded, .correctiveChecked, .finalChecked, .approvedFinalChecked', function(){
	   $('#no_record').hide();
		var location_id = $('#location_id').val();
		var task_id = $('#task_id').val();
		$('#isactive').val(0);
		const tabId = $(this).attr('href');
		//alert(tabId);
		
		if(tabId == '#corrective_needed_tab')
		{
			$('.tab-pane').removeClass('active show');
			//$('#corrective_needed_tab').addClass('active show');
			$('.nav-tabs .nav-link').removeClass('active');
			//$('.nav-tabs .nav-link[href="#corrective_needed_tab"]').addClass('active');
			
		}
		
		if(tabId == '#corrective_checked_tab')
		{
			//alert(tabId);
			$('.tab-pane').removeClass('active show');
			//$('#corrective_checked_tab').addClass('active show');
			$('.nav-tabs .nav-link').removeClass('active');
			//$('.nav-tabs .nav-link[href="#corrective_checked_tab"]').addClass('active');
			
		}
		
		if(tabId == '#process_final_checked_tab')
		{
			$('.tab-pane').removeClass('active show');
			//$('#process_final_checked_tab').addClass('active show');
			$('.nav-tabs .nav-link').removeClass('active');
			//$('.nav-tabs .nav-link[href="#process_final_checked_tab"]').addClass('active');
			
		}
		
		if(tabId == '#approved_final_checked_tab')
		{
			$('.tab-pane').removeClass('active show');
			//$('#approved_final_checked_tab').addClass('active show');
			$('.nav-tabs .nav-link').removeClass('active');
			//$('.nav-tabs .nav-link[href="#approved_final_checked_tab"]').addClass('active');
		}
		
		if(tabId == '#uncomplete_tab')
		{
			$('.tab-pane').removeClass('active show');
			//$('#uncomplete_tab').addClass('active show');
			$('.nav-tabs .nav-link').removeClass('active');
			//$('.nav-tabs .nav-link[href="#uncomplete_tab"]').addClass('active');
		}
		
		localStorage.setItem('selectedTab', tabId);
		const refreshUrl = "{{ url('los-task-status/LOCATION_ID/ISACTIVE') }}";
		const redirectUrl = refreshUrl.replace('LOCATION_ID', location_id).replace('ISACTIVE', 0);
		window.location.href = redirectUrl;
	});
	
	
	const scrollWrapper = $('#tabScrollWrapper');
	 const scrollAmount = 150;
    $('#scrollLeft').click(function () {
        scrollWrapper.animate({ scrollLeft: scrollWrapper.scrollLeft() - 150 }, 300);
    });

    $('#scrollRight').click(function () {
        scrollWrapper.animate({ scrollLeft: scrollWrapper.scrollLeft() + 150 }, 300);
    });
	
	var insActionApproved 	= localStorage.getItem('insActionApproved');
	var insPlanApproved 	= localStorage.getItem('insPlanApproved');
	var insActionRejected 	= localStorage.getItem('insActionRejected');
	var insPlanRejected 	= localStorage.getItem('insPlanRejected');
	var insFinalApproved 	= localStorage.getItem('insFinalApproved');
	var insFinalRejected 	= localStorage.getItem('insFinalRejected');
	
	if(insActionApproved == 1)
	{
		$('.corrective-message').html('&nbsp;&nbsp;<i class="fa fa-check"></i>&nbsp;&nbsp;Corrective action approved&nbsp;&nbsp;').fadeIn().delay(3000).fadeOut();
		localStorage.removeItem('insActionApproved');
	}
	
	if(insPlanApproved == 1)
	{
		$('.corrective-message').html('&nbsp;&nbsp;<i class="fa fa-check"></i>&nbsp;&nbsp;Corrective plan approved&nbsp;&nbsp;').fadeIn().delay(3000).fadeOut();
		localStorage.removeItem('insPlanApproved');
	}
	
	if(insActionRejected == 1)
	{
		$('.corrective-message').html('&nbsp;&nbsp;<i class="fa fa-check"></i>&nbsp;&nbsp;Corrective action rejected&nbsp;&nbsp;').fadeIn().delay(3000).fadeOut();
		localStorage.removeItem('insActionRejected');
	}
	
	if(insPlanRejected == 1)
	{
		$('.corrective-message').html('&nbsp;&nbsp;<i class="fa fa-check"></i>&nbsp;&nbsp;Corrective plan rejected&nbsp;&nbsp;').fadeIn().delay(3000).fadeOut();
		localStorage.removeItem('insPlanRejected');
	}
	
	if(insFinalApproved == 1)
	{
		$('.corrective-message').html('&nbsp;&nbsp;<i class="fa fa-check"></i>&nbsp;&nbsp;Approved final round check&nbsp;&nbsp;').fadeIn().delay(3000).fadeOut();
		localStorage.removeItem('insFinalApproved');
	}
	
	if(insFinalRejected == 1)
	{
		$('.corrective-message').html('&nbsp;&nbsp;<i class="fa fa-check"></i>&nbsp;&nbsp;Rejected final round check&nbsp;&nbsp;').fadeIn().delay(3000).fadeOut();
		localStorage.removeItem('insFinalRejected');
	}
	
	
	$(document).on('click', '.load-more-needed', function(){
		var location_id = $('#location_id').val();
		var moreload = $("#moreloadneeded").val();
		var tab = 'correctiveneeded';
		
		$('.load-more-needed').html('<i class="fas fa-spinner fa-spin"></i> &nbsp;&nbsp;Loading...');
		//alert(moreload);
		//var URL = "{{ route('los-load-more-data') }}"; old
		var URL = "{{ route('los-load-more-needed-data') }}";
		$.ajax({
			url: URL,
			type: "POST",
			data: {tab:tab, location_id:location_id, moreload:moreload, _token: csrfToken},
			dataType: 'json',
			success: function(response) {
				//alert(response.html);
				if(response.remain>0)
				{
					$('#showloadneeded').show();
				}
				else
				{
					$('#showloadneeded').hide();
				}
				$("#moreloadneeded").val(response.loadmore)
				$("#neddedDiv").append(response.html);
			},
			complete: function() {
				$('.load-more-needed').html('Load more');
			}
		});
		
	});
	
	$(document).on('click', '.load-more-action', function(){
		var location_id = $('#location_id').val();
		var moreload = $("#moreloadaction").val();
		var tab = 'correctiveaction';
		
		$('.load-more-action').html('<i class="fas fa-spinner fa-spin"></i> &nbsp;&nbsp;Loading...');
		//alert(moreload);
		//var URL = "{{ route('los-load-more-data') }}";
		var URL = "{{ route('los-load-more-action-data') }}";
		$.ajax({
			url: URL,
			type: "POST",
			data: {tab:tab, location_id:location_id, moreload:moreload, _token: csrfToken},
			dataType: 'json',
			success: function(response) {
				//alert(response.html);
				if(response.remain>0)
				{
					$('#showloadaction').show();
				}
				else
				{
					$('#showloadaction').hide();
				}
				$("#moreloadaction").val(response.loadmore)
				$("#actionDiv").append(response.html);
			},
			complete: function() {
				$('.load-more-action').html('Load more');
			}
		});
	});
	
	$(document).on('click', '.load-more-plan', function(){
		var location_id = $('#location_id').val();
		var moreload = $("#moreloadplan").val();
		var tab = 'correctiveplan';
		
		$('.load-more-plan').html('<i class="fas fa-spinner fa-spin"></i> &nbsp;&nbsp;Loading...');
		//alert(moreload);
		//var URL = "{{ route('los-load-more-data') }}";
		var URL = "{{ route('los-load-more-plan-data') }}";
		$.ajax({
			url: URL,
			type: "POST",
			data: {tab:tab, location_id:location_id, moreload:moreload, _token: csrfToken},
			dataType: 'json',
			success: function(response) {
				//alert(response.remain);
				if(response.remain>0)
				{
					$('#showloadplan').show();
				}
				else
				{
					$('#showloadplan').hide();
				}
				$("#moreloadplan").val(response.loadmore)
				$("#planDiv").append(response.html);
			},
			complete: function() {
				$('.load-more-plan').html('Load more');
			}
		});
		
	});
	
	$(document).on('click', '.load-more-appr', function(){
		var location_id = $('#location_id').val();
		var moreload = $("#moreloadappr").val();
		var tab = 'correctiveapproved';
		
		$('.load-more-appr').html('<i class="fas fa-spinner fa-spin"></i> &nbsp;&nbsp;Loading...');
		//alert(moreload);
		//var URL = "{{ route('los-load-more-data') }}";
		var URL = "{{ route('los-load-more-appr-data') }}";
		$.ajax({
			url: URL,
			type: "POST",
			data: {tab:tab, location_id:location_id, moreload:moreload, _token: csrfToken},
			dataType: 'json',
			success: function(response) {
				//alert(response.remain);
				if(response.remain>0)
				{
					$('#showloadplan').show();
				}
				else
				{
					$('#showloadappr').hide();
				}
				$("#moreloadappr").val(response.loadmore)
				$("#appCompletedDiv").append(response.html);
			},
			complete: function() {
				$('.load-more-appr').html('Load more');
			}
		});
		
	});
	
	// -- load more button first time check -- 
	var action_show = $('#action_show').val();
	var totalNeeded = $('#totalNeeded').val();
	var totalAction = $('#totalAction').val();
	var totalPlan = $('#totalPlan').val();
	var totalapprcompleted = $('#totalapprcompleted').val();
	
	if(parseInt(totalNeeded) > parseInt(action_show))
	{
		$('#showloadneeded').show();
	}
	
	if(parseInt(totalAction) > parseInt(action_show))
	{
		$('#showloadaction').show();
	}
	
	if(parseInt(totalPlan) > parseInt(action_show))
	{
		$('#showloadplan').show();
	}
	
	if(parseInt(totalapprcompleted) > parseInt(action_show))
	{
		$('#showloadappr').show();
	}
});
</script>
@endsection

