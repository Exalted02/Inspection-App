@extends('layouts.app')
@section('content')
@php 
//echo "<pre>";print_r($categoryData);die;
//echo "<pre>";print_r($correctiveAction);die;
//echo "<pre>";print_r($correctiveNeddedArray);die;
//echo "<pre>";print_r($approvedCompletedArray);die;


if(auth()->user()->user_type == 2)
{
	$path = 'locationowner';
	$user_type_name = 'Location owner';
}
$backgroung_img = url('images/noimages/noimage_background_avatar.png');
$profile_img = url('images/noimages/noimage_avatar.png');
if(!empty($userdata->background_image))
{
	$backgroung_img = url('uploads/profile/' .$userdata->id .'/'. $path .'/'. $userdata->background_image);
}
//echo $path; die;
if(!empty($userdata->profile_image))
{
	$profile_img = url('uploads/profile/' .$userdata->id .'/'. $path  . '/'. $userdata->profile_image);
}

//echo $profile_img;die;
$locationIds = App\Models\Users_location::where('user_id', auth()->user()->id)->pluck('location_id')->toArray();

//echo "<pre>";print_r($locationIds);die;
$correctiveActionArray =[];

$total_count = 0;
@endphp
	<!-- =-=-=-=-=-=-= Breadcrumb =-=-=-=-=-=-= -->
	
	{{--<div class="profile-card">
		<div class="profile-banner" style="background-image: url( '{{url('uploads/profile/' .$userdata->id .'/inspector/'. $userdata->background_image )}} ')"></div>
			<div class="profile-info container">
				<img class="profile-avatar" src="{{ url('uploads/profile/' .$userdata->id .'/inspector/'. $userdata->profile_image)}}" alt="Profile Picture">
				<div class="width-100 ml-10px">
					<h2 class="profile-name">{{ $userdata->name ?? ''}}</h2>
					<p class="profile-description">
						{{$user_type_name ?? ''}} at {{ $userdata->get_company->company_name ?? '' }},
							{{ $location_name ?? '' }}
							
					</p>
				</div>
			</div>
		</div>--}}
    <!-- =-=-=-=-=-=-= Breadcrumb End =-=-=-=-=-=-= --> 
    <!-- =-=-=-=-=-=-= Main Content Area =-=-=-=-=-=-= -->
	<div class="container checklist">
	<div class="row">
	    <div class="col-md-8 col-sm-8 col-xs-10 profile-name">
		   Inspection Closure
	    </div>
		<div class="col-md-4 col-sm-4 col-xs-2 location-count" id="show_count">
	    </div>
	 </div>
		
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
					@foreach($locationIds as $locations)
						@php 
						$locationData = App\Models\Manage_location::where('id', $locations)->first();
						
						$taskListIds = App\Models\Task_lists::where('location_id', $locations)->pluck('id');
						// echo "<pre>";print_r($taskListIds);exit;
						
						$categoryIds = App\Models\Task_list_subcategories::whereIn('task_list_id', $taskListIds)->pluck('task_list_category_id');
						
						$submit_task_id = App\Models\Task_list_subcategories::whereIn('task_list_category_id', $categoryIds)->pluck('task_list_id')->toArray();
						
						$company_id = App\Models\User::where('user_type', 2)->where('id', auth()->user()->id)->first()->company_name;
		
						$users_location = App\Models\Users_location::where('company_id', $company_id)->where('user_type', 2)->where('user_id', auth()->user()->id)->where('location_id', $locations)->first();
						$primary_owner = $users_location ? $users_location->primary_owner : '';
						
						$excludedChecklistPairs = [];
						if($primary_owner == 1)
						{
							$excludedChecklistPairs = App\Models\Task_list_corrective_action::whereNotIn('rejected_repeated', [0])
								->whereIn('task_list_id', $taskListIds)
								->where('lo_id','!=',auth()->user()->id) //new 24-09-2025
								->orWhereIn('lo_direct_approve', [0, 1]) // new 25-09-2025
								//->where('lo_corrective_action_plan', null)
								->where(function ($q) {
									$q->where(function ($q) {
										$q->where('inspector_action', 0)->where('los_action', 0);
									})->orWhere(function ($q) {
										$q->where('inspector_action', 1)->where('los_action', 0);
									})->orWhere(function ($q) {
										$q->where('inspector_action', 0)->where('los_action', 1);
									})->orWhere(function ($q) {
										$q->where('inspector_action', 1)->where('los_action', 1);
									});
								})
								->whereNotNull('checklist_id')
								->get(['task_list_id', 'checklist_id'])
								->map(function ($item) {
									return $item->task_list_id . '-' . $item->checklist_id;
								})
								->toArray();
								
								$correctiveChecklistIds = DB::table('task_list_checklists')
								->where('approve', 0)
								->whereIn('task_list_id', $taskListIds)
								->whereIn('category_id', $categoryIds)
								->get(['task_list_id', 'checklist_id'])
								->filter(function ($item) use ($excludedChecklistPairs) {
									$pairKey = $item->task_list_id . '-' . $item->checklist_id;
									return !in_array($pairKey, $excludedChecklistPairs);
								})
								->toArray();
						}
						else{
							$excludedChecklistPairs = App\Models\Task_list_corrective_action::whereNotIn('rejected_repeated', [0])
								->whereIn('task_list_id', $taskListIds)
								->where('lo_id',auth()->user()->id) //new 24-09-2025
								->where('tab_no', 1) //new 26-09-2025
								//->orWhereIn('lo_direct_approve', [0, 1]) // new 25-09-2025
								->where(function ($q) {
									$q->where(function ($q) {
										$q->where('inspector_action', 0)->where('los_action', 0);
									})->orWhere(function ($q) {
										$q->where('inspector_action', 2)->where('los_action', 2);
									});
									/*->orWhere(function ($q) {
										$q->where('inspector_action', 0)->where('los_action', 1);
									})->orWhere(function ($q) {
										$q->where('inspector_action', 1)->where('los_action', 1);
									});*/
								})
								->whereNotNull('checklist_id')
								->whereNull('subchecklist_id')
								->get(['task_list_id', 'checklist_id'])
								->map(function ($item) {
									return $item->task_list_id . '-' . $item->checklist_id;
								})
								->toArray();
								
								$correctiveChecklistIds = DB::table('task_list_checklists')
								->where('approve', 0)
								->whereIn('task_list_id', $taskListIds)
								->whereIn('category_id', $categoryIds)
								->get(['task_list_id', 'checklist_id'])
								->filter(function ($item) use ($excludedChecklistPairs) {
									$pairKey = $item->task_list_id . '-' . $item->checklist_id;
									
									$excludedKeys = array_map(function($pair) {
											$parts = explode('-', $pair);
											return $parts[0] . '-' . $parts[1];
										}, $excludedChecklistPairs);
										
									return in_array($pairKey, $excludedKeys);
									
									//return in_array($pairKey, $excludedChecklistPairs);
								})
								->toArray();
						}
						$excludedSubChecklistPairs = [];
		
						if($primary_owner == 1)
						{
							$excludedSubChecklistPairs = App\Models\Task_list_corrective_action::whereNotIn('rejected_repeated', [0])
								->whereIn('task_list_id', $taskListIds)
								->where('lo_id', '!=' ,auth()->user()->id) //new 24-09-2025
								->orWhereIn('lo_direct_approve', [0, 1]) // new 25-09-2025
								->where(function ($q) {
									$q->where(function ($q) {
										$q->where('inspector_action', 0)->where('los_action', 0);
									})->orWhere(function ($q) {
										$q->where('inspector_action', 1)->where('los_action', 0);
									})->orWhere(function ($q) {
										$q->where('inspector_action', 0)->where('los_action', 1);
									})->orWhere(function ($q) {
										$q->where('inspector_action', 1)->where('los_action', 1);
									});
								})
								->whereNotNull('subchecklist_id')
								->get(['task_list_id', 'checklist_id', 'subchecklist_id'])
								->map(function ($item) {
									return $item->task_list_id . '-' . $item->checklist_id . '-' . $item->subchecklist_id;
								})
								->toArray();
								
								$correctiveSubChecklistIds = DB::table('task_list_subchecklists')
									->where('approve', 0)
									->whereIn('task_list_id', $taskListIds)
									->whereIn('category_id', $categoryIds)
									->get(['task_list_id', 'task_list_checklist_id', 'subchecklist_id'])
									->filter(function ($item) use ($excludedSubChecklistPairs) {
										$pairKey = $item->task_list_id . '-' . $item->task_list_checklist_id . '-' . $item->subchecklist_id;
										return !in_array($pairKey, $excludedSubChecklistPairs);
									})
									->map(function ($item) {
										return (object)[
											'task_list_id' => $item->task_list_id,
											'task_list_checklist_id' => $item->task_list_checklist_id,
											'subchecklist_id' => $item->subchecklist_id,
										];
									})
									->values()
									->toArray();
						}
						else{
							
							$excludedSubChecklistPairs = App\Models\Task_list_corrective_action::whereNotIn('rejected_repeated', [0])
								->whereIn('task_list_id', $taskListIds)
								->where('lo_id', auth()->user()->id) //new 24-09-2025
								->where('tab_no', 1) //new 26-09-2025
								->where(function ($q) {
									$q->where(function ($q) {
										$q->where('inspector_action', 0)->where('los_action', 0);
									})->orWhere(function ($q) {
										$q->where('inspector_action', 2)->where('los_action', 2);
									});
								})
								->whereNotNull('subchecklist_id')
								->get(['task_list_id', 'checklist_id', 'subchecklist_id'])
								->map(function ($item) {
									return $item->task_list_id . '-' . $item->checklist_id . '-' . $item->subchecklist_id;
								})
								->toArray();
								
								$correctiveSubChecklistIds = DB::table('task_list_subchecklists')
									->where('approve', 0)
									->whereIn('task_list_id', $taskListIds)
									->whereIn('category_id', $categoryIds)
									->get(['task_list_id', 'task_list_checklist_id', 'subchecklist_id'])
									->filter(function ($item) use ($excludedSubChecklistPairs) {
										$pairKey = $item->task_list_id . '-' . $item->task_list_checklist_id . '-' . $item->subchecklist_id;
										
										$excludedKeys = array_map(function($pair) {
											$parts = explode('-', $pair);
											return $parts[0] . '-' . $parts[1] . '-' . $parts[2];
										}, $excludedSubChecklistPairs);
										
										return in_array($pairKey, $excludedKeys);
										//return !in_array($pairKey, $excludedSubChecklistPairs);
									})
									->map(function ($item) {
										return (object)[
											'task_list_id' => $item->task_list_id,
											'task_list_checklist_id' => $item->task_list_checklist_id,
											'subchecklist_id' => $item->subchecklist_id,
										];
									})
									->values()
									->toArray();
						}
						$correctiveneeded = DB::table(function ($query) use (
							$taskListIds,
							$categoryIds,
							$submit_task_id,
							$correctiveChecklistIds,
							$correctiveSubChecklistIds
						) {
							// Subquery 1: From task_list_checklists
							$baseQuery = DB::table('task_list_checklists')
								->select(
									'id',
									'checklist_id as checklist_id',
									DB::raw("'checklist' as type"),
									'task_list_id',
									'category_id',
									'approve',
									'created_at',
									'updated_at',
									DB::raw('NULL as subchecklist_id'),
									DB::raw('NULL as task_list_checklist_id')
								)
								->whereIn('category_id', $categoryIds)
								->whereIn('task_list_id', $submit_task_id)
								->where('approve', 0)
								->where(function ($query) use ($correctiveChecklistIds) {
									if (!empty($correctiveChecklistIds)) {
										$query->where(function ($q) use ($correctiveChecklistIds) {
											foreach ($correctiveChecklistIds as $item) {
												$taskListId = is_array($item) ? $item['task_list_id'] : $item->task_list_id;
												$checklistId = is_array($item) ? $item['checklist_id'] : $item->checklist_id;

												$q->orWhere(function ($subQ) use ($taskListId, $checklistId) {
													$subQ->where('task_list_id', $taskListId)
														 ->where('checklist_id', $checklistId);
												});
											}
										});
									} else {
										$query->whereRaw('1 = 0'); // Prevent matching any row
									}
								});

							// Subquery 2: From task_list_subchecklists
							$unionQuery = DB::table('task_list_subchecklists')
								->select(
									'id',
									'subchecklist_id as item_id',
									DB::raw("'subchecklist' as type"),
									'task_list_id',
									'category_id',
									'approve',
									'created_at',
									'updated_at',
									'subchecklist_id',
									'task_list_checklist_id'
								)
								//->whereIn('task_list_id', $taskListIds)
								->whereIn('task_list_id', $submit_task_id)
								->whereIn('category_id', $categoryIds)
								->where('approve', 0)
								->where(function ($query) use ($correctiveSubChecklistIds) {
									if (!empty($correctiveSubChecklistIds)) {
										foreach ($correctiveSubChecklistIds as $item) {
											$taskListId = is_array($item) ? $item['task_list_id'] : $item->task_list_id;
											$subchecklistId = is_array($item) ? $item['subchecklist_id'] : $item->subchecklist_id;

											$query->orWhere(function ($subQ) use ($taskListId, $subchecklistId) {
												$subQ->where('task_list_id', $taskListId)
													 ->where('subchecklist_id', $subchecklistId);
											});
										}
									} else {
										$query->whereRaw('1 = 0');
									}
								});

							$query->fromSub($baseQuery->unionAll($unionQuery), 'combined');
						}, 'combined')
							//->orderByDesc('updated_at')
							->orderBy('updated_at', 'asc')
							// ->offset($offset)
							// ->limit($limit)
							->get();
						$correctiveNeddedArray = [];
						foreach($correctiveneeded as $needed)
						{
							if($needed->type == 'checklist')
							{
								$task_list_checklist_corrective_needed = App\Models\Task_list_corrective_action::where('task_list_id', $needed->task_list_id)->where('checklist_id', $needed->checklist_id)->first();
								
								$checklistData = App\Models\Task_list_checklists::where('checklist_id', $needed->checklist_id)->where('task_list_id', $needed->task_list_id)->first();
								$id = $checklistData ? $checklistData->id : '';
									
								if(!$task_list_checklist_corrective_needed)
								{
									$isfiles = '';
									$images = '';
									
									$isfiles = App\Models\Task_list_checklist_rejected_files::where('task_list_checklist_id', $id)->first();
									
									$images = $isfiles ? $isfiles->file  : '';
								
									$correctiveNeddedArray[] = [
										'type' => 'checklist',
										'task_id' => $needed->task_list_id,
										'checklist_id' => $needed->checklist_id,
										'rejected_region' => $checklistData->rejected_region,
										'image' => $images,
										'created_at' => $needed->created_at,
										'updated_at' => $needed->updated_at,
										'inspector_action' => '',
										'los_action' => '',
										'rejected_status' => '',
									];
								}
								else
								{
									$isfiles = '';
									$images = '';
									$isfiles = App\Models\Task_list_checklist_rejected_files::where('task_list_checklist_id', $id)->first();
									
									$images = $isfiles ? $isfiles->file  : '';
									$correctiveNeddedArray[] = [
										'type' => 'checklist',
										'task_id' => $needed->task_list_id,
										'checklist_id' => $needed->checklist_id,
										'rejected_region' => $checklistData->rejected_region,
										'image' => $images,
										'created_at' => $needed->created_at,
										'updated_at' => $needed->updated_at,
										'inspector_action'=> $task_list_checklist_corrective_needed->inspector_action,
										'los_action'=> $task_list_checklist_corrective_needed->los_action,
										'rejected_status'=> $task_list_checklist_corrective_needed->rejected_status,
									];
								}
							}
							else if($needed->type == 'subchecklist')
							{
								$task_list_subchecklist_corrective_needed = App\Models\Task_list_corrective_action::where('task_list_id', $needed->task_list_id)->where('checklist_id', $needed->task_list_checklist_id)->where('subchecklist_id', $needed->subchecklist_id)->first();
											
								$subchecklistData = App\Models\Task_list_subchecklists::where('task_list_id', $needed->task_list_id)->where('task_list_checklist_id', $needed->task_list_checklist_id)->where('subchecklist_id',$needed->subchecklist_id)->first();
								$id = $subchecklistData ? $subchecklistData->id : '';
											
								if(!$task_list_subchecklist_corrective_needed)
								{
									$isSubChecklistfiles = '';
									$subChecklistimages = '';
									$isSubChecklistfiles = App\Models\Task_list_subchecklist_rejected_files::where('task_list_checklist_id', $id)->first();
									$subChecklistimages = $isSubChecklistfiles ? $isSubChecklistfiles->file  : '';
													
									$correctiveNeddedArray[] = [
										'type' => 'subchecklist',
										'task_id' => $needed->task_list_id,
										'checklist_id' => $needed->task_list_checklist_id,
										'subchecklist_id'=>$needed->subchecklist_id,
										'rejected_region' => $subchecklistData->rejected_region,
										'image' => $subChecklistimages,
										'created_at' => $needed->created_at,
										'updated_at' => $needed->updated_at,
										'inspector_action' => '',
										'los_action' => '',
										'rejected_status' => '',
									];
								}
								else
								{
									$isSubChecklistfiles = '';
									$subChecklistimages = '';
									$isSubChecklistfiles = App\Models\Task_list_subchecklist_rejected_files::where('task_list_checklist_id', $id)->first();
									$subChecklistimages = $isSubChecklistfiles ? $isSubChecklistfiles->file  : '';
									
									$correctiveNeddedArray[] = [
										'type' => 'subchecklist',
										'task_id' => $needed->task_list_id,
										'checklist_id' => $needed->task_list_checklist_id,
										'subchecklist_id'=>$needed->subchecklist_id,
										'rejected_region' => $subchecklistData->rejected_region,
										'image' => $subChecklistimages,
										'created_at' => $needed->created_at,
										'updated_at' => $needed->updated_at,
										'inspector_action'=> $task_list_subchecklist_corrective_needed->inspector_action,
										'los_action'=> $task_list_subchecklist_corrective_needed->los_action,
										'rejected_status'=> $task_list_subchecklist_corrective_needed->rejected_status,
									];
								}
							}
							
						}
						usort($correctiveNeddedArray, function ($a, $b) {
							return strtotime($b['created_at']) <=> strtotime($a['created_at']);
						});
						// echo "<pre>";print_r(count($correctiveNeddedArray));die;
						
						$total_count = $total_count + count($correctiveNeddedArray);
						
						@endphp
						
						@if(count($correctiveNeddedArray) > 0)
						<!-- Tabs -->
						<div class="row mt-2">
							<div class="col-md-8 col-sm-8 col-xs-10 location-name">{{ $locationData->location_name ?? '' }}
							</div>
							<div class="col-md-4 col-sm-4 col-xs-2 location-count">{{ count($correctiveNeddedArray) }}
							</div>
						</div>

							
						<!-- Tab panes -->
						<div class="row tab-content mt-2">
							<div class="col-md-8 col-sm-8 col-xs-12">
							@foreach($correctiveNeddedArray as $result)
								@php 
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
												
												$filedata = App\Models\Task_list_subchecklist_rejected_files::where('task_list_checklist_id', $subchecklistData->id)->first();
												
												$images = $filedata && $filedata->file != ''  ? url('uploads/reject-files/subchecklist/' . $filedata->file) : url('images/noimages/corrective-needed.png') ;
												
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
											@if($result['inspector_action'] == 2)
											<a href="{{ route('location-owner-subchecklist-rejected-question-reply',['task_id'=>$result['task_id'], 'checklist_id'=> $result['checklist_id'],'subchecklist_id'=>$result['subchecklist_id'],'type' => $result['type'] ]) }}">
											@else
											<a href="{{ route('location-owner-subchecklist-question-reply',['task_id'=>$result['task_id'], 'checklist_id'=> $result['checklist_id'],'subchecklist_id'=>$val['subchecklist_id'],'type' => $result['type'],'tab'=>'corrective-action', 'lid'=> $locations ]) }}">
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
												<i class="fa fa-clock"></i> {{ change_date_format($rejectedRegionData->created_at, 'Y-m-d H:i:s', 'd M Y, h.i A') }}
												</p>
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
												<a href="{{ route('location-owner-checklist-question-reply',['task_id'=>$result['task_id'], 'checklist_id'=> $result['checklist_id'],'type' => $result['type'],'tab'=>'corrective-action', 'lid'=> $locations]) }}">
												@endif
												<h6>{{ $checklistName ?? '' }} 
												</h6>
													<p class="text-muted mb-0">
													{{ \Illuminate\Support\Str::words($rejectedRegionData->rejected_region ?? '', 30, '...') }}
													</p>
													<p class="text-muted mb-0">
													<i class="fa fa-clock"></i> {{ change_date_format($rejectedRegionData->created_at, 'Y-m-d H:i:s', 'd M Y, h.i A') }}
													</p>
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
							</div>
						</div>
						@endif
						@endforeach
						<input type="hidden" value="{{ $total_count ?? 0}}" id="total_count">
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
	let total_count = $('#total_count').val();
	$('#show_count').html(total_count);
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

