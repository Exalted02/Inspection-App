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
  
  $taskLocation = App\Models\Task_lists::where('location_id', $location_id)->whereBetween('created_at', [$startDate, $endDate])->pluck('id')->toArray();
	$taskListIds = App\Models\Task_lists::whereIn('location_id', [$location_id])->whereBetween('created_at', [$startDate, $endDate])
					->pluck('id');
	$categoryIds = App\Models\Task_list_subcategories::whereIn('task_list_id', $taskListIds)
					->pluck('task_list_category_id');
// echo '<pre>'; print_r($categoryIds); die;

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
		// echo $correctiveNeededCount; die;		
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
		// echo $correctiveActionCount; die;
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
		// echo $correctivePlanCount; die;
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
		// echo $correctiveApprovedCount; die;
		
		$correctiveNeddedChecklistArray = [];
		$correctiveNeddedSubchecklistArray = [];
		
		$observations = 0;
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
			$get_tasklist_checklist = App\Models\Task_list_checklists::where('approve', 0)->where('task_list_id', $val)->get();
			if($get_tasklist_checklist->count() > 0)
			{
				$observations += $get_tasklist_checklist->count();
			}
			$get_tasklist_subchecklist = App\Models\Task_list_subchecklists::where('approve', 0)->where('task_list_id', $val)->get();
			if($get_tasklist_subchecklist->count() > 0)
			{
				$observations += $get_tasklist_subchecklist->count();
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
		$repeated_obs_count = App\Models\Task_list_corrective_action::whereIn('task_list_id', $taskLocation)->where('repeated_observation', 1)->whereBetween('created_at', [$startDate, $endDate])->count();
		// echo "<pre>";print_r($taskLocation);die;
		$no_of_repeated_obs = $repeated_obs_count;
		
		//------- for time to close observation count ---
		 
		$lo_direct_approve = App\Models\Task_list_corrective_action::whereIn('task_list_id', $taskLocation)->where('lo_direct_approve', 1)->whereBetween('lo_completed_by', [$today, $futureDate])->count();
		
		$lo_no_direct_approve = App\Models\Task_list_corrective_action::whereIn('task_list_id', $taskLocation)->where('lo_direct_approve', 0)->whereBetween('lo_completed_by', [$today, $futureDate])->count();
		$close_obs = $lo_direct_approve + $lo_no_direct_approve;
		
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
					$task_wise_array[] = [
						'task_created_array' => $task_created_at->toDateTimeString(),
						'task_updated_array' => $updated_at->toDateTimeString()
					];
				}
			}
		}
		$createdDates = array_column($task_wise_array, 'task_created_array');
		$updatedDates = array_column($task_wise_array, 'task_updated_array');

		$minCreated = Carbon::parse(min($createdDates));
		$maxUpdated = Carbon::parse(max($updatedDates));

		$diffDays = $minCreated->diffInDays($maxUpdated);
		
		$time_to_close_obs = $time_to_close_obs + $close_obs;
@endphp
    <div class="container location-details">
		<div class="d-flex align-items-center location-header mb-3">
			<img src="{{url('uploads/location/' . $location_details->image ?? '')}}" alt="Location" />
			<div>
				<div class="title">{{ $location_details->location_name ?? ''}}</div>
				<small class="text-muted"><i class="fa fa-location-dot"></i>@if(!empty($location_details->unit_floor))
				{{ $location_details->unit_floor ?? ''}}</br>
				@endif
				{{ $location_details->street ?? ''}}</br>
				{{ $location_details->zipcode ?? ''}}
				</small>
			</div>
		</div>
		<!-- =-=-=-=-=-=-= Breadcrumb End =-=-=-=-=-=-= --> 
		<!-- =-=-=-=-=-=-= Main Content Area =-=-=-=-=-=-= -->
		<div class="main-content-area clearfix">
			<section class="custom-padding1">
					<div class="pt-2 pb-2">
						<div class="row flex-wrap d-flex">
							<div class="col-md-6 col-sm-6 col-xs-6 small-card-odd d-flex">
								<div class="bg small-card">
									<div class="small-card-title">No. of inspections</div>
									<div class="small-card-counter">{{ $correctiveNeededCount+$correctiveActionCount+$correctivePlanCount+$correctiveApprovedCount }}</div>
									<div class="small-card-counter-title">WEEKLY</div>
								</div>
							</div>
							<div class="col-md-6 col-sm-6 col-xs-6 small-card-even d-flex">
								<div class="bg small-card">
									<div class="small-card-title">No. of observations</div>
									<div class="small-card-counter">{{ $observations ?? '' }}</div>
									<div class="small-card-counter-title">WEEKLY</div>
								</div>
							</div>
							<div class="col-md-6 col-sm-6 col-xs-6 small-card-odd d-flex">
								<div class="bg small-card">
									<div class="small-card-title">Time to close observation</div>
									<div class="small-card-counter">{{ $diffDays }}</div>
									<div class="small-card-counter-title">DAYS</div>
								</div>
							</div>
							<div class="col-md-6 col-sm-6 col-xs-6 small-card-even d-flex">
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
							
							$img = $tasks->image !='' ? url('uploads/task/' . $tasks->image) : url('uploads/task/default-task-pic.png');
							
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
									<a href="{{ route('management-location-task-details', ['task_id'=> $tasks->id,'active'=>1 ]) }}">
										<img src="{{ $img}}" alt="Task" />
										<h6 class="location-observation-title">{{ $tasks->task_title ?? '' }}</h6>
											{{--<p class="location-observation-title-details d-flex gap-10px align-items-center mb-0">Pending LOS to approve <img src="{{url('uploads/profile/' .$inspector->get_user->id .'/inspector/'. $inspector->get_user->profile_image)}}" class="rounded-profile-img mb-0" alt="Profile image">{{ $inspector->get_user->name ?? ''}}</p>--}}
									</a>
								</div>
							</div>
							@endforeach
						@else
							<div class="text-center">
								<div class="add-task-box">							
									<strong><h3>No record found</h3></strong>
								</div>
							</div>
						@endif
					</div>
				</div>
			</section>
		</div>
    </div>
@endsection 
@section('scripts')

@endsection

