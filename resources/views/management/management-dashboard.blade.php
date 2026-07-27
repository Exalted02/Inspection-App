@extends('layouts.app')
@section('content')
@php 
 use Carbon\Carbon;
 $startDate = $startDate; // 4 weeks ago
 $endDate = $endDate; //upto today
 
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
 
 
 //-----------06-10-2025---------------
		$location_ids = App\Models\Users_location::where('user_id', auth()->user()->id)->pluck('location_id')->toArray();
		//echo "<pre>";print_r($location_ids);die;
		
			$taskListIds = App\Models\Task_lists::whereIn('location_id', $location_ids)->whereBetween('created_at', [$startDate, $endDate])
					->pluck('id');
		


		//echo "<pre>";print_r($taskListIds);die;
		$categoryIds = App\Models\Task_list_subcategories::whereIn('task_list_id', $taskListIds)
					->pluck('task_list_category_id');

		$submit_task_id = App\Models\Task_list_subcategories::whereIn('task_list_category_id', $categoryIds)->pluck('task_list_id')->toArray();

		//----------------corrective needed ------------
		
		$excludedChecklistPairs = App\Models\Task_list_corrective_action::whereNotIn('rejected_repeated', [0])
			->whereIn('task_list_id', $taskListIds)
			->where('lo_id', auth()->user()->id) //add new 24-09-2025
			->orWhereIn('lo_direct_approve', [0, 1])
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
					
		$excludedSubChecklistPairs = App\Models\Task_list_corrective_action::whereNotIn('rejected_repeated', [0])
					->whereIn('task_list_id', $taskListIds)
					->where('los_id', auth()->user()->id) // add new 24-09-2025
					->orWhereIn('lo_direct_approve', [0, 1])
					//->orWhere('lo_direct_approve', 0)
					//->orWhere('lo_direct_approve', 1)
					/*->where(function ($q) {
						$q->where('lo_direct_approve', 0)
						  ->orWhere('lo_direct_approve', 1);
					})*/
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
					//->whereNotNull('checklist_id')
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
					->get(['task_list_id', 'subchecklist_id', 'task_list_checklist_id'])
					->filter(function ($item) use ($excludedSubChecklistPairs) {
						$pairKey = $item->task_list_id . '-' . $item->task_list_checklist_id . '-' . $item->subchecklist_id;
						return !in_array($pairKey, $excludedSubChecklistPairs);
					})
					->map(function ($item) {
						return (object)[
							'task_list_id' => $item->task_list_id,
							'subchecklist_id' => $item->subchecklist_id,
							'task_list_checklist_id' => $item->task_list_checklist_id,
						];
					})
					->values()
					->toArray();
		

		

		$correctiveNeededCount = DB::table(function ($query) use (
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

					// Merge both queries using unionAll
					$query->fromSub($baseQuery->unionAll($unionQuery), 'combined');
				}, 'combined')->count();
		//echo $correctiveNeededCount; die;
		//----------------corrective action ------------

		$correctiveActionCount = App\Models\Task_list_corrective_action::whereIn('lo_direct_approve', [1])->whereIn('task_list_id', $taskListIds)->whereIn('category_id', $categoryIds)
					->where(function ($q) {
						$q->where(function ($q) {
							$q->where('inspector_action', 0)->where('los_action', 1);
						})->orWhere(function ($q) {
							$q->where('inspector_action', 1)->where('los_action', 0);
						})->orWhere(function ($q) {
							$q->where('inspector_action', 0)->where('los_action', 0);
						});
					})->count();
		//----------- corrective plan ------------

		$correctivePlanCount = App\Models\Task_list_corrective_action::whereIn('lo_direct_approve', [0])->whereIn('task_list_id', $taskListIds)->whereIn('category_id', $categoryIds)
					->where(function ($q) {
						$q->where(function ($q) {
							$q->where('inspector_action', 0)->where('los_action', 1);
						})->orWhere(function ($q) {
							$q->where('inspector_action', 1)->where('los_action', 0);
						})->orWhere(function ($q) {
							$q->where('inspector_action', 0)->where('los_action', 0);
						});
					})->count();
					
		//----------- approved / completed ------------
		$subcategoriesTaskIds = App\Models\Task_list_subcategories::whereIn('task_list_category_id', $categoryIds)->pluck('task_list_id');

		$matchingTaskListIds = array_values(array_intersect($taskListIds->toArray(),  $subcategoriesTaskIds->toArray()));

		$correctiveChecklistApprIds = App\Models\Task_list_corrective_action::whereIn('task_list_id', $matchingTaskListIds)
				->where(function ($q) {
					$q->where(function ($q) {
						$q->where('inspector_action', 1)->where('los_action', 1);
					});
				})
				->whereNotNull('checklist_id')
				->get(['task_list_id', 'checklist_id'])
					->map(function ($item) {
						return $item->task_list_id . '-' . $item->checklist_id;
					})
				->toArray();
				
		$existingCorrectiveChecklistApprIds = DB::table('task_list_corrective_actions')
						->whereIn('task_list_id', $taskListIds)
						->whereNotNull('checklist_id')
						->pluck('checklist_id')->toArray();
						
		$checklistApprIds = DB::table('task_list_checklists')
						->where('approve', 1)
						->whereIn('task_list_id', $matchingTaskListIds)
						->whereIn('category_id', $categoryIds)
						//->whereNotIn('checklist_id', $existingCorrectiveChecklistApprIds)
						->get(['task_list_id', 'checklist_id'])
						->map(function ($item) {
							return $item->task_list_id . '-' . $item->checklist_id;
						 })
						->toArray();
						
		$correctiveApprChecklistIds = array_merge($correctiveChecklistApprIds,$checklistApprIds);

		//--
		$correctiveSubChecklistApprIds = App\Models\Task_list_corrective_action::whereIn('task_list_id', $matchingTaskListIds)
					->where(function ($q) {
						$q->where(function ($q) {
							$q->where('inspector_action', 1)->where('los_action', 1);
						});
					})
					->whereNotNull('subchecklist_id')
					->get(['task_list_id', 'checklist_id', 'subchecklist_id'])
					->map(function ($item) {
						return $item->task_list_id . '-' . $item->checklist_id . '-' . $item->subchecklist_id;
					})
					->toArray();
					
		$existingCorrectiveSubChecklistIds = DB::table('task_list_corrective_actions')
						->whereIn('task_list_id', $taskListIds)
						->whereNotNull('subchecklist_id')
						->pluck('subchecklist_id')->toArray();
						
		$subchecklistApprIds = DB::table('task_list_subchecklists')
					->where('approve', 1)
					->whereIn('task_list_id', $matchingTaskListIds)
					->whereIn('category_id', $categoryIds)
					->get(['task_list_id', 'task_list_checklist_id', 'subchecklist_id'])
					->map(function ($item) {
						return $item->task_list_id . '-' . $item->task_list_checklist_id . '-' . $item->subchecklist_id;
					 })
					->toArray();
					
		$correctiveApprSubChecklistIds = array_merge($correctiveSubChecklistApprIds,$subchecklistApprIds);

		if (!empty($correctiveApprChecklistIds)) {
					$baseChecklistQuery = DB::table('task_list_checklists')
						->select(
							'id',
							'checklist_id',
							DB::raw("'checklist' as type"),
							'task_list_id',
							'category_id',
							'approve',
							'rejected_region',
							'created_at',
							'updated_at',
							DB::raw('NULL as subchecklist_id'),
							DB::raw('NULL as task_list_checklist_id')
						)
						->where(function ($query) use ($correctiveApprChecklistIds) {
							foreach ($correctiveApprChecklistIds as $pair) {
								[$taskListId, $checklistId] = explode('-', $pair);
								$query->orWhere(function ($q) use ($taskListId, $checklistId) {
									$q->where('task_list_id', $taskListId)
									  ->where('checklist_id', $checklistId)
									  ->whereIn('approve', [0,1]);
								});
							}
						});
				} else {
					// Empty query (always false condition)
					$baseChecklistQuery = DB::table('task_list_checklists')
						->select(
							'id',
							'checklist_id',
							DB::raw("'checklist' as type"),
							'task_list_id',
							'category_id',
							'approve',
							'rejected_region',
							'created_at',
							'updated_at',
							DB::raw('NULL as subchecklist_id'),
							DB::raw('NULL as task_list_checklist_id')
						)
						->whereRaw('1=0');
				}
				
		// if subchecklist ids are not empty, union it
			if (!empty($correctiveApprSubChecklistIds)) {
				$subChecklistQuery = DB::table('task_list_subchecklists')
					->select(
						'id',
						DB::raw('NULL as checklist_id'),
						DB::raw("'subchecklist' as type"),
						'task_list_id',
						'category_id',
						'approve',
						'rejected_region',
						'created_at',
						'updated_at',
						'subchecklist_id',
						'task_list_checklist_id'
					)
					->where(function ($q) use ($correctiveApprSubChecklistIds) {
						foreach ($correctiveApprSubChecklistIds as $pair) {
							[$taskListId, $checklistId, $subchecklistId] = explode('-', $pair);
							$q->orWhere(function ($sub) use ($taskListId, $checklistId, $subchecklistId) {
								$sub->where('task_list_id', $taskListId)
									->where('task_list_checklist_id', $checklistId)
									->where('subchecklist_id', $subchecklistId)
									->whereIn('approve', [0,1]);
							});
						}
					});

				$baseChecklistQuery->unionAll($subChecklistQuery);
			}
			
		/*$correctiveApproved = DB::table(DB::raw("({$baseChecklistQuery->toSql()}) as combined"))
					->mergeBindings($baseChecklistQuery)
					->orderBy('updated_at', 'desc')
					->offset($offset)
					->limit($limit)
					->get();*/

		$correctiveApprovedCount = DB::table(DB::raw("({$baseChecklistQuery->toSql()}) as combined"))
					->mergeBindings($baseChecklistQuery)->count();
					
$tot_close_obs = 0;

$observations = 0;
$get_tasklist_checklist = App\Models\Task_list_checklists::where('approve', 0)->whereIn('task_list_id', $taskListIds)->get();
if($get_tasklist_checklist->count() > 0)
{
	$observations += $get_tasklist_checklist->count();
}
$get_tasklist_subchecklist = App\Models\Task_list_subchecklists::where('approve', 0)->whereIn('task_list_id', $taskListIds)->get();
if($get_tasklist_subchecklist->count() > 0)
{
	$observations += $get_tasklist_subchecklist->count();
}
@endphp

<style>
	.active-btn {
		background-color: #b1b2b3 !important;
		color: #fff !important;
	}
</style>
	<!-- =-=-=-=-=-=-= Breadcrumb =-=-=-=-=-=-= -->
	
	<div class="profile-card">
		<div class="profile-banner" style="background-image: url( '{{url('uploads/profile/' .$userdata->id .'/management/'. $userdata->background_image )}} ')"></div>
		<div class="profile-info container">
			<img class="profile-avatar" src="{{ url('uploads/profile/' .$userdata->id .'/management/'. $userdata->profile_image)}}" alt="Profile Picture">
			<div class="width-100 ml-10px">
				<h2 class="profile-name">{{ $userdata->name ?? ''}}</h2>
				<p class="profile-description">
					{{--Management at {{ $userdata->get_company->company_name ?? '' }},<br> {{ $location_name ?? '' }}--}}
					Management at {{ $userdata->get_company->company_name ?? '' }}
						
				</p>
			</div>
		</div>
	</div>
    <!-- =-=-=-=-=-=-= Breadcrumb End =-=-=-=-=-=-= --> 
    <div class="container">
		<h2 class="page-title">Welcome to your overview</h2>
		<div class="page-subtitle mb-3">Check out how your factory is performing</div>
		<div class="d-flex gap-4">
			<div class=" d-flex align-items-center">View by</div>
			<div class=""><button type="button" class="btn btn-outline-secondary weekly-list-show">Weekly</button>
			</div>
			<div class=""><button type="button" class="btn btn-outline-secondary monthly-list-show">Monthly</button>
			</div>
		</div>
		<div class="pt-2 pb-2">
			<div class="row flex-wrap d-flex">
				<div class="col-md-4 col-sm-4 col-xs-4 small-card-first d-flex">
					<div class="bg small-card">
						<div class="small-card-title">No. of inspections</div>
						<div class="small-card-counter">{{ $correctiveNeededCount+$correctiveActionCount+$correctivePlanCount+$correctiveApprovedCount }}</div>
							{{--<div class="small-card-counter">{{ $tot_inspection ?? ''}}</div>--}}
						<div class="small-card-counter-title">WEEKLY</div>
					</div>
				</div>
				<div class="col-md-4 col-sm-4 col-xs-4 small-card-second d-flex">
					<div class="bg small-card">
						<div class="small-card-title">No. of observations</div>
						<div class="small-card-counter"><span id="tot_no_of_obs1">{{$observations}}</span></div>
						<div class="small-card-counter-title">WEEKLY</div>
					</div>
				</div>
				<div class="col-md-4 col-sm-4 col-xs-4 small-card-third d-flex">
					<div class="bg small-card">
						<div class="small-card-title">Time to close observation</div>
						<div class="small-card-counter"><span id="tot_close_obs">0</span></div>
						<div class="small-card-counter-title">DAYS</div>
					</div>
				</div>
			</div>
		</div>
    </div>
	@php
	$all_task_wise_array = [];
	@endphp
	@foreach($locations as $key=>$location)
	@php 
		$taskLocation = App\Models\Task_lists::where('location_id', $location->id)->whereBetween('created_at', [$startDate, $endDate])->pluck('id')->toArray();
		
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
		$repeated_obs_count = App\Models\Task_list_corrective_action::whereIn('task_list_id', $taskLocation)->where('repeated_observation', 1)->whereBetween('created_at', [$startDate, $endDate])->count();
		//echo $repeated_obs_count;die;
		// $no_of_repeated_obs = ceil($repeated_obs_count / 4);
		$no_of_repeated_obs = $repeated_obs_count;
		
		//------- for time to close observation count ---
		 
		$lo_direct_approve = App\Models\Task_list_corrective_action::whereIn('task_list_id', $taskLocation)->where('lo_direct_approve', 1)->whereBetween('lo_completed_by', [$startDate, $endDate])->count();
		
		$lo_no_direct_approve = App\Models\Task_list_corrective_action::whereIn('task_list_id', $taskLocation)->where('lo_direct_approve', 0)->whereBetween('lo_completed_by', [$startDate, $endDate])->count();
		$close_obs = $lo_direct_approve + $lo_no_direct_approve;
		
		//$time_to_close_obs = $time_to_close_obs + $close_obs;
		
		$days = 0;
		$observations = 0;
		$task_wise_array = [];
		foreach($taskLocation as $tsk_id)
		{
			$exists = App\Models\Task_list_corrective_action::where('task_list_id', $tsk_id)->where('los_action', 1)->where('inspector_action', 1)->exists();
			
			if ($exists) {
				$corrective = App\Models\Task_list_corrective_action::where('task_list_id', $tsk_id)
					->where('los_action', 1)
					->where('inspector_action', 1)
					->first();

				$updated_at = Carbon::parse($corrective->updated_at);

				// Try to get created_at from checklist first
				$task_checklist = App\Models\Task_list_checklists::where('task_list_id', $tsk_id)
					->where('approve', 0)
					->first();

				// If not found, try subchecklist
				if ($task_checklist) {
					$task_created_at = Carbon::parse($task_checklist->created_at);
				} else {
					$task_subchecklist = App\Models\Task_list_subchecklists::where('task_list_id', $tsk_id)
						->where('approve', 0)
						->first();

					$task_created_at = $task_subchecklist ? Carbon::parse($task_subchecklist->created_at) : null;
				}

				if (isset($task_created_at)) {
					// Calculate difference in days
					$days = $days + $task_created_at->diffInDays($updated_at);
					//echo "Task ID {$tsk_id}: {$days} days<br>";
					$all_task_wise_array[] = $task_wise_array[] = [
						'task_created_array' => $task_created_at->toDateTimeString(),
						'task_updated_array' => $updated_at->toDateTimeString()
					];
				}
			}
			
			
			$get_tasklist_checklist = App\Models\Task_list_checklists::where('approve', 0)->where('task_list_id', $tsk_id)->get();
			if($get_tasklist_checklist->count() > 0)
			{
				$observations += $get_tasklist_checklist->count();
			}
			$get_tasklist_subchecklist = App\Models\Task_list_subchecklists::where('approve', 0)->where('task_list_id', $tsk_id)->get();
			if($get_tasklist_subchecklist->count() > 0)
			{
				$observations += $get_tasklist_subchecklist->count();
			}
		}
		// echo '<pre>'; print_r($task_wise_array);
		$createdDates = array_column($task_wise_array, 'task_created_array');
		$updatedDates = array_column($task_wise_array, 'task_updated_array');

		$minCreated = Carbon::parse(min($createdDates));
		$maxUpdated = Carbon::parse(max($updatedDates));

		$diffDays = $minCreated->diffInDays($maxUpdated);
		$close_obs = $diffDays;
		$tot_close_obs = $tot_close_obs + $close_obs;
		
		
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
			<div class="row flex-wrap d-flex">
				<div class="col-md-4 col-sm-4 col-xs-4 small-card-first d-flex">
					<div class="small-card">
						<div class="small-card-title">No. of observations</div>
						<div class="small-card-counter">{{ $observations ?? '' }}</div>
						<div class="small-card-counter-title">{{ $slug == 'weekly' ? 'WEEKLY' : "MONTHLY" }}</div>
					</div>
				</div>
				<div class="col-md-4 col-sm-4 col-xs-4 small-card-second d-flex">
					<div class="small-card">
						<div class="small-card-title">Repeat observations</div>
						<div class="small-card-counter">{{ $no_of_repeated_obs }}</div>
						<div class="small-card-counter-title">{{ $slug == 'weekly' ? 'WEEKLY' : "MONTHLY" }}</div>
					</div>
				</div>
				<div class="col-md-4 col-sm-4 col-xs-4 small-card-third d-flex">
					<div class="small-card">
						<div class="small-card-title">Time to close observations</div>
						<div class="small-card-counter">{{ $close_obs }}</div>
						<div class="small-card-counter-title">DAYS</div>
					</div>
				</div>
			</div>
			<div class="row flex-wrap d-flex">
				<canvas id="correctionChart{{$key}}" height="70"></canvas>
			</div>
		</div>
	</div>
	@endforeach
	@php
	// echo '<pre>'; print_r($all_task_wise_array);	
	$createdDates = array_column($all_task_wise_array, 'task_created_array');
	$updatedDates = array_column($all_task_wise_array, 'task_updated_array');

	$minCreated = Carbon::parse(min($createdDates));
	$maxUpdated = Carbon::parse(max($updatedDates));

	$totdiffDays = $minCreated->diffInDays($maxUpdated);
	@endphp
	<input type="hidden" id="loc_tot_no_of_obs" value="{{ $loc_tot_no_of_obs ?? ''}}">
	<input type="hidden" id="time_to_close_obs" value="{{ $time_to_close_obs ?? ''}}">
	<input type="hidden" id="tot_close_observation" value="{{ $totdiffDays ?? ''}}">
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
	<input type="hidden" value="{{ $slug ?? '' }}" id="slug">
@endsection 
@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
$(document).ready(function() {
	var tot_obs = $('#loc_tot_no_of_obs').val();
	//var close_obs = $('#time_to_close_obs').val();
	var tot_close_obs = $('#tot_close_observation').val();
	//alert(tot_close_obs);
	$('#tot_no_of_obs').text(tot_obs);
	$('#tot_close_obs').text(tot_close_obs);
	
	
	let slug = $('#slug').val();
	if(slug == 'weekly')
	{
		$('.weekly-list-show').addClass('active-btn');
	}
	
	if(slug == 'monthly')
	{
		$('.monthly-list-show').addClass('active-btn');
	}
	
	
	$(document).on('click', '.weekly-list-show', function(){
		$('.weekly-list-show').removeClass('active-btn');
		$(this).addClass('active-btn');
		
		redirect = "{{ route('management-dashboard', ':slug') }}";
		window.location.href = redirect.replace(':slug', 'weekly');
		
	});
	
	$(document).on('click', '.monthly-list-show', function(){
		$('.weekly-list-show').removeClass('active-btn');
		$(this).addClass('active-btn');
		
		redirect = "{{ route('management-dashboard', ':slug') }}";
		window.location.href = redirect.replace(':slug', 'monthly');
		
	});
	
});
</script>
<script>
@foreach($locations as $key=>$location)
    const chartData{{ $key }} = @json($chartData[$key]);
	// console.log(chartData{{ $key }});

    const labels{{ $key }} = chartData{{ $key }}.map(item => item.label);
    const correctiveData{{ $key }} = chartData{{ $key }}.map(item => item.corrective_needed);
    const repeatData{{ $key }} = chartData{{ $key }}.map(item => item.repeat_correction);

    const data{{ $key }} = {
        labels: labels{{ $key }},
        datasets: [
            {
                label: '# of Corrective Needed',
                data: correctiveData{{ $key }},
                borderColor: '#0033cc', // deep blue
                backgroundColor: '#0033cc',
                tension: 0.4,
                pointStyle: 'circle',
                pointRadius: correctiveData{{ $key }}.map(v => v > 0 ? 6 : 0),
                pointHoverRadius: 8,
            },
            {
                label: '# of Repeat Correction Needed',
                data: repeatData{{ $key }},
                borderColor: '#cc0000', // deep red
                backgroundColor: '#cc0000',
                tension: 0.4,
                pointStyle: 'circle',
                pointRadius: repeatData{{ $key }}.map(v => v > 0 ? 6 : 0),
                pointHoverRadius: 8,
            }
        ]
    };

    const config{{ $key }} = {
        type: 'line',
        data: data{{ $key }},
        options: {
            responsive: true,
            plugins: {
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            if (context.dataset.label === '# of Corrective Needed')
                                return `${context.parsed.y} corrective needed`;
                            else
                                return `${context.parsed.y} repeat correction needed`;
                        }
                    }
                },
                legend: {
                    labels: {
                        usePointStyle: true,
                        pointStyle: 'circle'
                    }
                }
            },
            scales: {
                x: {
                    title: { display: true, text: 'Weeks (Monday - Sunday)' },
                },
                y: {
                    title: { display: true, text: 'Count' },
                    beginAtZero: true
                }
            }
        }
    };

    new Chart(document.getElementById('correctionChart{{ $key }}'), config{{ $key }});
@endforeach
</script>
@endsection

