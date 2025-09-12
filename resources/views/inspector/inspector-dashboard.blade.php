@extends('layouts.app')
@section('content')
@php 
use Illuminate\Support\Facades\DB;

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
	
	$company_id = App\Models\User::where('user_type', 2)->where('id', auth()->user()->id)->first()->company_name;
	
	$user_loc_data = App\Models\Users_location::where('user_id', auth()->user()->id)->where('company_id', $company_id)->where('user_type', 2)->where('notification_status', 1)->first();
	$notifi_status = $user_loc_data ? $user_loc_data->notification_status : '';
	
	$loc_id = $user_loc_data ? $user_loc_data->location_id : '';
	$loc_data = App\Models\Manage_location::where('id', $loc_id)->first();
	$loc_name = $loc_data ? $loc_data->location_name : '';
	
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

$city = '';
$state = '';
$country = '';
//$taskData = '';
@endphp
    <!-- =-=-=-=-=-=-= Breadcrumb =-=-=-=-=-=-= -->
	<div class="profile-card">
		<div class="profile-banner" style="background-image: url( '{{ $backgroung_img ?? '' }} ')">
		<div class="message-forward-bg">
		  <div class="center-fixed corrective-message-forward" style="display:none;">
		  </div>
		</div>
			{{--<div class="notification-message" style="display:none;"></div>--}}
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
							$correctiveNeddedChecklistArray = [];
							$correctiveNeddedSubchecklistArray = [];
							$countNedded = 0;
							
							$lacationData = App\Models\Manage_location::where('id',$locations->location_id)->first();
							$cityData = App\Models\Cities::where('id', $lacationData->city_id)->first();
							$city = $cityData ? $cityData->name : '';
							
							$stateData = App\Models\States::where('id', $lacationData->state_id)->first();
							
							$state = $stateData ? $stateData->name : '';
							
							$countryData = App\Models\Countries::where('id', $lacationData->country_id)->first();
							
							$country = $countryData ? $countryData->name : '';
							
							$loc_image = $lacationData && $lacationData->image != null ? url('uploads/location/' .$lacationData->image) : url('images/noimages/noimage_region.png');
							
							$total_task = App\Models\Task_lists::where('inspector_id',  auth()->user()->id)->where('location_id', $locations->location_id)->count();
							
							$correctiveActionChecklistArray = [];
							
							if(auth()->user()->user_type == 1)
							{
								$taskData = App\Models\Task_lists::where('inspector_id',  auth()->user()->id)->where('location_id', $locations->location_id)->get();
							}
							
							if(auth()->user()->user_type == 2)
							{
								$taskData = App\Models\Task_lists::where('location_id', $locations->location_id)->get();
							}
							
							if(auth()->user()->user_type == 3)
							{
								$taskData = App\Models\Task_lists::where('location_id', $locations->location_id)->get();
							}
							
							
							$tasksArr = [];
							$locCatArr = [];
							$taskCnt = 0;
							$categoriesArr = [];
							if($taskData->isNotempty())
							{
								foreach($taskData as $val)
								{
									$ifTaskRxists = App\Models\Task_list_subcategories::where('task_list_id', $val->id)->exists();
									if($ifTaskRxists)
									{	

										$categoriesArr = App\Models\Task_list_subcategories::where('task_list_id', $val->id)->pluck('task_list_category_id')->toArray();
										//echo "<pre>";print_r($categoriesArr);
										$correctiveActions = App\Models\Task_list_corrective_action::where('task_list_id', $val->id)->whereIn('category_id', $categoriesArr)->get();
										
										if($correctiveActions->isNotEmpty())
										{
											foreach($correctiveActions as $correctiveAction)
											{
												$type = '';
												$image = '';
												//$type = $correctiveAction->subchecklist_id == null ? 'checklist' : 'subchecklist';
												
												if($correctiveAction->subchecklist_id == null)
												{
													$type = 'checklist';
													
													$checklistFile = App\Models\Task_list_checklists::with('get_checklist_files')->where('task_list_id', $val->id)->where('checklist_id', $correctiveAction->checklist_id)->first();
													
													$image = $checklistFile && $checklistFile->get_checklist_files->isNotEmpty() ? $checklistFile->get_checklist_files->first()->file : null;
													
												}
												else
												{
													$type = 'subchecklist';
													
													$subChecklistFile = App\Models\Task_list_subchecklists::with('get_subchecklist_files')->where('task_list_id', $val->id)->where('task_list_checklist_id', $correctiveAction->checklist_id)->where('subchecklist_id', $correctiveAction->subchecklist_id)->first();
													
													$image = $subChecklistFile && $subChecklistFile->get_subchecklist_files->isNotEmpty() ? $subChecklistFile->get_subchecklist_files->first()->file : null;
												}
												
												$correctiveActionChecklistArray[] = [
													'type' => $type ,
													'task_id' => $val->id,
													'checklist_id' => $correctiveAction->checklist_id,
													'subchecklist_id' => $correctiveAction->subchecklist_id,
													'rejected_region' => $correctiveAction->lo_corrective_action_plan,
													'inspector_action' => $correctiveAction->inspector_action,
													'los_action' => $correctiveAction->los_action,
													'second_checked' => $correctiveAction->lo_corrective_action_plan_second_check,
													'lo_direct_approve' => $correctiveAction->lo_direct_approve,
													'image' => $image,
												];
											}
										}
										
										//- corrective needed checklist-
										$categoriesChecklistArr = [];
										$categoriesChecklistArr = App\Models\Task_list_subcategories::where('task_list_id', $val->id)->pluck('task_list_category_id')->toArray();
										// checklist and  respective files approve= 0 or 1 
										$taskChklist = App\Models\Task_list_checklists::where('task_list_id', $val->id)->whereIn('category_id', $categoriesChecklistArr)->get();
										if($taskChklist->isNotEmpty())
										{
											foreach($taskChklist as $task)
											{
												
												$task_list_checklist_corrective_needed = App\Models\Task_list_corrective_action::where('task_list_id', $val->id)
												->where('checklist_id', $task->checklist_id)
												->first();
												if($task->approve == 0)
												{
													if(!$task_list_checklist_corrective_needed)
													{								
														$isfiles = '';
														$images = '';
														$isfiles = App\Models\Task_list_checklist_rejected_files::where('task_list_checklist_id', $task->id)->first();
														
														$images = $isfiles ? $isfiles->file  : '';
															$correctiveNeddedChecklistArray[] = [
																																																										                                                      'type'=>'checklist',
																																																						'task_id' => $val->id,
																																																						'checklist_id' => $task->checklist_id,
																																																						'rejected_region' => $task->rejected_region,
																																																						'image' => $images,
																																																						'inspector_action' => '',
																																																						'los_action' => '',
																];
													}
													else
													{
														// newimplement
														$isfiles = '';
														$images = '';
														$isfiles = App\Models\Task_list_checklist_rejected_files::where('task_list_checklist_id', $task->id)->first();
														$images = $isfiles ? $isfiles->file  : '';
														$correctiveNeddedChecklistArray[] = [
															'type' => 'checklist',
															'task_id' => $val->id,
															'checklist_id' => $task->checklist_id,
															'rejected_region' => $task->rejected_region,
															'image' => $images,
															'inspector_action'=> $task_list_checklist_corrective_needed->inspector_action,
															'los_action'=> $task_list_checklist_corrective_needed->los_action,
														];
														
													}
													
												}
												
											}
										}
										
										// subchecklist and respective files
					
										$categoriesSubChecklistArr = [];
										$categoriesSubChecklistArr = App\Models\Task_list_subcategories::where('task_list_id', $val->id)->pluck('task_list_category_id')->toArray();
										
										$taskSubChklist = App\Models\Task_list_subchecklists::where('task_list_id', $val->id)->whereIn('category_id', $categoriesSubChecklistArr)->get();
										if($taskSubChklist->isNotEmpty())
										{
											foreach($taskSubChklist as $subtask)
											{
												$task_list_subchecklist_corrective_needed = App\Models\Task_list_corrective_action::where('task_list_id', $val->id)
												->where('checklist_id', $subtask->task_list_checklist_id)
												->where('subchecklist_id', $subtask->subchecklist_id)
												->first();
												
												if($subtask->approve == 0)
												{
													if(!$task_list_subchecklist_corrective_needed)
													{
														$isSubChecklistfiles = '';
														$subChecklistimages = '';
														$isSubChecklistfiles = App\Models\Task_list_subchecklist_rejected_files::where('task_list_subchecklist_id', $subtask->id)->first();
														
														$subChecklistimages = $isSubChecklistfiles ? $isSubChecklistfiles->file  : '';
														$correctiveNeddedSubchecklistArray[] = [
																	'type' => 'subchecklist',
																	'task_id' => $val->id,
																	'checklist_id' => $subtask->task_list_checklist_id,
																	'subchecklist_id'=>$subtask->subchecklist_id,
																	'rejected_region' => $subtask->rejected_region,
																	'image' => $subChecklistimages,
																	'inspector_action' => '',
																	'los_action' => '',
																];
													}
													else
													{
														$isSubChecklistfiles = '';
														$subChecklistimages = '';
														$isSubChecklistfiles = App\Models\Task_list_subchecklist_rejected_files::where('task_list_subchecklist_id', $subtask->id)->first();
														
														$subChecklistimages = $isSubChecklistfiles ? $isSubChecklistfiles->file  : '';
														
														//  new implement
														$correctiveNeddedSubchecklistArray[] = [
																'type' => 'subchecklist',
																'task_id' => $val->id,
																'checklist_id' => $subtask->task_list_checklist_id,
																'subchecklist_id'=>$subtask->subchecklist_id,
																'rejected_region' => $subtask->rejected_region,
																'image' => $subChecklistimages,
																'inspector_action'=> $task_list_subchecklist_corrective_needed->inspector_action,
																'los_action'=> $task_list_subchecklist_corrective_needed->los_action,
															];
														//----
														
													}
												}
											}
											
										}
									}
									
									//$taskStatus = $val->status == 0 ? '' : //($tasks->status == 1 ? 'Incomplete' : '');
									
									if(auth()->user()->user_type == 1)
									{
										if($val->status == 0 || $val->status == 1)
										{
											$taskCnt++;
										}
									}
									else{
										if($val->status == 1)
										{
											$taskCnt++;
										}
									}
									
									
								}
							}
							$countAction = 0;
							$countPlan = 0;
							//echo "<pre>";print_r($correctiveActionChecklistArray);
							foreach($correctiveActionChecklistArray as $result)
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
							
							// if task not checklist submit then count pending
							
							$allTaskLocationWise = App\Models\Task_lists::where('inspector_id',  auth()->user()->id)->where('location_id', $locations->location_id)->get();
							if($allTaskLocationWise->isNotempty())
							{
								foreach($allTaskLocationWise as $val)
								{
									$existsInChecklists = \App\Models\Task_list_checklists::where('task_list_id', $val->id)->exists();
									$existsInSubChecklists = \App\Models\Task_list_subchecklists::where('task_list_id', $val->id)->exists();
									
									if($existsInChecklists || $existsInSubChecklists)				{	

										// Get category_ids from both tables
										$checklistCategories = \App\Models\Task_list_checklists::where('task_list_id', $val->id)
											->pluck('category_id')
											->toArray();

										$subChecklistCategories = \App\Models\Task_list_subchecklists::where('task_list_id', $val->id)
											->pluck('category_id')
											->toArray();

										// Merge and get unique category_ids
										$allCategories = array_unique(array_merge($checklistCategories, $subChecklistCategories));
										//echo "<pre>";print_r($allCategories);
										
										foreach($allCategories as $cat)
										{
											$exists = App\Models\Task_list_subcategories::where('task_list_id', $val->id)->where('task_list_category_id', $cat)->exists();
											if(!$exists)
											{
												//$taskCnt++;
											}
										}
									}
								}
							}
							
							$correctiveNeeded = array_merge($correctiveNeddedChecklistArray,$correctiveNeddedSubchecklistArray);
							if(count($correctiveNeeded) > 0)
							{
								foreach($correctiveNeeded as $result)
								{
									if(($result['inspector_action']=='' && $result['los_action']=='') || ($result['inspector_action']== 2 && $result['los_action']==2))
									{
										$countNedded++;
									}
								}
							}
							//echo "<pre>";print_r($tasksArr);
						@endphp
                            <div class="col-md-4 col-xs-6 col-sm-6">
								<div class="category-grid-box-1">
								@if(auth()->user()->user_type == 1)
								<a title="" href="{{route('location-details', ['id' => $locations->location_id ])}}">
									<div class="image" style="background-image: url('{{ $loc_image }}');">
										<img alt="Test" src="{{ $loc_image  }}" class="img-responsive d-none">
										<div class="ribbon popular"></div>
										<div class="price-tag">
											<div class="price"><span>{{ $countAction + $countPlan + $taskCnt }} pending tasks</span></div>
										</div>
										{{--<div class="price-tag">
											<div class="price"><span>{{ $taskCnt + $countNedded }} pending tasks</span></div>
										</div>--}}
									</div>
									<div class="short-description-1 clearfix">
										<h3>{{ $lacationData->location_name ?? '' }}</h3>
									</a>
								@elseif(auth()->user()->user_type == 3)
									<a title="" href="{{route('los-task-status', ['id' => $locations->location_id, 'active'=>1])}}">
									<div class="image" style="background-image: url('{{ $loc_image }}');">
										<img alt="Test" src="{{ $loc_image  }}" class="img-responsive d-none">
										<div class="ribbon popular"></div>
										<div class="price-tag">
										<div class="price"><span>{{ $countAction + $countPlan + $taskCnt }}  pending tasks</span></div>
										</div>
										{{--<div class="price"><span>{{ $countNedded }}  pending tasks</span></div>--}}
										</div>
									</div>
									<div class="short-description-1 clearfix">
										<h3>{{ $lacationData->location_name ?? '' }}</h3>
									</a>
								
								@elseif(auth()->user()->user_type == 2)
									<a title="" href="{{ route('lo-task-status', ['id' => $locations->location_id, 'active'=>1 ])}}">
									<div class="image" style="background-image: url('{{ $loc_image }}');">
										<img alt="Test" src="{{ $loc_image  }}" class="img-responsive d-none">
										<div class="ribbon popular"></div>
										<div class="price-tag">
											<div class="price"><span>{{ $taskCnt + $countNedded }}  pending tasks</span></div>
										</div>
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
		<input type="hidden" id="notifi_status" value="{{ $notifi_status ?? '';}}">
		<input type="hidden" id="loc_name" value="{{ $loc_name ?? '';}}">
    </div>
@endsection 
@section('scripts')
<script>
$(document).ready(function() {
	var notifi_status = $('#notifi_status').val();
	
	//alert(notifi_status);
	if(notifi_status == 1)
	{
		var loc_name = $('#loc_name').val();
		
		$('.corrective-message-forward').html('<i class="fa fa-check"></i>You received a new location (' + loc_name + ')').fadeIn().delay(3000).fadeOut();
		
		var URL = "{{ route('lo-update-nofication-status') }}";
			$.ajax({
				url: URL,
				type: "POST",
				data: {_token: csrfToken},
				dataType: 'json',
				success: function(response) {
					//alert(response.html);
					
				},
				complete: function() {
					//$('.load-more-appr').html('Load more');
				}
			});
	}
});
</script>
@endsection

