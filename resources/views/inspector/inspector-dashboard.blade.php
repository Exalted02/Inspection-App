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
							
							$correctiveActionChecklistArray = [];
							
							$taskData = App\Models\Task_lists::where('inspector_id',  auth()->user()->id)->where('location_id', $locations->location_id)->get();
							$tasksArr = [];
							$locCatArr = [];
							$taskCnt = 0;
							if($taskData->isNotempty())
							{
								foreach($taskData as $val)
								{
									$ifTaskRxists = App\Models\Task_list_subcategories::where('task_list_id', $val->id)->exists();
									if($ifTaskRxists)
									{					
										$correctiveActions = App\Models\Task_list_corrective_action::where('task_list_id', $val->id)->get();
										
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
									}
									
									$locCatArr = App\Models\Task_location_categories::where('task_list_id', $val->id)->pluck('category_id')->toArray();
								
									//echo "<pre>";print_r($locCatArr);
									$taskCnt = 0;
									foreach($locCatArr as $cat)
									{
										$exists = App\Models\Task_list_subcategories::where('task_list_id', $val->id)->where('task_list_category_id', $cat)->exists();
										if(!$exists)
										{
											$taskCnt++;
										}
									}
								}
							}
							$countAction = 0;
							$countPlan = 0;
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
							
							/*App\Models\Task_lists::with('get_location')where('location_id', $locations->location_id)->where('inspector_id', auth()->user()->id)->first();*/
							
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

