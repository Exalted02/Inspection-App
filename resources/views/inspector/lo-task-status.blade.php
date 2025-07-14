@extends('layouts.app')
@section('content')
@php 
//echo "<pre>";print_r($userdata);die;
//echo "<pre>";print_r($correctiveNeeded);die;
//echo "<pre>";print_r($correctiveCheck);die;
//echo "<pre>";print_r($correctiveAction);die;
//echo "<pre>";print_r($approvedCompleted);die;
$location_name = App\Models\Manage_location::where('id', $location_id)->first()->location_name;
use Carbon\Carbon;
$j = 0;
$k = 0;
$l = 0;
$m = 0;

$countNedded = 0;
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

foreach($correctiveCheck as $result)
{
	if($result['lo_direct_approve'] == 1 && ($result['inspector_action'] == 0 || $result['los_action'] == 0))
	{
		$countAction++;
	}
	
	if($result['lo_direct_approve'] == 0  && ($result['inspector_action'] == 0 || $result['los_action'] == 0))
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

$correctiveNeededArray = [];
$correctiveActionArray = [];
$correctivePlanArray = [];
$approvedCompletedArray = [];


//echo "<pre>";print_r($correctiveNeeded);die;
foreach($correctiveNeeded as $needed)
{
	if(($needed['inspector_action']=='' && $needed['los_action']=='') || ($needed['inspector_action']== 2 && $needed['los_action']==2))
	{
		if($needed['type'] == 'checklist')
		{
			$correctiveNeededArray[] = [
				'type' => $needed['type'],
				'task_id' => $needed['task_id'],
				'checklist_id' => $needed['checklist_id'],
				'rejected_region' => $needed['rejected_region'],
				'image' => $needed['image'],
				'inspector_action' => $needed['inspector_action'],
				'los_action' => $needed['los_action']
			];
		}
		else{
			$correctiveNeededArray[] = [
				'type' => $needed['type'],
				'task_id' => $needed['task_id'],
				'checklist_id' => $needed['checklist_id'],
				'subchecklist_id' => $needed['subchecklist_id'],
				'rejected_region' => $needed['rejected_region'],
				'image' => $needed['image'],
				'inspector_action' => $needed['inspector_action'],
				'los_action' => $needed['los_action']
			];
		}
		
	}
}

$correctiveNeededArray = array_slice($correctiveNeededArray, 0, config('custom.LOAD_MORE_LIST_SHOW'));

//echo "<pre>"; print_r($correctiveCheck);die;
foreach($correctiveCheck as $result)
{
	if($result['lo_direct_approve'] == 1 && ($result['inspector_action'] == 0 || $result['los_action'] == 0))
	{
		if($result['type'] == 'checklist')
		{
			$correctiveActionArray[] = [
				'type' => $result['type'],
				'task_id' => $result['task_id'],
				'checklist_id' => $result['checklist_id'],
				'rejected_region' => $result['rejected_region'],
				'image' => $result['image'],
				'inspector_action' => $result['inspector_action'],
				'los_action' => $result['los_action'],
				'second_checked' => $result['second_checked'],
				'lo_direct_approve' => $result['lo_direct_approve']
			
			];
		}
		else{
			$correctiveActionArray[] = [
				'type' => $result['type'],
				'task_id' => $result['task_id'],
				'checklist_id' => $result['checklist_id'],
				'subchecklist_id' => $result['subchecklist_id'],
				'rejected_region' => $result['rejected_region'],
				'image' => $result['image'],
				'inspector_action' => $result['inspector_action'],
				'los_action' => $result['los_action'],
				'second_checked' => $result['second_checked'],
				'lo_direct_approve' => $result['lo_direct_approve']
			];
		}
	}
	
	if($result['lo_direct_approve'] == 0  && ($result['inspector_action'] == 0 || $result['los_action'] == 0))
	{
		if($result['type'] == 'checklist')
		{
			$correctivePlanArray[] = [
				'type' => $result['type'],
				'task_id' => $result['task_id'],
				'checklist_id' => $result['checklist_id'],
				'rejected_region' => $result['rejected_region'],
				'image' => $result['image'],
				'inspector_action' => $result['inspector_action'],
				'los_action' => $result['los_action'],
				'second_checked' => $result['second_checked'],
				'lo_direct_approve' => $result['lo_direct_approve']
			
			];
		}
		else{
			$correctivePlanArray[] = [
				'type' => $result['type'],
				'task_id' => $result['task_id'],
				'checklist_id' => $result['checklist_id'],
				'subchecklist_id' => $result['subchecklist_id'],
				'rejected_region' => $result['rejected_region'],
				'image' => $result['image'],
				'inspector_action' => $result['inspector_action'],
				'los_action' => $result['los_action'],
				'second_checked' => $result['second_checked'],
				'lo_direct_approve' => $result['lo_direct_approve']
			];
		}
	}
}

$correctiveActionArray = array_slice($correctiveActionArray, 0, config('custom.LOAD_MORE_LIST_SHOW'));
$correctivePlanArray = array_slice($correctivePlanArray, 0, config('custom.LOAD_MORE_LIST_SHOW'));

//echo "<pre>";print_r($approvedCompleted);die;
foreach($approvedCompleted as $result)
{
	if($result['inspector_action'] == 1 && $result['los_action'] == 1)
	{
		if($result['type'] == 'checklist')
		{
			if(isset($result['image']))
			{
				$approvedCompletedArray[] = [
					'type' => $result['type'],
					'task_id' => $result['task_id'],
					'checklist_id' => $result['checklist_id'],
					'rejected_region' => $result['rejected_region'],
					'image' => $result['image'],
					'inspector_action' => $result['inspector_action'],
					'los_action' => $result['los_action']
				];
			}
			else
			{
				$approvedCompletedArray[] = [
					'type' => $result['type'],
					'task_id' => $result['task_id'],
					'checklist_id' => $result['checklist_id'],
					'rejected_region' => $result['rejected_region'],
					'inspector_action' => $result['inspector_action'],
					'los_action' => $result['los_action']
				];
			}
		}
		else
		{
			
			if(isset($result['image']))
			{
				$approvedCompletedArray[] = [
					'type' => $result['type'],
					'task_id' => $result['task_id'],
					'checklist_id' => $result['checklist_id'],
					'subchecklist_id' => $result['subchecklist_id'],
					'rejected_region' => $result['rejected_region'],
					'image' => $result['image'],
					'inspector_action' => $result['inspector_action'],
					'los_action' => $result['los_action']
				];
			}
			else
			{
				$approvedCompletedArray[] = [
					'type' => $result['type'],
					'task_id' => $result['task_id'],
					'checklist_id' => $result['checklist_id'],
					'subchecklist_id' => $result['subchecklist_id'],
					'rejected_region' => $result['rejected_region'],
					'inspector_action' => $result['inspector_action'],
					'los_action' => $result['los_action']
				];
			}
			
			
		}
		
	}
}

$approvedCompletedArray = array_slice($approvedCompletedArray, 0, config('custom.LOAD_MORE_LIST_SHOW'));

$action_show = config('custom.LOAD_MORE_LIST_SHOW');
$totalNeeded = $countNedded;
$totalAction = $countAction;
$totalPlan = $countPlan;
$totalapprcompleted = $countCompleted;

//echo "<pre>";print_r($correctiveActionArray);die;
//echo "<pre>";print_r($correctiveNeededArray);die;

@endphp
    <!-- =-=-=-=-=-=-= Breadcrumb =-=-=-=-=-=-= -->
	
		<div class="profile-card">
		<div class="profile-banner" style="background-image: url( '{{url('uploads/profile/' .$userdata->id .'/locationowner/'. $userdata->background_image )}} ')"></div>
			<div class="profile-info">
				<img class="profile-avatar" src="{{ url('uploads/profile/' .$userdata->id .'/locationowner/'. $userdata->profile_image)}}" alt="Profile Picture">
				<h2 class="profile-name">{{ $userdata->name ?? ''}}</h2>
				<p class="profile-description">
					Location Owner at {{ $userdata->get_company->company_name ?? '' }},<br>
						{{ $location_name ?? '' }}
						
				</p>
			</div>
		</div>
    <!-- =-=-=-=-=-=-= Breadcrumb End =-=-=-=-=-=-= --> 
    <!-- =-=-=-=-=-=-= Main Content Area =-=-=-=-=-=-= -->
	<div class="container location-owner-details">
	<div class="main-content-area clearfix">
			<section class="custom-padding1">
				<div class="container">
					<div class="row custom-tab">
						<!-- Tabs -->
						{{--<div class="tab-scroll-container">
						<div class="scroll-arrow left-arrow" id="scrollLeft"><i class="fa fa-chevron-left"></i></div>
							<div class="tab-scroll-wrapper" id="tabScrollWrapper">--}}
									<ul class="nav nav-tabs custom-tab-style" role="tablist">
									{{--<li role="presentation" class=""><a class="correctiveAction" href="#inprogress_tab" aria-controls="inprogress_tab" role="tab">{{ $total_corrective_action ?? ''}} Corrective actions</a></li>
										<li role="presentation"><a class="completedtab"  href="#completed_tab" aria-controls="completed_tab" role="tab">{{ count($correctiveCheck)}} Corrective checks</a></li>
										<li role="presentation"><a class="rejectInspector"  href="#rejected_by_inspector_tab" aria-controls="completed_tab" role="tab">Rejected</a></li>
									<li role="presentation"><a class="approvedByInspector" href="#approved_by_inspector_tab" aria-controls="completed_tab" role="tab">Approved</a></li>--}}
									
									<li role="presentation" class=""><a class="correctiveAction" href="#inprogress_tab" aria-controls="inprogress_tab" role="tab"><span class="counter-red">{{ $countNedded }}</span>Corrective Needed</a></li>
										<li role="presentation"><a class="completedtab"  href="#completed_tab" aria-controls="completed_tab" role="tab"><span class="counter-grey">{{ $countAction }}</span>Corrective Action</a></li>
										<li role="presentation"><a class="rejectInspector"  href="#rejected_by_inspector_tab" aria-controls="completed_tab" role="tab"><span class="counter-grey">{{ $countPlan }}</span>Corrective Plan</a></li>
										<li role="presentation"><a class="approvedByInspector" href="#approved_by_inspector_tab" aria-controls="completed_tab" role="tab"><span class="counter-grey">{{ $countCompleted }}</span>Completed/Approved</a></li>
										
									</ul>
								{{--</div>
								<div class="scroll-arrow right-arrow" id="scrollRight"><i class="fa fa-chevron-right"></i></div>
						</div>--}}
						<!-- Tab panes -->
						<div class="tab-content">
							<div role="tabpanel" class="tab-pane" id="inprogress_tab">
							<span id="neddedDiv">
							@foreach($correctiveNeededArray as $result)
								@php 
								    $j++;
								    $arrSubchecklist = [];
									$checklistData = App\Models\Checklist::where('id', $result['checklist_id'])->first();
									
									/*$subchecklistData = App\Models\Subchecklist::where('id', $result['checklist_id'])->first();*/
									
									/*$checklistName = $checklistData ? $checklistData->name : ($subchecklistData ?  $subchecklistData->name : '');*/
									
									$checklistName = $checklistData ? $checklistData->name : '';
									
									$rejectedRegionData = $result['type'] == 'checklist'
									? App\Models\Task_list_checklists::where('task_list_id',$result['task_id'])->where('checklist_id', $result['checklist_id'])->first()
									: App\Models\Task_list_subchecklists::where('task_list_id',$result['task_id'])->where('task_list_checklist_id', $result['checklist_id'])->first();

									if($result['image'] != '')
									{
										$images = $result['type'] == 'checklist' ?  url('uploads/reject-files/' . $result['image']) :  url('uploads/reject-files/subchecklist/' . $result['image']);
									}
									else{
										$images = url('images/noimages/noimage_region.png');
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
												
												$images = $filedata && $filedata->file != ''  ? url('uploads/reject-files/subchecklist/' . $filedata->file) : url('images/noimages/noimage_region.png') ;
												
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
									{{--<a href="{{ route('location-owner-subchecklist-question-reply',['task_id'=>$result['task_id'], 'checklist_id'=> $result['checklist_id'],'subchecklist_id'=>$val['subchecklist_id'],'type' => $result['type'],'tab'=>'corrective-action']) }}">--}}
									    @if($result['inspector_action'] == 2)
										<a href="{{ route('location-owner-subchecklist-rejected-question-reply',['task_id'=>$result['task_id'], 'checklist_id'=> $result['checklist_id'],'subchecklist_id'=>$result['subchecklist_id'],'type' => $result['type'] ]) }}">
									    @else
										<a href="{{ route('location-owner-subchecklist-question-reply',['task_id'=>$result['task_id'], 'checklist_id'=> $result['checklist_id'],'subchecklist_id'=>$val['subchecklist_id'],'type' => $result['type'],'tab'=>'corrective-action', 'lid'=> $location_id ]) }}">
										@endif
										<h6>{{ $checklistName ?? '' }} 
										@if($val!='')
											-> {{$val['name'] ?? ''}}
										@endif
										</h6>
											<p class="text-muted mb-0">
											{{ \Illuminate\Support\Str::words($rejectedRegionData->rejected_region ?? '', 30, '...') }}
											</p>
											<p class="text-muted mb-0">
											<i class="fa fa-clock">  {{ Carbon::parse($rejectedRegionData->created_at)->format('d M Y, h:i A') }}</i>
											</p>
											<p class="text-muted mb-0">
											<i class="fa fa-map-marker"></i> {{ $location_name ?? ''}}
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
									{{--<a href="{{ route('location-owner-checklist-question-reply',['task_id'=>$result['task_id'], 'checklist_id'=> $result['checklist_id'],'type' => $result['type'],'tab'=>'corrective-action']) }}">--}}
										@if($result['inspector_action'] == 2)
										<a href="{{ route('location-owner-checklist-rejected-question-reply',['task_id'=>$result['task_id'], 'checklist_id'=> $result['checklist_id'],'type' => $result['type'] ]) }}">
										@else
										<a href="{{ route('location-owner-checklist-question-reply',['task_id'=>$result['task_id'], 'checklist_id'=> $result['checklist_id'],'type' => $result['type'],'tab'=>'corrective-action', 'lid'=> $location_id]) }}">
										@endif
										<h6>{{ $checklistName ?? '' }} 
										</h6>
											<p class="text-muted mb-0">
											{{ \Illuminate\Support\Str::words($rejectedRegionData->rejected_region ?? '', 30, '...') }}
											</p>
											<p class="text-muted mb-0">
											<i class="fa fa-clock">  {{ Carbon::parse($rejectedRegionData->created_at)->format('d M Y, h:i A') }}</i>
											</p>
											<p class="text-muted mb-0">
											<i class="fa fa-map-marker"></i> {{ $location_name ?? ''}}
											</p>
										</a>
									</div>
								</div>
								@endif
							
							@endforeach
							</span>
								<div class="load-more-needed" id="showloadneeded">Load more</div>
								<input type="hidden" value="{{ $moreloadneeded ?? '' }}" id="moreloadneeded">
							</div>
							<div class="tab-pane" id="completed_tab">
							<span id="actionDiv">
							@foreach($correctiveActionArray as $result)
							@if($result['lo_direct_approve'] == 1 && ($result['inspector_action'] == 0 || $result['los_action'] == 0))
								@php 
							        $k++;
								    $arrSubchecklist = [];
									$checklistData = App\Models\Checklist::where('id', $result['checklist_id'])->first();
									
									/*$subchecklistData = App\Models\Subchecklist::where('id', $result['checklist_id'])->first();*/
									
									/*$checklistName = $checklistData ? $checklistData->name : ($subchecklistData ?  $subchecklistData->name : '');*/
									
									$checklistName = $checklistData ? $checklistData->name : '';
									
									$rejectedRegionData = $result['type'] == 'checklist'
									? App\Models\Task_list_checklists::where('task_list_id',$result['task_id'])->where('checklist_id', $result['checklist_id'])->first()
									: App\Models\Task_list_subchecklists::where('task_list_id',$result['task_id'])->where('task_list_checklist_id', $result['checklist_id'])->first();

									if($result['image'] != '')
									{
										$images = $result['type'] == 'checklist' ?  url('uploads/reject-files/' . $result['image']) :  url('uploads/reject-files/subchecklist/' . $result['image']);
									}
									else{
										$images = url('images/noimages/noimage_region.png');
									}
									
									if($result['type'] == 'subchecklist')
									{
										$subchecklistData = App\Models\Task_list_subchecklists::where('task_list_id',$result['task_id'])->where('task_list_checklist_id',$result['checklist_id'])->where('subchecklist_id',$result['subchecklist_id'])->where('approve',0)->first();
										if($subchecklistData)
										{
											//foreach($subchecklistData as $subcheck)
											//{
												$subchecklistName = App\Models\Subchecklist::where('id', $subchecklistData->subchecklist_id)->first();
												
												$filedata = App\Models\Task_list_subchecklist_rejected_files::where('task_list_subchecklist_id', $subchecklistData->id)->first();
												
												$images = $filedata && $filedata->file != '' ? url('uploads/reject-files/subchecklist/' . $filedata->file) : url('images/noimages/noimage_region.png');
												
												$arrSubchecklist[] = [
													'id' => $subchecklistData->id,
													'name' => $subchecklistName ? $subchecklistName->name : '',
													'image' => $images,
													'subchecklist_id' => $subchecklistData->subchecklist_id,
												];
											//}
										}
									}
									
									$appr_by = '';
									if($result['inspector_action']==1)
									{
										$appr_by = 'Approved Inspector';
									}
									
									if($result['los_action']==1)
									{
										$appr_by = 'Approved LOS';
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
										<a href="{{ route('lo-subchecklist-first-reply-view',['location_id'=>$location_id,'task_id'=>$result['task_id'], 'checklist_id'=> $result['checklist_id'],'subchecklist_id'=>$val['subchecklist_id'],'type' => $result['type'],'tab'=>'corrective-action']) }}">
										<h6>{{ $checklistName ?? '' }} 
										@if($val!='')
											-> {{$val['name'] ?? ''}}
										@endif
										</h6>
											<p class="text-muted mb-0">
											{{ \Illuminate\Support\Str::words($rejectedRegionData->rejected_region ?? '', 30, '...') }}
											</p>
											<p class="text-muted mb-0">
											<i class="fa fa-clock">  {{ Carbon::parse($rejectedRegionData->created_at)->format('d M Y, h:i A') }}</i>
											</p>
											<p class="text-muted mb-0">
											<i class="fa fa-map-marker"></i> {{ $location_name ?? ''}} 
												@if($result['inspector_action']==1)
												<button type="button" class="btn btn-outline-success"  style="pointer-events: none; background-color: transparent; border-color: #198754; color: #198754; padding: 2px 6px; font-size: 12px; line-height: 1;">Approved Inspector</button>
												@elseif($result['los_action']==1)
												<button type="button" class="btn btn-outline-success"  style="pointer-events: none; background-color: transparent; border-color: #198754; color: #198754; padding: 2px 6px; font-size: 12px; line-height: 1;">Approved LOS</button>
												@else
													<button type="button" class="btn btn-warning" style="pointer-events: none; background-color: transparent; border-color: #ffc107; color: #ffc107; padding: 2px 6px; font-size: 12px; line-height: 1;">Pending</button>
												@endif
												
													{{--<button type="button" class="btn btn-outline-success btn-sm custom-small-btn" style="pointer-events: none;">{{ $appr_by }}</button>--}}
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
										<a href="{{ route('lo-checklist-first-reply-view',['location_id'=>$location_id,'task_id'=>$result['task_id'], 'checklist_id'=> $result['checklist_id'],'type' => $result['type'],'tab'=>'corrective-action']) }}">
										<h6>{{ $checklistName ?? '' }} 
										</h6>
											<p class="text-muted mb-0">
											{{ \Illuminate\Support\Str::words($rejectedRegionData->rejected_region ?? '', 30, '...') }}
											</p>
											<p class="text-muted mb-0">
											<i class="fa fa-clock">  {{ Carbon::parse($rejectedRegionData->created_at)->format('d M Y, h:i A') }}</i>
											</p>
											<p class="text-muted mb-0">
											<i class="fa fa-map-marker"></i> {{ $location_name ?? ''}} 
												@if($result['inspector_action']==1)
												<button type="button" class="btn btn-outline-success"  style="pointer-events: none; background-color: transparent; border-color: #198754; color: #198754; padding: 2px 6px; font-size: 12px; line-height: 1;">Approved Inspector</button>
												@elseif($result['los_action']==1)
												<button type="button" class="btn btn-outline-success"  style="pointer-events: none; background-color: transparent; border-color: #198754; color: #198754; padding: 2px 6px; font-size: 12px; line-height: 1;">Approved LOS</button>
												@else
													<button type="button" class="btn btn-warning" style="pointer-events: none; background-color: transparent; border-color: #ffc107; color: #ffc107; padding: 2px 6px; font-size: 12px; line-height: 1;">Pending</button>
												@endif
											</p>
										</a>
									</div>
								</div>
								@endif
							@endif
							@endforeach
							</span>
							<div class="load-more-action" id="showloadaction">Load more</div>
								<input type="hidden" value="{{ $moreloadaction ?? '' }}" id="moreloadaction">
							
							</div>
							<div class="tab-pane" id="rejected_by_inspector_tab">
							<span id="planDiv">
							@foreach($correctivePlanArray as $result)
								@if($result['lo_direct_approve'] == 0  && ($result['inspector_action'] == 0 || $result['los_action'] == 0))
									@php 
								        $l++;
										$arrSubchecklist = [];
										$checklistData = App\Models\Checklist::where('id', $result['checklist_id'])->first();
										
										/*$subchecklistData = App\Models\Subchecklist::where('id', $result['checklist_id'])->first();*/
										
										/*$checklistName = $checklistData ? $checklistData->name : ($subchecklistData ?  $subchecklistData->name : '');*/
										
										$checklistName = $checklistData ? $checklistData->name : '';
										
										$rejectedRegionData = $result['type'] == 'checklist'
										? App\Models\Task_list_checklists::where('task_list_id',$result['task_id'])->where('checklist_id', $result['checklist_id'])->first()
										: App\Models\Task_list_subchecklists::where('task_list_id',$result['task_id'])->where('task_list_checklist_id', $result['checklist_id'])->first();

										if($result['image'] != '')
										{
											$images = $result['type'] == 'checklist' ?  url('uploads/reject-files/' . $result['image']) :  url('uploads/reject-files/subchecklist/' . $result['image']);
										}
										else{
											$images = url('images/noimages/noimage_region.png');
										}
										
										if($result['type'] == 'subchecklist')
										{
											$subchecklistData = App\Models\Task_list_subchecklists::where('task_list_id',$result['task_id'])->where('task_list_checklist_id',$result['checklist_id'])->where('subchecklist_id',$result['subchecklist_id'])->where('approve',0)->first();
											if($subchecklistData)
											{
												//foreach($subchecklistData as $subcheck)
												//{
													$subchecklistName = App\Models\Subchecklist::where('id', $subchecklistData->subchecklist_id)->first();
													
													$filedata = App\Models\Task_list_subchecklist_rejected_files::where('task_list_subchecklist_id', $subchecklistData->id)->first();
													
													$images = $filedata && $filedata->file != '' ? url('uploads/reject-files/subchecklist/' . $filedata->file) : url('images/noimages/noimage_region.png');
													
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
											{{--<a href="{{ route('location-owner-subchecklist-rejected-question-reply',['task_id'=>$result['task_id'], 'checklist_id'=> $result['checklist_id'],'subchecklist_id'=>$result['subchecklist_id'],'type' => $result['type'] ]) }}">--}}
												<a href="{{ route('lo-subchecklist-first-reply-view',['location_id'=>$location_id,'task_id'=>$result['task_id'], 'checklist_id'=> $result['checklist_id'],'subchecklist_id'=>$val['subchecklist_id'],'type' => $result['type'],'tab'=>'corrective-action']) }}">
												<h6>{{ $checklistName ?? '' }} 
												@if($val!='')
													-> {{$val['name'] ?? ''}}
												@endif
												</h6>
													<p class="text-muted mb-0">
													{{ \Illuminate\Support\Str::words($rejectedRegionData->rejected_region ?? '', 30, '...') }}
													</p>
													<p class="text-muted mb-0">
													<i class="fa fa-clock">  {{ Carbon::parse($rejectedRegionData->created_at)->format('d M Y, h:i A') }}</i>
													</p>
													<p class="text-muted mb-0">
													<i class="fa fa-map-marker"></i> {{ $location_name ?? ''}}
													@if($result['inspector_action']==1)
													<button type="button" class="btn btn-outline-success"  style="pointer-events: none; background-color: transparent; border-color: #198754; color: #198754; padding: 2px 6px; font-size: 12px; line-height: 1;">Approved Inspector</button>
													@elseif($result['los_action']==1)
													<button type="button" class="btn btn-outline-success"  style="pointer-events: none; background-color: transparent; border-color: #198754; color: #198754; padding: 2px 6px; font-size: 12px; line-height: 1;">Approved LOS</button>
													@else
														<button type="button" class="btn btn-warning" style="pointer-events: none; background-color: transparent; border-color: #ffc107; color: #ffc107; padding: 2px 6px; font-size: 12px; line-height: 1;">Pending</button>
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
										{{--<a href="{{ route('location-owner-checklist-rejected-question-reply',['task_id'=>$result['task_id'], 'checklist_id'=> $result['checklist_id'],'type' => $result['type'] ]) }}">--}}
											<a href="{{ route('lo-checklist-first-reply-view',['location_id'=>$location_id,'task_id'=>$result['task_id'], 'checklist_id'=> $result['checklist_id'],'type' => $result['type'],'tab'=>'corrective-action']) }}">
											<h6>{{ $checklistName ?? '' }} 
											</h6>
												<p class="text-muted mb-0">
												{{ \Illuminate\Support\Str::words($rejectedRegionData->rejected_region ?? '', 30, '...') }}
												</p>
												<p class="text-muted mb-0">
												<i class="fa fa-clock">  {{ Carbon::parse($rejectedRegionData->created_at)->format('d M Y, h:i A') }}</i>
												</p>
												<p class="text-muted mb-0">
												<i class="fa fa-map-marker"></i> {{ $location_name ?? ''}}
												@if($result['inspector_action']==1)
												<button type="button" class="btn btn-outline-success"  style="pointer-events: none; background-color: transparent; border-color: #198754; color: #198754; padding: 2px 6px; font-size: 12px; line-height: 1;">Approved Inspector</button>
												@elseif($result['los_action']==1)
												<button type="button" class="btn btn-outline-success"  style="pointer-events: none; background-color: transparent; border-color: #198754; color: #198754; padding: 2px 6px; font-size: 12px; line-height: 1;">Approved LOS</button>
												@else
													<button type="button" class="btn btn-warning" style="pointer-events: none; background-color: transparent; border-color: #ffc107; color: #ffc107; padding: 2px 6px; font-size: 12px; line-height: 1;">Pending</button>
												@endif
												</p>
											</a>
										</div>
									</div>
									@endif
								@endif
							@endforeach
							</span>
							
							<div class="load-more-plan" id="showloadplan">Load more</div>
								<input type="hidden" value="{{ $moreloadplan ?? '' }}" id="moreloadplan">
								
							</div>
							<div class="tab-pane" id="approved_by_inspector_tab">
							<span id="appCompletedDiv">
							@foreach($approvedCompletedArray as $result)
								@if($result['inspector_action'] == 1 && $result['los_action'] == 1)
									@php 
								        $m++;
										$arrSubchecklist = [];
										$checklistData = App\Models\Checklist::where('id', $result['checklist_id'])->first();
										
										/*$subchecklistData = App\Models\Subchecklist::where('id', $result['checklist_id'])->first();*/
										
										/*$checklistName = $checklistData ? $checklistData->name : ($subchecklistData ?  $subchecklistData->name : '');*/
										
										$checklistName = $checklistData ? $checklistData->name : '';
										
										$rejectedRegionData = $result['type'] == 'checklist'
										? App\Models\Task_list_checklists::where('task_list_id',$result['task_id'])->where('checklist_id', $result['checklist_id'])->first()
										: App\Models\Task_list_subchecklists::where('task_list_id',$result['task_id'])->where('task_list_checklist_id', $result['checklist_id'])->first();

										if (isset($result['image']) && $result['image'] != '') {
											$images = $result['type'] == 'checklist'
												? url('uploads/reject-files/' . $result['image'])
												: url('uploads/reject-files/subchecklist/' . $result['image']);
										} else {
											$images = url('images/noimages/noimage_region.png');
										}
										
										//$images = url('images/noimages/noimage_region.png');
										
										$userData = App\Models\Task_lists::with('get_user')->where('id', $result['task_id'])->first();
										
										if($result['type'] == 'subchecklist')
										{
											$subchecklistData = App\Models\Task_list_subchecklists::where('task_list_id',$result['task_id'])->where('task_list_checklist_id',$result['checklist_id'])->where('subchecklist_id',$result['subchecklist_id'])->first();
											if($subchecklistData)
											{
												//foreach($subchecklistData as $subcheck)
												//{
													$subchecklistName = App\Models\Subchecklist::where('id', $subchecklistData->subchecklist_id)->first();
													
													$filedata = App\Models\Task_list_subchecklist_rejected_files::where('task_list_subchecklist_id', $subchecklistData->id)->first();
													
													$images = $filedata && $filedata->file != '' ? url('uploads/reject-files/subchecklist/' . $filedata->file) : url('images/noimages/noimage_region.png');
													  
													
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
												
												if (!empty($result['image'])) {
													$route = route('lo-subchecklist-completed-approved-view', [
														'location_id' => $location_id,
														'task_id' => $result['task_id'],
														'checklist_id' => $result['checklist_id'],
														'subchecklist_id' => $val['subchecklist_id'],
														'type' => $result['type'],
														'tab' => 'corrective-action'
													]);
													$class = '';
												} else {
													$route = "javascript:void(0)";
													$class = 'list-approved-filter';
												}
											@endphp
										<div class="d-flex mb-3 task {{ $class }}">
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
												@if($val!='')
													-> {{$val['name'] ?? ''}}
												@endif
												</h6>
													<p class="text-muted mb-0">
													{{ \Illuminate\Support\Str::words($rejectedRegionData->rejected_region ?? '', 30, '...') }}
													</p>
													<p class="text-muted mb-0">
													<i class="fa fa-clock">  {{ Carbon::parse($rejectedRegionData->created_at)->format('d M Y, h:i A') }}
														  <button type="button" class="btn btn-outline-success ms-2"  style="pointer-events: none; background-color: transparent; border-color: #198754; color: #198754; padding: 2px 6px; font-size: 12px; line-height: 1;margin-left: 8px;">By {{ $userData->get_user->name ?? ''}}</button></i>
													</p>
													<p class="text-muted mb-0">
													<i class="fa fa-map-marker"></i> {{ $location_name ?? ''}}
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
											
											if (!empty($result['image'])) {
													$route = route('lo-checklist-completed-approved-view',['location_id'=>$location_id,'task_id'=>$result['task_id'], 'checklist_id'=> $result['checklist_id'],'type' => $result['type'],'tab'=>'corrective-action']);
													$class = '';
												} else {
													$route = "javascript:void(0)";
													$class = 'list-approved-filter';
												}
										@endphp
										<div class="d-flex mb-3 task {{ $class }}">
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
												<p class="text-muted mb-0">
												<i class="fa fa-clock">  {{ Carbon::parse($rejectedRegionData->created_at)->format('d M Y, h:i A') }}  <button type="button" class="btn btn-outline-success ms-2"  style="pointer-events: none; background-color: transparent; border-color: #198754; color: #198754; padding: 2px 6px; font-size: 12px; line-height: 1; margin-left: 8px;">By {{ $userData->get_user->name ?? ''}}</button></i>
												</p>
												<p class="text-muted mb-0">
												<i class="fa fa-map-marker"></i> {{ $location_name ?? ''}}
												</p>
											</a>
										</div>
									</div>
									@endif
								@endif
							@endforeach
							</span>
							
							<div class="load-more-appr" id="showloadappr">Load more</div>
								<input type="hidden" value="{{ $moreloadappr ?? '' }}" id="moreloadappr">
							
							</div>
						</div>
						<div class="text-left" style="display:none" id="no_record"><strong><h3>No record found</h3></strong></div>
					</div>
				</div>
			</section>
		</div>
	</div>
	<input type="hidden" value="{{ $location_id ?? ''}}" id="location_id">
	<input type="hidden" value="{{ $task_id ?? ''}}" id="task_id">
	<input type="hidden" value="{{ $isactive ?? ''}}" id="isactive">
	
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
$(document).ready(function() {
	var isactive = $('#isactive').val();
	
	if(isactive == 0)
	{
		const selectedTab = localStorage.getItem('selectedTab');
		
		if (selectedTab) {
			$('a[href="' + selectedTab + '"]').tab('show');
			
			if(selectedTab == '#inprogress_tab')
			{
				var norecord_j = $('#norecord_j').val();
				if(norecord_j==0)
				{
					$('#no_record').show();
				}
			}
			
			if(selectedTab == '#completed_tab')
			{
				var norecord_k = $('#norecord_k').val();
				if(norecord_k==0)
				{
					$('#no_record').show();
				}
			}
			
			if(selectedTab == '#rejected_by_inspector_tab')
			{
				var norecord_l = $('#norecord_l').val();
				if(norecord_l==0)
				{
					$('#no_record').show();
				}
				
			}
			
			if(selectedTab == '#approved_by_inspector_tab')
			{
				var norecord_m = $('#norecord_m').val();
				if(norecord_m==0)
				{
					$('#no_record').show();
				}
			}
		}
	}
	
	
	if(isactive == 1)
	{
		$('a[href="#inprogress_tab"]').tab('show');
		$('#inprogress_tab').show();
		$('#isactive').val(0);
		
		var norecord_j = $('#norecord_j').val();
		if(norecord_j==0)
		{
			$('#no_record').show();
		}
	}
	
  
	/*const selectedTab = localStorage.getItem('selectedTab');
	if (selectedTab) {
        $('a[href="' + selectedTab + '"]').tab('show');
    }*/
	
	$(document).on('click','.correctiveAction, .completedtab, .rejectInspector, .approvedByInspector', function(){
		var location_id = $('#location_id').val();
		var task_id = $('#task_id').val();
		$('#isactive').val(0);
		const tabId = $(this).attr('href');
		
		if(tabId == '#correctiveAction')
		{
			$('.tab-pane').removeClass('active show');
			//$('#correctiveAction').addClass('active show');
			$('.nav-tabs .nav-link').removeClass('active');
			//$('.nav-tabs .nav-link[href="#correctiveAction"]').addClass('active');
			
		}
		
		if(tabId == '#completedtab')
		{
			$('.tab-pane').removeClass('active show');
			//$('#completedtab').addClass('active show');
			$('.nav-tabs .nav-link').removeClass('active');
			//$('.nav-tabs .nav-link[href="#completedtab"]').addClass('active');
			
		}
		
		if(tabId == '#rejectInspector')
		{
			$('.tab-pane').removeClass('active show');
			//$('#rejectInspector').addClass('active show');
			$('.nav-tabs .nav-link').removeClass('active');
			//$('.nav-tabs .nav-link[href="#rejectInspector"]').addClass('active');
			
		}
		
		if(tabId == '#approvedByInspector')
		{
			$('.tab-pane').removeClass('active show');
			//$('#approvedByInspector').addClass('active show');
			$('.nav-tabs .nav-link').removeClass('active');
			//$('.nav-tabs .nav-link[href="#approvedByInspector"]').addClass('active');
			
		}
		
		
		localStorage.setItem('selectedTab', tabId);
		const refreshUrl = "{{ url('lo-task-status/LOCATION_ID/ISACTIVE') }}";
		const redirectUrl = refreshUrl.replace('LOCATION_ID', location_id).replace('ISACTIVE', 0);
		window.location.href = redirectUrl;
	});
	
	const scrollWrapper = $('#tabScrollWrapper');
    $('#scrollLeft').click(function () {
        scrollWrapper.animate({ scrollLeft: scrollWrapper.scrollLeft() - 150 }, 300);
    });

    $('#scrollRight').click(function () {
        scrollWrapper.animate({ scrollLeft: scrollWrapper.scrollLeft() + 150 }, 300);
    });
	
	
	var loActionSubmited = localStorage.getItem('loActionSubmited');
	var loPlanSubmited = localStorage.getItem('loPlanSubmited');
	var loFinalSubmited = localStorage.getItem('loFinalSubmited');
	if(loActionSubmited == 1)
	{
		$('.corrective-message').html('&nbsp;&nbsp;<i class="fa fa-check"></i>&nbsp;&nbsp;Corrective action submitted&nbsp;&nbsp;&nbsp;').fadeIn().delay(3000).fadeOut();
		
		localStorage.removeItem('loActionSubmited');
	}
	
	if(loPlanSubmited == 1)
	{
		$('.corrective-message').html('&nbsp;&nbsp;<i class="fa fa-check"></i>&nbsp;&nbsp;Corrective plan submitted&nbsp;&nbsp;&nbsp;').fadeIn().delay(3000).fadeOut();
		
		localStorage.removeItem('loPlanSubmited');
	}
	
	if(loFinalSubmited == 1)
	{
		$('.corrective-message').html('&nbsp;&nbsp;<i class="fa fa-check"></i>&nbsp;&nbsp;Corrective final round submitted&nbsp;&nbsp;').fadeIn().delay(3000).fadeOut();
		localStorage.removeItem('loFinalSubmited');
	}
	
	$(document).on('click', '.load-more-needed', function(){
		var location_id = $('#location_id').val();
		var moreload = $("#moreloadneeded").val();
		var tab = 'correctiveneeded';
		//alert(moreload);
		var URL = "{{ route('lo-load-more-data') }}";
		$.ajax({
			url: URL,
			type: "POST",
			data: {tab:tab, location_id:location_id, moreload:moreload, _token: csrfToken},
			dataType: 'json',
			success: function(response) {
				//alert(response.remain);
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
		});
		
	});
	
	$(document).on('click', '.load-more-action', function(){
		var location_id = $('#location_id').val();
		var moreload = $("#moreloadaction").val();
		var tab = 'correctiveaction';
		//alert(moreload);
		var URL = "{{ route('lo-load-more-data') }}";
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
		});
		
	});
	
	$(document).on('click', '.load-more-plan', function(){
		var location_id = $('#location_id').val();
		var moreload = $("#moreloadplan").val();
		var tab = 'correctiveplan';
		//alert(moreload);
		var URL = "{{ route('lo-load-more-data') }}";
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
		});
		
	});
	
	$(document).on('click', '.load-more-appr', function(){
		var location_id = $('#location_id').val();
		var moreload = $("#moreloadappr").val();
		var tab = 'correctiveapproved';
		//alert(moreload);
		var URL = "{{ route('lo-load-more-data') }}";
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
		});
		
	});
	
	// -- load more button first time check -- 
	var action_show = $('#action_show').val();
	var totalNeeded = $('#totalNeeded').val();
	var totalAction = $('#totalAction').val();
	var totalPlan = $('#totalPlan').val();
	var totalapprcompleted = $('#totalapprcompleted').val();
	//alert(totalNeeded);alert(action_show);
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

