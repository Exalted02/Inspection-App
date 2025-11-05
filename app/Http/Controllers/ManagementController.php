<?php
  
namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Manage_location;
use App\Models\Task_lists;
use App\Models\Users_location;
use App\Models\Task_list_subcategories;
use App\Models\Task_list_checklists;
use App\Models\Task_list_subchecklists;
use App\Models\Task_list_corrective_action;
use App\Models\Task_list_checklist_rejected_files;
use App\Models\Task_list_subchecklist_rejected_files;
use App\Models\User;
use Illuminate\Support\Facades\DB;

use Carbon\Carbon;
use Illuminate\Support\Collection;

class ManagementController extends Controller
{
    public function index($slug = '')
    {
		$data = [];
		if(auth()->user()->user_type == 1 || auth()->user()->user_type == 2 || auth()->user()->user_type == 3)
		{
			return redirect('inspector-dashboard');
		}
		
		//$locations = Manage_location::where('company_id', auth()->user()->company_name)->get();
		//----------------------
		$userLocationArr = [];
		$userLocationArr = Users_location::where('user_id', auth()->user()->id)->pluck('location_id')->toArray();
		
		$locations = Manage_location::whereIn('id', $userLocationArr)->get();
		//----------------------
		$data['userdata'] = User::with('get_user_location')->where('id', auth()->user()->id)->first();
		$data['locations'] = $locations;
		$data['userLocationArr'] =$userLocationArr;
		
		//====== chart data start=======
		$today = Carbon::today();
		if($slug == 'weekly')
		{
			$startDate = $today->copy()->subDays(15)->startOfWeek(Carbon::MONDAY);
			$endDate = $today->copy()->addDays(15)->endOfWeek(Carbon::SUNDAY);
		}
		
		if($slug == 'monthly')
		{
			$startDate = $today->copy()->startOfMonth();
			$endDate = $today->copy()->endOfMonth();
		}
			//echo $startDate.' -- '.$endDate; die;
			$weeks = collect();
			$current = $startDate->copy();
		
		
		
		
		$chartData = [];
		foreach($locations as $key=>$location)
		{
			$taskLocation = Task_lists::where('location_id', $location->id)->pluck('id')->toArray();
			
			// count total corrective needed
		$taskListIds = Task_lists::where('location_id', $location->id)->pluck('id');
		//echo "<pre>";print_r($taskListIds);die;
		
		$categoryIds = Task_list_subcategories::whereIn('task_list_id', $taskListIds)
			->pluck('task_list_category_id');
			
		$submit_task_id = Task_list_subcategories::whereIn('task_list_category_id', $categoryIds)->pluck('task_list_id')->toArray();
		
		
		
			
		//------------------------------------------------
			while ($current->lte($endDate)) {
				$weekStart = $current->copy();
				$weekEnd = $current->copy()->endOfWeek(Carbon::SUNDAY);
				$weeks->push([
					'label' => $weekStart->format('d M') . ' - ' . $weekEnd->format('d M'),
					'start' => $weekStart->toDateString(),
					'end' => $weekEnd->toDateString(),
				]);
				$current->addWeek();
			}
			//echo "<pre>";print_r($weeks);die;

			// Example data fetch (replace with your own DB query)
			/*$daydata = collect([
				['date' => '2025-09-22', 'corrective_needed' => 10, 'repeat_correction' => 15],
				['date' => '2025-09-30', 'corrective_needed' => 20, 'repeat_correction' => 14],
				['date' => '2025-10-13', 'corrective_needed' => 5,  'repeat_correction' => 3],
				['date' => '2025-10-25', 'corrective_needed' => 5,  'repeat_correction' => 3],
			]);*/
			
			$daydata = collect();

			foreach ($weeks as $week) {
				$repeated_obs_count = Task_list_corrective_action::whereIn('task_list_id', $taskLocation)->where('repeated_observation', 1)->whereBetween('created_at', [$week['start'], $week['end']])->count();
				
				
				$excludedChecklistPairs = Task_list_corrective_action::whereNotIn('rejected_repeated', [0])
				->whereIn('task_list_id', $taskListIds)
				//->where('lo_id', auth()->user()->id) //add new 24-09-2025
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
			//echo "<pre>";print_r($correctiveChecklistIds);die;
			$excludedSubChecklistPairs = Task_list_corrective_action::whereNotIn('rejected_repeated', [0])
					->whereIn('task_list_id', $taskListIds)
					//->where('los_id', auth()->user()->id) // add new 24-09-2025
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
				//echo "<pre>";print_r($correctiveChecklistIds);die;
				//echo "<pre>";print_r($correctiveSubChecklistIds);die;
				
				$correctiveNeededCount = DB::table(function ($query) use (
					$taskListIds,
					$categoryIds,
					$submit_task_id,
					$correctiveChecklistIds,
					$correctiveSubChecklistIds,
					$week
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
						->whereBetween('created_at', [$week['start'], $week['end']])
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
						->whereBetween('created_at', [$week['start'], $week['end']])
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
				//echo $repeated_obs_count;die;
				$daydata->push([
					'date' => $week['start'],
					'corrective_needed' => $correctiveNeededCount,
					'repeat_correction' => $repeated_obs_count,
				]);
			}
			//echo "<pre>";print_r($daydata);die;
			// Prepare weekly data
			$chartData[$key] = collect($weeks)->map(function ($week) use ($daydata) {
				$weekData = $daydata->filter(function ($item) use ($week) {
					return $item['date'] >= $week['start'] && $item['date'] <= $week['end'];
				});

				return [
					'label' => $week['label'],
					'corrective_needed' => $weekData->sum('corrective_needed'),
					'repeat_correction' => $weekData->sum('repeat_correction'),
				];
			});
		//---------------------------------------------
		}
		
		//echo "<pre>";print_r($chartData);die;
		$data['chartData'] = $chartData;
		$data['slug'] = $slug;
        return view('management.management-dashboard', $data);
    }
    public function management_location($id='')
    {
		if (auth()->user()->user_type == 1 || auth()->user()->user_type == 2 
		|| auth()->user()->user_type == 3) {
			return redirect('management-dashboard');
		}
		
		$data = [];
		$data['location_id'] = $id;
		$data['location_details'] = Manage_location::where('id', $id)->first();
		$data['task_details'] = Task_lists::where('location_id', $id)->get();
        return view('management.management-location', $data);
    }
	public function management_location_task_details_bck($id='')
	{
		$data = [];
		$checklist_id = 5;
		$subchecklist_id = 1;
		$type = 'subchecklist';
		
		$location_id = 3;
		
		$data['task_id'] = $id ?? '';
		$data['location_id'] = $location_id ?? '';
		$data['checklist_id'] = $checklist_id ?? '';
		$data['subchecklist_id'] = $subchecklist_id ?? '';
		$data['type'] = $type ?? '';
		$data['tab'] = 'corrective-action';
		
		return view('management.management-task-reply-details', $data);
	}
	public function management_location_task_details($tid='', $active='')
	{
		if (auth()->user()->user_type == 1 || auth()->user()->user_type == 2 
		|| auth()->user()->user_type == 3) {
			return redirect('management-dashboard');
		}
		
		$inspectorId = auth()->user()->id;

		$offset = 0;
		$limit = config('custom.LOAD_MORE_LIST_SHOW');

		// Common filters
		/*$taskListIds = Task_lists::where('location_id', $lid)
			->where('inspector_id', auth()->user()->id)
			->pluck('id');*/
		
		//$taskListIds = Task_lists::where('location_id', $lid)->pluck('id');
		
		$taskListIds = array($tid);
		//echo "<pre>";print_r($taskListIds);die;
		$categoryIds = Task_list_subcategories::whereIn('task_list_id', $taskListIds)
			->pluck('task_list_category_id');
		
		//-----05-08-2025 if submit checklist then submit task data show-----
		$submit_task_id = Task_list_subcategories::whereIn('task_list_category_id', $categoryIds)->pluck('task_list_id')->toArray();
		// Checklist IDs with existing corrective needed
		
		//--- 04-08-2025----
		$excludedChecklistPairs = Task_list_corrective_action::whereNotIn('rejected_repeated', [0])
			->whereIn('task_list_id', $taskListIds)
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
			
		//------------------------------------
		//echo "<pre>";print_r($correctiveChecklistIds);die;
		//------------------------------------
		
		$excludedSubChecklistPairs = Task_list_corrective_action::whereNotIn('rejected_repeated', [0])
			->whereIn('task_list_id', $taskListIds)
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
			
		//echo "<pre>";print_r($excludedSubChecklistPairs);
			
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
		}, 'combined')
			->orderByDesc('updated_at')
			->offset($offset)
			->limit($limit)
			->get();
		//echo "<pre>";print_r($correctiveneeded);die;
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
		
		//echo "<pre>";print_r($correctiveneeded);die;
		$correctiveNeddedArray = [];
		foreach($correctiveneeded as $needed)
		{
			if($needed->type == 'checklist')
			{
				//echo $needed->checklist_id."</br>";
				$task_list_checklist_corrective_needed = Task_list_corrective_action::where('task_list_id', $needed->task_list_id)->where('checklist_id', $needed->checklist_id)->first();
				
				$checklistData = Task_list_checklists::where('checklist_id', $needed->checklist_id)->where('task_list_id', $needed->task_list_id)->first();
				$id = $checklistData ? $checklistData->id : '';
					
				if(!$task_list_checklist_corrective_needed)
				{
					$isfiles = '';
					$images = '';
					
					$isfiles = Task_list_checklist_rejected_files::where('task_list_checklist_id', $id)->first();
					
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
					$isfiles = Task_list_checklist_rejected_files::where('task_list_checklist_id', $id)->first();
					
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
				$task_list_subchecklist_corrective_needed = Task_list_corrective_action::where('task_list_id', $needed->task_list_id)->where('checklist_id', $needed->task_list_checklist_id)->where('subchecklist_id', $needed->subchecklist_id)->first();
							
				$subchecklistData = Task_list_subchecklists::where('task_list_id', $needed->task_list_id)->where('task_list_checklist_id', $needed->task_list_checklist_id)->where('subchecklist_id',$needed->subchecklist_id)->first();
				$id = $subchecklistData ? $subchecklistData->id : '';
							
				if(!$task_list_subchecklist_corrective_needed)
				{
					$isSubChecklistfiles = '';
					$subChecklistimages = '';
					$isSubChecklistfiles = Task_list_subchecklist_rejected_files::where('task_list_subchecklist_id', $id)->first();
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
					$isSubChecklistfiles = Task_list_subchecklist_rejected_files::where('task_list_subchecklist_id', $id)->first();
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
		//echo "<pre>";print_r($correctiveneeded);die;
		//============= corrective action --------------------------
		$correctiveActionArray = [];
		
		$correctiveActionCount = Task_list_corrective_action::whereIn('lo_direct_approve', [1])->whereIn('task_list_id', $taskListIds)->whereIn('category_id', $categoryIds)
			->where(function ($q) {
				$q->where(function ($q) {
					$q->where('inspector_action', 0)->where('los_action', 1);
				})->orWhere(function ($q) {
					$q->where('inspector_action', 1)->where('los_action', 0);
				})->orWhere(function ($q) {
					$q->where('inspector_action', 0)->where('los_action', 0);
				});
			})->count();
			
		
		$correctiveActionData = Task_list_corrective_action::whereIn('lo_direct_approve', [1])->whereIn('task_list_id', $taskListIds)->whereIn('category_id', $categoryIds)
			->where(function ($q) {
				$q->where(function ($q) {
					$q->where('inspector_action', 0)->where('los_action', 1);
				})->orWhere(function ($q) {
					$q->where('inspector_action', 1)->where('los_action', 0);
				})->orWhere(function ($q) {
					$q->where('inspector_action', 0)->where('los_action', 0);
				});
			})
			->offset($offset)
			->limit($limit)
			->get();
			//->whereNotNull('checklist_id')
			//->pluck('checklist_id')->toArray();
		//echo "<pre>";print_r($correctiveActionData);die;
		foreach($correctiveActionData as $action)
		{
			//echo $action->checklist_id. "</br>";
			
			$type = '';
			$image = '';
			if($action->subchecklist_id == null)
			{
				$type = 'checklist';
				
				$checklistFile = Task_list_checklists::with('get_checklist_files')->where('task_list_id', $action->task_list_id)->where('checklist_id', $action->checklist_id)->first();
				
				$image = $checklistFile && $checklistFile->get_checklist_files->isNotEmpty() ? $checklistFile->get_checklist_files->first()->file : null;
				
			}
			else
			{
				$type = 'subchecklist';
				
				$subChecklistFile = Task_list_subchecklists::with('get_subchecklist_files')->where('task_list_id', $action->task_list_id)->where('task_list_checklist_id', $action->checklist_id)->where('subchecklist_id', $action->subchecklist_id)->first();
				
				$image = $subChecklistFile && $subChecklistFile->get_subchecklist_files->isNotEmpty() ? $subChecklistFile->get_subchecklist_files->first()->file : null;
			}
			
			$correctiveActionArray[] = [
				'type' => $type ,
				'task_id' => $action->task_list_id,
				'checklist_id' => $action->checklist_id,
				'subchecklist_id' => $action->subchecklist_id,
				'rejected_region' => $action->lo_corrective_action_plan,
				'inspector_action' => $action->inspector_action,
				'los_action' => $action->los_action,
				'second_checked' => $action->lo_corrective_action_plan_second_check,
				'lo_direct_approve' => $action->lo_direct_approve,
				'image' => $image,
			];
			
		}
			
		//echo '<pre>';print_r($correctiveActionArray);die;
		
		//============= corrective plan  --------------------------
		$correctivePlanArray = [];
		
		$correctivePlanCount = Task_list_corrective_action::whereIn('lo_direct_approve', [0])->whereIn('task_list_id', $taskListIds)->whereIn('category_id', $categoryIds)
			->where(function ($q) {
				$q->where(function ($q) {
					$q->where('inspector_action', 0)->where('los_action', 1);
				})->orWhere(function ($q) {
					$q->where('inspector_action', 1)->where('los_action', 0);
				})->orWhere(function ($q) {
					$q->where('inspector_action', 0)->where('los_action', 0);
				});
			})->count();
			
		
		$correctivePlanData = Task_list_corrective_action::whereIn('lo_direct_approve', [0])->whereIn('task_list_id', $taskListIds)->whereIn('category_id', $categoryIds)
			->where(function ($q) {
				$q->where(function ($q) {
					$q->where('inspector_action', 0)->where('los_action', 1);
				})->orWhere(function ($q) {
					$q->where('inspector_action', 1)->where('los_action', 0);
				})->orWhere(function ($q) {
					$q->where('inspector_action', 0)->where('los_action', 0);
				});
			})
			->offset($offset)
			->limit($limit)
			->get();
			//->whereNotNull('checklist_id')
			//->pluck('checklist_id')->toArray();
		//echo "<pre>";print_r($correctiveActionData);die;
		foreach($correctivePlanData as $plan)
		{
			//echo $action->checklist_id. "</br>";
			
			$type = '';
			$image = '';
			if($plan->subchecklist_id == null)
			{
				$type = 'checklist';
				
				$checklistFile = Task_list_checklists::with('get_checklist_files')->where('task_list_id', $plan->task_list_id)->where('checklist_id', $plan->checklist_id)->first();
				
				$image = $checklistFile && $checklistFile->get_checklist_files->isNotEmpty() ? $checklistFile->get_checklist_files->first()->file : null;
				
			}
			else
			{
				$type = 'subchecklist';
				
				$subChecklistFile = Task_list_subchecklists::with('get_subchecklist_files')->where('task_list_id', $plan->task_list_id)->where('task_list_checklist_id', $plan->checklist_id)->where('subchecklist_id', $plan->subchecklist_id)->first();
				
				$image = $subChecklistFile && $subChecklistFile->get_subchecklist_files->isNotEmpty() ? $subChecklistFile->get_subchecklist_files->first()->file : null;
			}
			
			$correctivePlanArray[] = [
				'type' => $type ,
				'task_id' => $plan->task_list_id,
				'checklist_id' => $plan->checklist_id,
				'subchecklist_id' => $plan->subchecklist_id,
				'rejected_region' => $plan->lo_corrective_action_plan,
				'inspector_action' => $plan->inspector_action,
				'los_action' => $plan->los_action,
				'second_checked' => $plan->lo_corrective_action_plan_second_check,
				'lo_direct_approve' => $plan->lo_direct_approve,
				'image' => $image,
			];
			
		}
			
		//echo '<pre>';print_r($correctivePlanArray);die;
		
		//============= corrective completed approved -------------
		//correctiveChecklistIds
		$approvedCompletedArray = [];
		
		
		// get the task_id from subcategory tables
		$subcategoriesTaskIds = Task_list_subcategories::whereIn('task_list_category_id', $categoryIds)
			->pluck('task_list_id');

		$matchingTaskListIds = array_values(array_intersect($taskListIds,  $subcategoriesTaskIds->toArray()));
		//echo "<pre>";print_r($matchingTaskListIds);die;
		
		// Checklist IDs with existing corrective needed
		
		$correctiveChecklistApprIds = Task_list_corrective_action::whereIn('task_list_id', $matchingTaskListIds)
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
			
			
		//echo "<pre>";print_r($correctiveChecklistApprIds);die;
		//------------------------------------
		
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
				
		//echo "<pre>";print_r($checklistApprIds);die;
		$correctiveApprChecklistIds = array_merge($correctiveChecklistApprIds,$checklistApprIds);
		//echo "<pre>";print_r($correctiveApprChecklistIds);die;
		//------------------------------------
		
		$correctiveSubChecklistApprIds = Task_list_corrective_action::whereIn('task_list_id', $matchingTaskListIds)
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
			
		    //echo "<pre>";print_r($correctiveSubChecklistApprIds);die;
			
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
				
			//echo "<pre>";print_r($subchecklistApprIds);die;
			$correctiveApprSubChecklistIds = array_merge($correctiveSubChecklistApprIds,$subchecklistApprIds);	
			//echo "<pre>";print_r($correctiveApprSubChecklistIds);die;

		// Raw union query
		$correctiveApproved = DB::table(function ($query) use (
			$taskListIds,
			$matchingTaskListIds,
			$categoryIds,
			$submit_task_id,
			$correctiveApprChecklistIds,
			$correctiveApprSubChecklistIds
		) {
			$query->select(
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
				->from('task_list_checklists')
				//->whereIn('task_list_id', $taskListIds)
				//->whereIn('task_list_id', $matchingTaskListIds)
				->whereIn('category_id', $categoryIds)
				->whereIn('approve', [0,1])
				//->whereIn('checklist_id', $correctiveApprChecklistIds)
				->where(function ($query) use ($correctiveApprChecklistIds) {
					foreach ($correctiveApprChecklistIds as $pair) {
						[$taskListId, $checklistId] = explode('-', $pair);
						$query->orWhere(function ($q) use ($taskListId, $checklistId) {
							$q->where('task_list_id', $taskListId)
							  ->where('checklist_id', $checklistId);
						});
					}
				})
			->unionAll(
				DB::table('task_list_subchecklists')
					->select(
						'id',
						'subchecklist_id as item_id',
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
					//->whereIn('task_list_id', $taskListIds)
					//->whereIn('task_list_id', $matchingTaskListIds)
					->whereIn('category_id', $categoryIds)
					->whereIn('approve', [0,1])
					//->whereIn('subchecklist_id', $correctiveApprSubChecklistIds)
					->where(function ($query) use ($correctiveApprSubChecklistIds) {
						foreach ($correctiveApprSubChecklistIds as $pair) {
								[$taskListId, $checklistId, $subchecklistId] = explode('-', $pair);
								$query->orWhere(function ($q) use ($taskListId, $checklistId, $subchecklistId) {
									$q->where('task_list_id', $taskListId)
									  ->where('task_list_checklist_id', $checklistId)
									  ->where('subchecklist_id', $subchecklistId);
								});
							}
					})
			);
		}, 'combined')
		//->orderByDesc('updated_at')
		->orderBy('updated_at', 'asc')
		->offset($offset)
		->limit($limit)
		->get();
		//echo "<pre>";print_r($correctiveApproved);die;
		$correctiveApprovedCount = DB::table(function ($query) use (
			$taskListIds,
			$matchingTaskListIds,
			$categoryIds,
			$submit_task_id,
			$correctiveApprChecklistIds,
			$correctiveApprSubChecklistIds
		) {
			$query->select(
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
				->from('task_list_checklists')
				//->whereIn('task_list_id', $taskListIds)
				//->whereIn('task_list_id', $matchingTaskListIds)
				->whereIn('category_id', $categoryIds)
				->whereIn('approve', [0,1])
				//->whereIn('checklist_id', $correctiveApprChecklistIds)
				->where(function ($query) use ($correctiveApprChecklistIds) {
					foreach ($correctiveApprChecklistIds as $pair) {
						[$taskListId, $checklistId] = explode('-', $pair);
						$query->orWhere(function ($q) use ($taskListId, $checklistId) {
							$q->where('task_list_id', $taskListId)
							  ->where('checklist_id', $checklistId);
						});
					}
				})
			->unionAll(
				DB::table('task_list_subchecklists')
					->select(
						'id',
						'subchecklist_id as item_id',
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
					//->whereIn('task_list_id', $taskListIds)
					//->whereIn('task_list_id', $matchingTaskListIds)
					->whereIn('category_id', $categoryIds)
					->whereIn('approve', [0,1])
					//->whereIn('subchecklist_id', $correctiveApprSubChecklistIds)
					->where(function ($query) use ($correctiveApprSubChecklistIds) {
						foreach ($correctiveApprSubChecklistIds as $pair) {
								[$taskListId, $checklistId, $subchecklistId] = explode('-', $pair);
								$query->orWhere(function ($q) use ($taskListId, $checklistId, $subchecklistId) {
									$q->where('task_list_id', $taskListId)
									  ->where('task_list_checklist_id', $checklistId)
									  ->where('subchecklist_id', $subchecklistId);
								});
							}
					})
			);
		}, 'combined')->count();
		
	
		//echo "<pre>";print_r($correctiveApproved);die;
		
		foreach($correctiveApproved as $appr)
		{
			
			if($appr->approve == 0)
			{
				if($appr->type == 'checklist')
				{
					$task_list_checklist_corrective_needed = Task_list_corrective_action::where('task_list_id', $appr->task_list_id)->where('checklist_id', $appr->checklist_id)->first();
					if($task_list_checklist_corrective_needed)
					{
						$chklstData  = Task_list_checklists::where('task_list_id', $appr->task_list_id)->where('checklist_id', $appr->checklist_id)->first();
						$id = $chklstData ? $chklstData->id : '';
						$isfiles = '';
						$images = '';
						$isfiles = Task_list_checklist_rejected_files::where('task_list_checklist_id', $id)->first();
						$images = $isfiles ? $isfiles->file  : '';
						$approvedCompletedArray[] = [
							'type' => 'checklist',
							'task_id' => $appr->task_list_id,
							'checklist_id' => $appr->checklist_id,
							'rejected_region' => $appr->rejected_region,
							'image' => $images,
							'inspector_action'=> $task_list_checklist_corrective_needed->inspector_action,
							'los_action'=> $task_list_checklist_corrective_needed->los_action,
							'created_at'=> change_date_format($task_list_checklist_corrective_needed->created_at, 'Y-m-d H:i:s', 'd M Y, h:i A'),
							'updated_at'=> change_date_format($task_list_checklist_corrective_needed->updated_at, 'Y-m-d H:i:s', 'd M Y, h:i A'),
							//'second_checked'=> $task_list_checklist_corrective_action->lo_corrective_action_plan_second_check,
						];
					}
				}
				else if($appr->type == 'subchecklist')
				{
					$task_list_subchecklist_corrective_needed = Task_list_corrective_action::where('task_list_id', $appr->task_list_id)->where('checklist_id', $appr->task_list_checklist_id)->where('subchecklist_id', $appr->subchecklist_id)->first();
					{
						if($task_list_subchecklist_corrective_needed)
						{
							$subchklstData  = task_list_subchecklists::where('task_list_id', $appr->task_list_id)->where('task_list_checklist_id', $appr->checklist_id)->where('subchecklist_id',$appr->subchecklist_id)->first();
							$id = $subchklstData ? $subchklstData->id : '';
						
							$isSubChecklistfiles = '';
							$subChecklistimages = '';
							$isSubChecklistfiles = Task_list_subchecklist_rejected_files::where('task_list_subchecklist_id', $id)->first();
									
							$approvedCompletedArray[] = [
								'type' => 'subchecklist',
								'task_id' => $appr->task_list_id,
								'checklist_id' => $appr->task_list_checklist_id,
								'subchecklist_id'=>$appr->subchecklist_id,
								'rejected_region' => $appr->rejected_region,
								'image' => $subChecklistimages,
								'inspector_action'=> $task_list_subchecklist_corrective_needed->inspector_action,
								'los_action'=> $task_list_subchecklist_corrective_needed->los_action,
								'created_at'=> change_date_format($task_list_subchecklist_corrective_needed->created_at, 'Y-m-d H:i:s', 'd M Y, h:i A'),
								'updated_at'=>change_date_format($task_list_subchecklist_corrective_needed->updated_at, 'Y-m-d H:i:s', 'd M Y, h:i A'),
								//'second_checked'=> $task_list_subchecklist_corrective_action->lo_corrective_action_plan_second_check,
							];
						}
					}
				}
			
			}
			elseif($appr->approve == 1)
			{
				if($appr->type == 'checklist')
				{
					$approvedCompletedArray[] = [
						'type' => 'checklist',
						'task_id' => $appr->task_list_id,
						'checklist_id' => $appr->checklist_id,
						'rejected_region' => $appr->rejected_region,
						'inspector_action' => 1,
						'los_action' => 1,
						'created_at'=> change_date_format($appr->created_at, 'Y-m-d H:i:s', 'd M Y, h:i A'),
						'updated_at'=> change_date_format($appr->updated_at, 'Y-m-d H:i:s', 'd M Y, h:i A'),
					];
				}
				else
				{
					$approvedCompletedArray[] = [
						'type' => 'subchecklist',
						'task_id' => $appr->task_list_id,
						'checklist_id' => $appr->task_list_checklist_id,
						'subchecklist_id'=>$appr->subchecklist_id,
						'rejected_region' => $appr->rejected_region,
						'inspector_action' => 1,
						'los_action' => 1,
						'created_at'=>change_date_format($appr->created_at, 'Y-m-d H:i:s', 'd M Y, h:i A'),
						'updated_at'=>change_date_format($appr->updated_at, 'Y-m-d H:i:s', 'd M Y, h:i A'),
					];
				}
				
			}
			
		}
		
		usort($approvedCompletedArray, function ($a, $b) {
			return strtotime($b['updated_at']) <=> strtotime($a['updated_at']);
		});
		
		//echo "<pre>";print_r($approvedCompletedArray);die;
		//echo "<pre>";print_r($correctiveNeddedArray);die;
		//==========================================================
		usort($correctiveNeddedArray, function ($a, $b) {
			return strtotime($b['created_at']) <=> strtotime($a['created_at']);
		});
		// Checklist corrective action
		$data = [];
		$data['correctiveNeddedArray'] = $correctiveNeddedArray;
		$data['countNedded'] = $correctiveNeededCount;
		$data['correctiveActionArray'] = $correctiveActionArray;
		$data['countAction'] = $correctiveActionCount;
		$data['correctivePlanArray'] = $correctivePlanArray;
		$data['countPlan'] = $correctivePlanCount;
		
		$data['approvedCompletedArray'] = $approvedCompletedArray;
		$data['countCompleted'] = $correctiveApprovedCount;
		//$data['countCompleted'] = count($approvedCompletedArray);
		
		$data['correctiveAction'] = [];
		
		$data['location_id'] = Task_lists::where('id', $tid)->first()->location_id;
		$data['task_id'] = $tid;
		$data['task_name'] = '';
		$data['isactive'] = $active;
		$data['userdata'] = User::with('get_user_location')->where('id', auth()->user()->id)->first();
		
		$data['moreloadneeded'] = config('custom.LOAD_MORE_LIST_SHOW');
		$data['moreloadaction'] = config('custom.LOAD_MORE_LIST_SHOW');
		$data['moreloadplan'] = config('custom.LOAD_MORE_LIST_SHOW');
		$data['moreloadappr'] = config('custom.LOAD_MORE_LIST_SHOW');
		
		return view('management.management-task-reply-details', $data);
	}
	public function management_location_task_details_old_07_08_2025($id='', $active='')
	{
		if (auth()->user()->user_type == 1 || auth()->user()->user_type == 2 
		|| auth()->user()->user_type == 3) {
			return redirect('management-dashboard');
		}
		
		$data = [];
		//$active = 1;
		$correctiveNeddedChecklistArray = [];
		$completedApprChecklistArray = [];
		
		$correctiveNeddedSubchecklistArray = [];
		$completedApprSubcheckListArray = [];
		$categoriesArr = [];
		// -- if inspector login 
		$locationData = Task_lists::where('id', $id)->first();
		$data['location_id'] = $locationData ? $locationData->location_id : '';
		
		$data['task_id'] = $id;
		$data['task_name'] = '';
		$data['isactive'] = $active;
		
		// for corrective checked work 
		$correctiveActionChecklistArray = [];
		
		$ifTaskExists = Task_list_subcategories::where('task_list_id', $id)->exists();
		
		if($ifTaskExists)
		{
			$categoriesArr = Task_list_subcategories::where('task_list_id', $id)->pluck('task_list_category_id')->toArray();
			
			$correctiveActions = Task_list_corrective_action::where('task_list_id', $id)->whereIn('category_id', $categoriesArr)->get();
			
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
						
						$checklistFile = Task_list_checklists::with('get_checklist_files')->where('task_list_id', $id)->where('checklist_id', $correctiveAction->checklist_id)->first();
						
						$image = $checklistFile && $checklistFile->get_checklist_files->isNotEmpty() ? $checklistFile->get_checklist_files->first()->file : null;
						
					}
					else
					{
						$type = 'subchecklist';
						
						$subChecklistFile = Task_list_subchecklists::with('get_subchecklist_files')->where('task_list_id', $id)->where('task_list_checklist_id', $correctiveAction->checklist_id)->where('subchecklist_id', $correctiveAction->subchecklist_id)->first();
						
						$image = $subChecklistFile && $subChecklistFile->get_subchecklist_files->isNotEmpty() ? $subChecklistFile->get_subchecklist_files->first()->file : null;
					}
					
					$correctiveActionChecklistArray[] = [
						'type' => $type ,
						'task_id' => $id,
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
			
			//----------------------12-05-2025----------------------------
			$categoriesChecklistArr = [];
			$categoriesChecklistArr = Task_list_subcategories::where('task_list_id', $id)->pluck('task_list_category_id')->toArray();
			// checklist and  respective files approve= 0 or 1 
			$taskChklist = Task_list_checklists::where('task_list_id', $id)->whereIn('category_id', $categoriesChecklistArr)->get();
			if($taskChklist->isNotEmpty())
			{
				foreach($taskChklist as $task)
				{
					
					$task_list_checklist_corrective_needed = Task_list_corrective_action::where('task_list_id', $id)
					->where('checklist_id', $task->checklist_id)
					->first();
					if($task->approve == 0)
					{
						if(!$task_list_checklist_corrective_needed)
						{								
							$isfiles = '';
							$images = '';
							$isfiles = Task_list_checklist_rejected_files::where('task_list_checklist_id', $task->id)->first();
							
							$images = $isfiles ? $isfiles->file  : '';
								$correctiveNeddedChecklistArray[] = [
										'type' => 'checklist',
										'task_id' => $id,
										'checklist_id' => $task->checklist_id,
										'rejected_region' => $task->rejected_region,
										'image' => $images,
										'inspector_action' => '',
										'los_action' => '',
										'rejected_status' => '',
									];
						}
						else
						{
							// newimplement
							$isfiles = '';
							$images = '';
							$isfiles = Task_list_checklist_rejected_files::where('task_list_checklist_id', $task->id)->first();
							$images = $isfiles ? $isfiles->file  : '';
							$correctiveNeddedChecklistArray[] = [
								'type' => 'checklist',
								'task_id' => $id,
								'checklist_id' => $task->checklist_id,
								'rejected_region' => $task->rejected_region,
								'image' => $images,
								'inspector_action'=> $task_list_checklist_corrective_needed->inspector_action,
								'los_action'=> $task_list_checklist_corrective_needed->los_action,
								'rejected_status'=> $task_list_checklist_corrective_needed->rejected_status,
							];
							//--------
							
							$isfiles = '';
							$images = '';
							$isfiles = Task_list_checklist_rejected_files::where('task_list_checklist_id', $task->id)->first();
							$images = $isfiles ? $isfiles->file  : '';
							
							$completedApprChecklistArray[] = [
								'type' => 'checklist',
								'task_id' => $id,
								'checklist_id' => $task->checklist_id,
								'rejected_region' => $task->rejected_region,
								'image' => $images,
								'inspector_action'=> $task_list_checklist_corrective_needed->inspector_action,
								'los_action'=> $task_list_checklist_corrective_needed->los_action,
								'updated_at'=> $task_list_checklist_corrective_needed->updated_at->format('Y-m-d H:i:s'),
								//'second_checked'=> $task_list_checklist_corrective_action->lo_corrective_action_plan_second_check,
							];
											
							/*$completedApprChecklistArray[] = [
										'type' => 'checklist',
										'task_id' => $id,
										'checklist_id' => $task->checklist_id,
										'rejected_region' => $task->rejected_region,
										'image' => $images,
										'inspector_action'=> $task_list_checklist_corrective_needed->inspector_action,
										'los_action'=> $task_list_checklist_corrective_needed->los_action,
										//'second_checked'=> $task_list_checklist_corrective_action->lo_corrective_action_plan_second_check,
									];*/
						}
						
					}
					elseif($task->approve == 1)
					{
						$completedApprChecklistArray[] = [
												'type' => 'checklist',
												'task_id' => $id,
												'checklist_id' => $task->checklist_id,
												'rejected_region' => $task->rejected_region,
												'inspector_action' => 1,
												'los_action' => 1,
												'updated_at'=> $task->updated_at->format('Y-m-d H:i:s'),
											];
						/*$completedApprChecklistArray[] = [
										'type' => 'checklist',
										'task_id' => $id,
										'checklist_id' => $task->checklist_id,
										'rejected_region' => $task->rejected_region,
										'inspector_action' => 1,
										'los_action' => 1,
									];*/
					}
				}
			}
			
			// subchecklist and respective files
			
			$categoriesSubChecklistArr = [];
			$categoriesSubChecklistArr = Task_list_subcategories::where('task_list_id', $id)->pluck('task_list_category_id')->toArray();
			
			$taskSubChklist = Task_list_subchecklists::where('task_list_id', $id)->whereIn('category_id', $categoriesSubChecklistArr)->get();
			if($taskSubChklist->isNotEmpty())
			{
				foreach($taskSubChklist as $subtask)
				{
					$task_list_subchecklist_corrective_needed = Task_list_corrective_action::where('task_list_id', $id)
					->where('checklist_id', $subtask->task_list_checklist_id)
					->where('subchecklist_id', $subtask->subchecklist_id)
					->first();
					
					if($subtask->approve == 0)
					{
						if(!$task_list_subchecklist_corrective_needed)
						{
							$isSubChecklistfiles = '';
							$subChecklistimages = '';
							$isSubChecklistfiles = Task_list_subchecklist_rejected_files::where('task_list_subchecklist_id', $subtask->id)->first();
							
							$subChecklistimages = $isSubChecklistfiles ? $isSubChecklistfiles->file  : '';
							$correctiveNeddedSubchecklistArray[] = [
										'type' => 'subchecklist',
										'task_id' => $id,
										'checklist_id' => $subtask->task_list_checklist_id,
										'subchecklist_id'=>$subtask->subchecklist_id,
										'rejected_region' => $subtask->rejected_region,
										'image' => $subChecklistimages,
										'inspector_action' => '',
										'los_action' => '',
										'rejected_status' => '',
									];
						}
						else
						{
							$isSubChecklistfiles = '';
							$subChecklistimages = '';
							$isSubChecklistfiles = Task_list_subchecklist_rejected_files::where('task_list_subchecklist_id', $subtask->id)->first();
							
							$subChecklistimages = $isSubChecklistfiles ? $isSubChecklistfiles->file  : '';
							
							//  new implement
							$correctiveNeddedSubchecklistArray[] = [
									'type' => 'subchecklist',
									'task_id' => $id,
									'checklist_id' => $subtask->task_list_checklist_id,
									'subchecklist_id'=>$subtask->subchecklist_id,
									'rejected_region' => $subtask->rejected_region,
									'image' => $subChecklistimages,
									'inspector_action'=> $task_list_subchecklist_corrective_needed->inspector_action,
									'los_action'=> $task_list_subchecklist_corrective_needed->los_action,
									'rejected_status'=> $task_list_subchecklist_corrective_needed->rejected_status,
								];
							//----
							
							
							$completedApprSubcheckListArray[] = [
										'type' => 'subchecklist',
										'task_id' => $id,
										'checklist_id' => $subtask->task_list_checklist_id,
										'subchecklist_id'=>$subtask->subchecklist_id,
										'rejected_region' => $subtask->rejected_region,
										'image' => $subChecklistimages,
										'inspector_action'=> $task_list_subchecklist_corrective_needed->inspector_action,
										'los_action'=> $task_list_subchecklist_corrective_needed->los_action,
										//'second_checked'=> $task_list_subchecklist_corrective_action->lo_corrective_action_plan_second_check,
									];
							
						}
					}
					elseif($subtask->approve == 1)
					{
						$completedApprSubcheckListArray[] = [
								'type' => 'subchecklist',
								'task_id' => $id,
								'checklist_id' => $subtask->task_list_checklist_id,
								'subchecklist_id'=>$subtask->subchecklist_id,
								'rejected_region' => $subtask->rejected_region,
								'inspector_action' => 1,
								'los_action' => 1,
							];
						
					}
				}
				
			}
		} // task complete
		
		//-----------12-06-2025--------------------
		$data['correctiveNeeded'] = array_merge($correctiveNeddedChecklistArray,$correctiveNeddedSubchecklistArray);
		$data['approvedCompleted'] = array_merge($completedApprChecklistArray,$completedApprSubcheckListArray);
		//------------------------------------------
		//echo "<pre>";print_r($correctiveActionChecklistArray);die;
		$data['correctiveAction'] = $correctiveActionChecklistArray;
		
		//-----------------------
		$correctiveNeeded = array_merge($correctiveNeddedChecklistArray,$correctiveNeddedSubchecklistArray);
		//echo "<pre>";print_r($correctiveNeeded);die;
		$correctiveNeddedArray = [];
		foreach($correctiveNeeded as $needed)
		{
			if(($needed['inspector_action']=='' && $needed['inspector_action']=='') || ($needed['inspector_action']== 2 && $needed['inspector_action']==2))
			{
				if(isset($needed['subchecklist_id']))
				{
					$correctiveNeddedArray[] = [
						'type' => $needed['type'],
						'task_id' => $needed['task_id'],
						'checklist_id' => $needed['checklist_id'],
						'subchecklist_id' => $needed['subchecklist_id'],
						'rejected_region' => $needed['rejected_region'],
						'image' => $needed['image'],
						'inspector_action'=> $needed['inspector_action'],
						'los_action'=> $needed['los_action'],
						'rejected_status'=> $needed['rejected_status'],
					];
				}
				else
				{
					$correctiveNeddedArray[] = [
						'type' => $needed['type'],
						'task_id' => $needed['task_id'],
						'checklist_id' => $needed['checklist_id'],
						'rejected_region' => $needed['rejected_region'],
						'image' => $needed['image'],
						'inspector_action'=> $needed['inspector_action'],
						'los_action'=> $needed['los_action'],
						'rejected_status'=> $needed['rejected_status'],
					];
				}
			}
		}
		
		//echo "<pre>";print_r($correctiveNeeded);die;
		$data['correctiveNeddedArray'] = array_slice($correctiveNeddedArray, 0, config('custom.LOAD_MORE_LIST_SHOW'));
		$data['moreloadneeded'] = config('custom.LOAD_MORE_LIST_SHOW');
		
		//================corrective action==================
		$correctiveActionArray = [];
		$data['correctiveAction'] = $correctiveActionChecklistArray;
		$correctiveAction  = $correctiveActionChecklistArray;
		//echo "<pre>";print_r($correctiveAction);
		foreach($correctiveAction as $action)
		{
			if($action['lo_direct_approve'] == 1 && ($action['inspector_action'] == 0 || $action['los_action'] == 0))
			{
				$correctiveActionArray[] = [
					'type' => $action['type'],
					'task_id' => $action['task_id'],
					'checklist_id' => $action['checklist_id'],
					'subchecklist_id' => $action['subchecklist_id'],
					'rejected_region' => $action['rejected_region'],
					'inspector_action' => $action['inspector_action'],
					'los_action' => $action['los_action'],
					'second_checked' => $action['second_checked'],
					'lo_direct_approve' => $action['lo_direct_approve'],
					'image' => $action['image'],
				];
			}
		}
		//echo "<pre>";print_r($correctiveActionArray);die;
		$data['correctiveActionArray'] = array_slice($correctiveActionArray, 0, config('custom.LOAD_MORE_LIST_SHOW'));
		$data['moreloadaction'] = config('custom.LOAD_MORE_LIST_SHOW');
		
		//=================corrective plan=============
		$correctivePlanArray = [];
		//$data['correctiveAction'] = $correctiveActionChecklistArray;
		$correctivePlan  = $correctiveActionChecklistArray;
		//echo "<pre>";print_r($correctiveAction);
		foreach($correctivePlan as $plan)
		{
			if($plan['lo_direct_approve'] == 0 && ($plan['inspector_action'] == 0 || $plan['los_action'] == 0))
			{
				$correctivePlanArray[] = [
					'type' => $plan['type'],
					'task_id' => $plan['task_id'],
					'checklist_id' => $plan['checklist_id'],
					'subchecklist_id' => $plan['subchecklist_id'],
					'rejected_region' => $plan['rejected_region'],
					'inspector_action' => $plan['inspector_action'],
					'los_action' => $plan['los_action'],
					'second_checked' => $plan['second_checked'],
					'lo_direct_approve' => $plan['lo_direct_approve'],
					'image' => $plan['image'],
				];
			}
		}
		//echo "<pre>";print_r($correctiveActionArray);die;
		$data['correctivePlanArray'] = array_slice($correctivePlanArray, 0, config('custom.LOAD_MORE_LIST_SHOW'));
		$data['moreloadplan'] = config('custom.LOAD_MORE_LIST_SHOW');
		//==================Approved/completed=================
		
		$approvedCompletedArray = [];
		$approvedCompleted = array_merge($completedApprChecklistArray,$completedApprSubcheckListArray);
		
		usort($approvedCompleted, function ($a, $b) {
			$a_time = isset($a['updated_at']) ? strtotime($a['updated_at']) : 0;
			$b_time = isset($b['updated_at']) ? strtotime($b['updated_at']) : 0;
			return $b_time <=> $a_time; // Descending
		});
		/*usort($approvedCompleted, function ($a, $b) {
			return strtotime($b['updated_at']) <=> strtotime($a['updated_at']);
		});*/
		//echo "<pre>";print_r($approvedCompleted);die;
		//$data['approvedCompleted'] = $approvedCompleted;
		foreach($approvedCompleted as $appr)
		{
			if($appr['inspector_action'] == 1 && $appr['los_action'] == 1)
			{
				if(isset($appr['subchecklist_id']))
				{
					if(isset($appr['image']))
					{
						$approvedCompletedArray[] = [
							'type' => $appr['type'],
							'task_id' => $appr['task_id'],
							'checklist_id' => $appr['checklist_id'],
							'subchecklist_id'=>$appr['subchecklist_id'],
							'rejected_region' => $appr['rejected_region'],
							'image' => $appr['image'],
							'inspector_action' => $appr['inspector_action'],
							'los_action' => $appr['los_action'],
						];
					}
					else{
						$approvedCompletedArray[] = [
							'type' => $appr['type'],
							'task_id' => $appr['task_id'],
							'checklist_id' => $appr['checklist_id'],
							'subchecklist_id'=>$appr['subchecklist_id'],
							'rejected_region' => $appr['rejected_region'],
							'inspector_action' => $appr['inspector_action'],
							'los_action' => $appr['los_action'],
						];
					}
					
				}
				else
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
							'los_action' => $appr['los_action'],
						];
					}
					else{
						$approvedCompletedArray[] = [
							'type' => $appr['type'],
							'task_id' => $appr['task_id'],
							'checklist_id' => $appr['checklist_id'],
							'rejected_region' => $appr['rejected_region'],
							'inspector_action' => $appr['inspector_action'],
							'los_action' => $appr['los_action'],
						];
					}
					
				}
			}
		}
		$data['userdata'] = User::with('get_user_location')->where('id', auth()->user()->id)->first();
		//echo "<pre>";print_r($approvedCompletedArray);die;
		$data['approvedCompletedArray'] = array_slice($approvedCompletedArray, 0, config('custom.LOAD_MORE_LIST_SHOW'));
		$data['moreloadappr'] = config('custom.LOAD_MORE_LIST_SHOW');
		return view('management.management-task-reply-details', $data);
	}
	public function management_checklist_question_reply($location_id='',$task_id='',$checklist_id='',$type='', $tab='')
	{
		if (auth()->user()->user_type == 1 || auth()->user()->user_type == 2 
		|| auth()->user()->user_type == 3) {
			return redirect('management-dashboard');
		}
		
		$data = [];
		$data['task_id'] = $task_id ?? '';
		$data['location_id'] = $location_id ?? '';
		$data['checklist_id'] = $checklist_id ?? '';
		$data['type'] = $type ?? '';
		$data['tab'] = $tab ?? '';
		
		$corrective_actions_data  	= Task_list_corrective_action::where('task_list_id', $task_id)->where('checklist_id', $checklist_id)->first();
		$data['inspector_action'] 	= $corrective_actions_data ? $corrective_actions_data->inspector_action : '';
		$data['los_action']  		= $corrective_actions_data ? $corrective_actions_data->los_action : '';
		return view('management.management-check-reply', $data);
	}
	public function management_subchecklist_question_reply($location_id='',$task_id='',$checklist_id='',$subchecklist_id='',$type='', $tab='')
	{
		if (auth()->user()->user_type == 1 || auth()->user()->user_type == 2 
		|| auth()->user()->user_type == 3) {
			return redirect('management-dashboard');
		}
		
		$data = [];
		$data['task_id'] = $task_id ?? '';
		$data['location_id'] = $location_id ?? '';
		$data['checklist_id'] = $checklist_id ?? '';
		$data['subchecklist_id'] = $subchecklist_id ?? '';
		$data['type'] = $type ?? '';
		$data['tab'] = $tab ?? '';
		$corrective_actions_data  	= Task_list_corrective_action::where('task_list_id', $task_id)->where('checklist_id', $checklist_id)->where('subchecklist_id', $subchecklist_id)->first();
		$data['inspector_action'] 	= $corrective_actions_data ? $corrective_actions_data->inspector_action : '';
		$data['los_action']  		= $corrective_actions_data ? $corrective_actions_data->los_action : '';
		return view('management.management-check-reply', $data);
	}
	public function management_checklist_second_approve_by_lo($location_id='',$task_id='',$checklist_id='',$type='', $tab='')
	{
		if (auth()->user()->user_type == 1 || auth()->user()->user_type == 2 
		|| auth()->user()->user_type == 3) {
			return redirect('management-dashboard');
		}
		
		$data = [];
		$data['task_id'] = $task_id ?? '';
		$data['location_id'] = $location_id ?? '';
		$data['checklist_id'] = $checklist_id ?? '';
		$data['type'] = $type ?? '';
		$data['tab'] = $tab ?? '';
		
		$corrective_actions_data  	= Task_list_corrective_action::where('task_list_id', $task_id)->where('checklist_id', $checklist_id)->first();
		$data['inspector_action'] 	= $corrective_actions_data ? $corrective_actions_data->inspector_action : '';
		$data['los_action']  		= $corrective_actions_data ? $corrective_actions_data->los_action : '';
		$data['lo_corrective_action_plan_second_check']  		= $corrective_actions_data ? $corrective_actions_data->lo_corrective_action_plan_second_check : '';
		
		return view('management.management-check-reply-approved-by-lo', $data);
	}
	public function management_subchecklist_second_approve_by_lo($location_id='',$task_id='',$checklist_id='',$subchecklist_id='',$type='', $tab='')
	{
		if (auth()->user()->user_type == 1 || auth()->user()->user_type == 2 
		|| auth()->user()->user_type == 3) {
			return redirect('management-dashboard');
		}
		
		$data = [];
		$data['task_id'] = $task_id ?? '';
		$data['location_id'] = $location_id ?? '';
		$data['checklist_id'] = $checklist_id ?? '';
		$data['subchecklist_id'] = $subchecklist_id ?? '';
		$data['type'] = $type ?? '';
		$data['tab'] = $tab ?? '';
		
		$corrective_actions_data  	= Task_list_corrective_action::where('task_list_id', $task_id)->where('checklist_id', $checklist_id)->where('subchecklist_id', $subchecklist_id)->first();
		$data['inspector_action'] 	= $corrective_actions_data ? $corrective_actions_data->inspector_action : '';
		$data['los_action']  		= $corrective_actions_data ? $corrective_actions_data->los_action : '';
		
		return view('management.management-check-reply-approved-by-lo', $data);
	}
	public function management_checklist_completed_approved_view($location_id='',$task_id='',$checklist_id='',$type='', $tab='')
	{
		if (auth()->user()->user_type == 1 || auth()->user()->user_type == 2 
		|| auth()->user()->user_type == 3) {
			return redirect('management-dashboard');
		}
		
		$data = [];
		$data['task_id'] = $task_id ?? '';
		$data['location_id'] = $location_id ?? '';
		$data['checklist_id'] = $checklist_id ?? '';
		$data['type'] = $type ?? '';
		$data['tab'] = $tab ?? '';
		
		$corrective_actions_data  	= Task_list_corrective_action::where('task_list_id', $task_id)->where('checklist_id', $checklist_id)->first();
		$data['inspector_action'] 	= $corrective_actions_data ? $corrective_actions_data->inspector_action : '';
		$data['los_action']  		= $corrective_actions_data ? $corrective_actions_data->los_action : '';
		$data['lo_corrective_action_plan_second_check']  		= $corrective_actions_data ? $corrective_actions_data->lo_corrective_action_plan_second_check : '';
		
		return view('management.management-completed-approved-view', $data);
	}
	public function management_subchecklist_completed_approved_view($location_id='',$task_id='',$checklist_id='',$subchecklist_id='',$type='', $tab='')
	{
		if (auth()->user()->user_type == 1 || auth()->user()->user_type == 2 
		|| auth()->user()->user_type == 3) {
			return redirect('management-dashboard');
		}
		
		$data = [];
		$data['task_id'] = $task_id ?? '';
		$data['location_id'] = $location_id ?? '';
		$data['checklist_id'] = $checklist_id ?? '';
		$data['subchecklist_id'] = $subchecklist_id ?? '';
		$data['type'] = $type ?? '';
		$data['tab'] = $tab ?? '';
		
		$corrective_actions_data  	= Task_list_corrective_action::where('task_list_id', $task_id)->where('checklist_id', $checklist_id)->where('subchecklist_id', $subchecklist_id)->first();
		$data['inspector_action'] 	= $corrective_actions_data ? $corrective_actions_data->inspector_action : '';
		$data['los_action']  		= $corrective_actions_data ? $corrective_actions_data->los_action : '';
		
		return view('management.management-completed-approved-view', $data);
	}
	
	public function mgnt_load_more_data(Request $request)
	{
		$location_id = $request->location_id;
		$task_id = $request->task_id;
		$data = [];
		$interval = config('custom.LOAD_MORE_INTERVAL');
		$lower = empty($request->moreload) ? 0 : $request->moreload;
		$upper = empty($request->moreload) ? config('custom.LOAD_MORE_LIST_SHOW') : config('custom.LOAD_MORE_INTERVAL');
		$tab = $request->tab;
		//---------------------------------------------------
		
		$correctiveNeddedChecklistArray = [];
		$completedApprChecklistArray = [];
		
		$correctiveNeddedSubchecklistArray = [];
		$completedApprSubcheckListArray = [];
		// -- if inspector login 
		
		$data['location_id'] = $location_id;
		
		$data['task_id'] ='';
		$data['task_name'] = '';
		
		// for corrective checked work 
		$correctiveActionChecklistArray = [];
		
		$taskData = Task_lists::where('id', $task_id)->get();
		if($taskData->isNotEmpty())
		{
			foreach($taskData as $val)
			{
				$ifTaskRxists = Task_list_subcategories::where('task_list_id', $val->id)->exists();
				if($ifTaskRxists)
				{
					//$correctiveActions = Task_list_corrective_action::where('task_list_id', $val->id)->where('inspector_id', auth()->user()->id)->get();
					
					//-- 10-07-2025--
					///$correctiveActions = Task_list_corrective_action::where('task_list_id', $val->id)->get();
					//----------
					$categoriesArr = Task_list_subcategories::where('task_list_id', $val->id)->pluck('task_list_category_id')->toArray();
					
					$correctiveActions = Task_list_corrective_action::where('task_list_id', $val->id)->whereIn('category_id', $categoriesArr)->get();
					
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
								
								$checklistFile = Task_list_checklists::with('get_checklist_files')->where('task_list_id', $val->id)->where('checklist_id', $correctiveAction->checklist_id)->first();
								
								$image = $checklistFile && $checklistFile->get_checklist_files->isNotEmpty() ? $checklistFile->get_checklist_files->first()->file : null;
								
							}
							else
							{
								$type = 'subchecklist';
								
								$subChecklistFile = Task_list_subchecklists::with('get_subchecklist_files')->where('task_list_id', $val->id)->where('task_list_checklist_id', $correctiveAction->checklist_id)->where('subchecklist_id', $correctiveAction->subchecklist_id)->first();
								
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
					
					//-------------12-05-2025------------
					// checklist and  respective files approve=1 
					
					//---- 10-07-2025---
					//$taskChklist = Task_list_checklists::where('task_list_id', $val->id)->get();
					//-------------------
					$categoriesChecklistArr = [];
					$categoriesChecklistArr = Task_list_subcategories::where('task_list_id', $val->id)->pluck('task_list_category_id')->toArray();
					
					$taskChklist = Task_list_checklists::where('task_list_id', $val->id)->whereIn('category_id', $categoriesChecklistArr)->get();
					
					if($taskChklist->isNotEmpty())
					{
						foreach($taskChklist as $task)
						{
							
							$task_list_checklist_corrective_needed = Task_list_corrective_action::where('task_list_id', $val->id)
							->where('checklist_id', $task->checklist_id)
							->first();
							if($task->approve == 0)
							{
								if(!$task_list_checklist_corrective_needed)
								{								
									$isfiles = '';
									$images = '';
									$isfiles = Task_list_checklist_rejected_files::where('task_list_checklist_id', $task->id)->first();
									
									$images = $isfiles ? $isfiles->file  : '';
										$correctiveNeddedChecklistArray[] = [
												'type' => 'checklist',
												'task_id' => $val->id,
												'checklist_id' => $task->checklist_id,
												'rejected_region' => $task->rejected_region,
												'image' => $images,
												'inspector_action' => '',
												'los_action' => '',
												'rejected_status' => '',
											];
								}
								else
								{
									// newimplement
									$isfiles = '';
									$images = '';
									$isfiles = Task_list_checklist_rejected_files::where('task_list_checklist_id', $task->id)->first();
									$images = $isfiles ? $isfiles->file  : '';
									$correctiveNeddedChecklistArray[] = [
										'type' => 'checklist',
										'task_id' => $val->id,
										'checklist_id' => $task->checklist_id,
										'rejected_region' => $task->rejected_region,
										'image' => $images,
										'inspector_action'=> $task_list_checklist_corrective_needed->inspector_action,
										'los_action'=> $task_list_checklist_corrective_needed->los_action,
										'rejected_status'=> $task_list_checklist_corrective_needed->rejected_status,
									];
									//--------
									
									$isfiles = '';
									$images = '';
									$isfiles = Task_list_checklist_rejected_files::where('task_list_checklist_id', $task->id)->first();
									$images = $isfiles ? $isfiles->file  : '';
									$completedApprChecklistArray[] = [
												'type' => 'checklist',
												'task_id' => $val->id,
												'checklist_id' => $task->checklist_id,
												'rejected_region' => $task->rejected_region,
												'image' => $images,
												'inspector_action'=> $task_list_checklist_corrective_needed->inspector_action,
												'los_action'=> $task_list_checklist_corrective_needed->los_action,
												'updated_at'=> $task_list_checklist_corrective_needed->updated_at->format('Y-m-d H:i:s'),
												//'second_checked'=> $task_list_checklist_corrective_action->lo_corrective_action_plan_second_check,
											];
								}
								
							}
							elseif($task->approve == 1)
							{
								$completedApprChecklistArray[] = [
												'type' => 'checklist',
												'task_id' => $val->id,
												'checklist_id' => $task->checklist_id,
												'rejected_region' => $task->rejected_region,
												'inspector_action' => 1,
												'los_action' => 1,
												'updated_at'=> $task->updated_at->format('Y-m-d H:i:s'),
											];
							}
						}
					}
					
					// subchecklist and respective files
					//--------10-07-2025----
					//$taskSubChklist = Task_list_subchecklists::where('task_list_id', $val->id)->get();
					//---------------
					
					$categoriesSubChecklistArr = [];
					$categoriesSubChecklistArr = Task_list_subcategories::where('task_list_id', $val->id)->pluck('task_list_category_id')->toArray();
					
					$taskSubChklist = Task_list_subchecklists::where('task_list_id', $val->id)->whereIn('category_id', $categoriesSubChecklistArr)->get();
					
					if($taskSubChklist->isNotEmpty())
					{
						foreach($taskSubChklist as $subtask)
						{
							$task_list_subchecklist_corrective_needed = Task_list_corrective_action::where('task_list_id', $val->id)
							->where('checklist_id', $subtask->task_list_checklist_id)
							->where('subchecklist_id', $subtask->subchecklist_id)
							->first();
							
							if($subtask->approve == 0)
							{
								if(!$task_list_subchecklist_corrective_needed)
								{
									$isSubChecklistfiles = '';
									$subChecklistimages = '';
									$isSubChecklistfiles = Task_list_subchecklist_rejected_files::where('task_list_subchecklist_id', $subtask->id)->first();
									
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
												'rejected_status' => '',
											];
								}
								else
								{
									$isSubChecklistfiles = '';
									$subChecklistimages = '';
									$isSubChecklistfiles = Task_list_subchecklist_rejected_files::where('task_list_subchecklist_id', $subtask->id)->first();
									
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
											'rejected_status'=> $task_list_subchecklist_corrective_needed->rejected_status,
										];
									//----
									
									
									$completedApprSubcheckListArray[] = [
												'type' => 'subchecklist',
												'task_id' => $val->id,
												'checklist_id' => $subtask->task_list_checklist_id,
												'subchecklist_id'=>$subtask->subchecklist_id,
												'rejected_region' => $subtask->rejected_region,
												'image' => $subChecklistimages,
												'inspector_action'=> $task_list_subchecklist_corrective_needed->inspector_action,
												'los_action'=> $task_list_subchecklist_corrective_needed->los_action,
												'updated_at'=> $task_list_subchecklist_corrective_needed->updated_at->format('Y-m-d H:i:s'),
												//'second_checked'=> $task_list_subchecklist_corrective_action->lo_corrective_action_plan_second_check,
											];
									
								}
							}
							elseif($subtask->approve == 1)
							{
								$completedApprSubcheckListArray[] = [
										'type' => 'subchecklist',
										'task_id' => $val->id,
										'checklist_id' => $subtask->task_list_checklist_id,
										'subchecklist_id'=>$subtask->subchecklist_id,
										'rejected_region' => $subtask->rejected_region,
										'inspector_action' => 1,
										'los_action' => 1,
										'updated_at'=> $subtask->updated_at->format('Y-m-d H:i:s'),
									];
								
							}
						}
						
					}
					
				}
					
			} // task array end 
		}
		
		//-------------------------------
		$countNedded = 0;
		$countAction = 0;
		$countPlan = 0;
		$countCompleted = 0;
		$count  = $request->moreload =='' ? config('custom.LOAD_MORE_LIST_SHOW') : $request->moreload + $interval;
		
		if($request->tab == 'correctiveneeded')
		{
			$data['correctiveNeeded'] = array_merge($correctiveNeddedChecklistArray,$correctiveNeddedSubchecklistArray);
			$data['mode'] = 'corrective_needed';
			$correctiveNeeded = array_merge($correctiveNeddedChecklistArray,$correctiveNeddedSubchecklistArray);
			$data['mode'] = 'corrective_needed';
			foreach($correctiveNeeded as $result)
			{
				if(($result['inspector_action']=='' && $result['inspector_action']=='') || ($result['inspector_action']== 2 && $result['inspector_action']==2))
				{
					$countNedded++;
				}
			}
			$totalRecord = $countNedded;
		}
		
		if($request->tab == 'correctiveaction')
		{
			$data['correctiveAction'] = $correctiveActionChecklistArray;
			$data['mode'] = 'corrective_action';
			
			foreach($correctiveActionChecklistArray as $result)
			{
				if($result['lo_direct_approve'] == 1 && ($result['inspector_action'] == 0 || $result['los_action'] == 0))
				{
					$countAction++;
				}
			}
			$totalRecord = $countAction;
		}
		
		if($request->tab == 'correctiveplan')
		{
			$data['correctiveAction'] = $correctiveActionChecklistArray;
			$data['mode'] = 'corrective_plan';
			
			foreach($correctiveActionChecklistArray as $result)
			{
				
				if($result['lo_direct_approve'] == 0 && ($result['inspector_action'] == 0 || $result['los_action'] == 0))
				{
					$countPlan++;
				}
			}
			$totalRecord = $countPlan;
		}
		
		if($request->tab == 'correctiveapproved')
		{
			$data['approvedCompleted'] = array_merge($completedApprChecklistArray,$completedApprSubcheckListArray);
			$data['mode'] = 'corrective_appr';
			$apprArray = array_merge($completedApprChecklistArray,$completedApprSubcheckListArray);
			
			foreach($apprArray as $result)
			{
				if($result['inspector_action'] == 1 && $result['los_action'] == 1)
				{
					$countCompleted++;
				}
			}
			$totalRecord = $countCompleted;
		}
		
		
		//-------------------------------------
		$data['location_id'] = $location_id;
		$data['lower'] = $lower;
		$data['upper'] = $upper;
		$html = view('management.loadmore.mgnt-load-more-data', $data)->render();
		
		//--------------------------------------
		//$count  = $request->moreload =='' ? config('custom.LOAD_MORE_LIST_SHOW') : $request->moreload + $interval;
		$remain = $totalRecord - $count;
		
		return response()->json(['location_id'=> $location_id, 'html'=> $html, 'loadmore'=> $lower+$upper, 'remain'=> $remain]);
	}
	public function mgnt_load_more_needed_data(Request $request)
	{
		$location_id = $request->location_id;
		
		
		$data = array();
		$interval = config('custom.LOAD_MORE_INTERVAL');
		$lower = empty($request->moreload) ? 0 : $request->moreload;
		$upper = empty($request->moreload) ? config('custom.LOAD_MORE_LIST_SHOW') : config('custom.LOAD_MORE_INTERVAL');
		//-----------------------------------------------
		/*$taskListIds = Task_lists::where('location_id', $location_id)
			->where('inspector_id', auth()->user()->id)
			->pluck('id');*/
			
		//$taskListIds = Task_lists::where('location_id', $location_id)->pluck('id');
		
		$task_id = $request->task_id;
		$taskListIds = array($task_id);

		$categoryIds = Task_list_subcategories::whereIn('task_list_id', $taskListIds)
			->pluck('task_list_category_id');
			
		//-----05-08-2025 if submit checklist then submit task data show-----
		$submit_task_id = Task_list_subcategories::whereIn('task_list_category_id', $categoryIds)->pluck('task_list_id')->toArray();
		//---
		
		// Checklist IDs with existing corrective needed
		$excludedChecklistPairs = Task_list_corrective_action::whereNotIn('rejected_repeated', [0])
			->whereIn('task_list_id', $taskListIds)
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
		  //echo "<pre>";print_r($excludedChecklistPairs);die;
			
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
		//-----------subchecklist-----------------------
		$excludedSubChecklistPairs = Task_list_corrective_action::whereNotIn('rejected_repeated', [0])
			->whereIn('task_list_id', $taskListIds)
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
			
			//echo "<pre>";print_r($excludedSubChecklistPairs);die;
			
			$correctiveSubChecklistIds = DB::table('task_list_subchecklists')
			->where('approve', 0)
			->whereIn('task_list_id', $taskListIds)
			->whereIn('category_id', $categoryIds)
			->get(['task_list_id', 'subchecklist_id', 'task_list_checklist_id'])
			->filter(function ($item) use ($excludedSubChecklistPairs) {
				$pairKey = $item->task_list_id . '-' . $item->task_list_checklist_id;
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
		

		// Raw union query
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

			// Merge both queries using unionAll
			$query->fromSub($baseQuery->unionAll($unionQuery), 'combined');
		}, 'combined')
			->orderByDesc('updated_at')
			->offset($lower)
			->limit($upper)
			->get();
		
		
		$correctiveNeddedArray = [];
		foreach($correctiveneeded as $needed)
		{
			if($needed->type == 'checklist')
			{
				//echo $needed->checklist_id."</br>";
				$task_list_checklist_corrective_needed = Task_list_corrective_action::where('task_list_id', $needed->task_list_id)->where('checklist_id', $needed->checklist_id)->first();
				
				$checklistData = Task_list_checklists::where('checklist_id', $needed->checklist_id)->where('task_list_id', $needed->task_list_id)->first();
				$id = $checklistData ? $checklistData->id : '';
					
				if(!$task_list_checklist_corrective_needed)
				{
					$isfiles = '';
					$images = '';
					
					$isfiles = Task_list_checklist_rejected_files::where('task_list_checklist_id', $id)->first();
					
					$images = $isfiles ? $isfiles->file  : '';
				
					$correctiveNeddedArray[] = [
						'type' => 'checklist',
						'task_id' => $needed->task_list_id,
						'checklist_id' => $needed->checklist_id,
						'rejected_region' => $checklistData->rejected_region,
						'image' => $images,
						'inspector_action' => '',
						'los_action' => '',
						'rejected_status' => '',
					];
				}
				else
				{
					$isfiles = '';
					$images = '';
					$isfiles = Task_list_checklist_rejected_files::where('task_list_checklist_id', $id)->first();
					
					$images = $isfiles ? $isfiles->file  : '';
					$correctiveNeddedArray[] = [
						'type' => 'checklist',
						'task_id' => $needed->task_list_id,
						'checklist_id' => $needed->checklist_id,
						'rejected_region' => $checklistData->rejected_region,
						'image' => $images,
						'inspector_action'=> $task_list_checklist_corrective_needed->inspector_action,
						'los_action'=> $task_list_checklist_corrective_needed->los_action,
						'rejected_status'=> $task_list_checklist_corrective_needed->rejected_status,
					];
				}
			}
			else if($needed->type == 'subchecklist')
			{
				$task_list_subchecklist_corrective_needed = Task_list_corrective_action::where('task_list_id', $needed->task_list_id)->where('checklist_id', $needed->task_list_checklist_id)->where('subchecklist_id', $needed->subchecklist_id)->first();
							
				$subchecklistData = Task_list_subchecklists::where('task_list_id', $needed->task_list_id)->where('task_list_checklist_id', $needed->task_list_checklist_id)->where('subchecklist_id',$needed->subchecklist_id)->first();
				$id = $subchecklistData ? $subchecklistData->id : '';
							
				if(!$task_list_subchecklist_corrective_needed)
				{
					$isSubChecklistfiles = '';
					$subChecklistimages = '';
					$isSubChecklistfiles = Task_list_subchecklist_rejected_files::where('task_list_subchecklist_id', $id)->first();
					$subChecklistimages = $isSubChecklistfiles ? $isSubChecklistfiles->file  : '';
									
					$correctiveNeddedArray[] = [
						'type' => 'subchecklist',
						'task_id' => $needed->task_list_id,
						'checklist_id' => $needed->task_list_checklist_id,
						'subchecklist_id'=>$needed->subchecklist_id,
						'rejected_region' => $subchecklistData->rejected_region,
						'image' => $subChecklistimages,
						'inspector_action' => '',
						'los_action' => '',
						'rejected_status' => '',
					];
				}
				else
				{
					$isSubChecklistfiles = '';
					$subChecklistimages = '';
					$isSubChecklistfiles = Task_list_subchecklist_rejected_files::where('task_list_subchecklist_id', $id)->first();
					$subChecklistimages = $isSubChecklistfiles ? $isSubChecklistfiles->file  : '';
					
					$correctiveNeddedArray[] = [
						'type' => 'subchecklist',
						'task_id' => $needed->task_list_id,
						'checklist_id' => $needed->task_list_checklist_id,
						'subchecklist_id'=>$needed->subchecklist_id,
						'rejected_region' => $subchecklistData->rejected_region,
						'image' => $subChecklistimages,
						'inspector_action'=> $task_list_subchecklist_corrective_needed->inspector_action,
						'los_action'=> $task_list_subchecklist_corrective_needed->los_action,
						'rejected_status'=> $task_list_subchecklist_corrective_needed->rejected_status,
					];
				}
			}
			
		}
		//----------------------------------------------
		//echo "<pre>";print_r($correctiveneeded);die;
		
		//$totalRecord = $correctiveNeddedArray;
		//echo count($totalRecord);
		//echo "<pre>";print_r($totalRecord);die;
		//$correctiveNeddedArray = array_slice($correctiveNeddedArray, $lower, $upper);
		//echo "<pre>";print_r($correctiveNeddedArray);die;
		$data['location_id'] = $location_id;
		$data['mode'] = 'corrective_needed';
		$data['correctiveNeddedArray'] = $correctiveNeddedArray;
		$html = view('management.loadmore.mgnt-load-more-data', $data)->render();
		//---------------------------------------------
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
		
		$count  = $request->moreload =='' ? config('custom.LOAD_MORE_LIST_SHOW') : $request->moreload + $interval;
		//echo count($totalRecord) .' '.$count; die;
		$remain = $correctiveNeededCount - $count;
		
		return response()->json(['location_id'=> $location_id, 'html'=> $html, 'loadmore'=> $lower+$upper, 'remain'=> $remain]);
	}
	public function mgnt_load_more_action_data(Request $request)
	{
		$location_id = $request->location_id;
		
		$data = array();
		$interval = config('custom.LOAD_MORE_INTERVAL');
		$lower = empty($request->moreload) ? 0 : $request->moreload;
		$upper = empty($request->moreload) ? config('custom.LOAD_MORE_LIST_SHOW') : config('custom.LOAD_MORE_INTERVAL');
		
		//----------------------------------------
		/*$taskListIds = Task_lists::where('location_id', $location_id)
			->where('inspector_id', auth()->user()->id)
			->pluck('id');*/
		//$taskListIds = Task_lists::where('location_id', $location_id)->pluck('id');
		
		$task_id = $request->task_id;
		$taskListIds = array($task_id);

		$categoryIds = Task_list_subcategories::whereIn('task_list_id', $taskListIds)
			->pluck('task_list_category_id');
			
		$correctiveActionArray = [];
		
		$correctiveActionData = Task_list_corrective_action::whereIn('lo_direct_approve', [1])->whereIn('task_list_id', $taskListIds)->whereIn('category_id', $categoryIds)
			->where(function ($q) {
				$q->where(function ($q) {
					$q->where('inspector_action', 0)->where('los_action', 1);
				})->orWhere(function ($q) {
					$q->where('inspector_action', 1)->where('los_action', 0);
				})->orWhere(function ($q) {
					$q->where('inspector_action', 0)->where('los_action', 0);
				});
			})
			->offset($lower)
			->limit($upper)
			->get();
			//->whereNotNull('checklist_id')
			//->pluck('checklist_id')->toArray();
		//echo "<pre>";print_r($correctiveActionData);die;
		foreach($correctiveActionData as $action)
		{
			//echo $action->checklist_id. "</br>";
			
			$type = '';
			$image = '';
			if($action->subchecklist_id == null)
			{
				$type = 'checklist';
				
				$checklistFile = Task_list_checklists::with('get_checklist_files')->where('task_list_id', $action->task_list_id)->where('checklist_id', $action->checklist_id)->first();
				
				$image = $checklistFile && $checklistFile->get_checklist_files->isNotEmpty() ? $checklistFile->get_checklist_files->first()->file : null;
				
			}
			else
			{
				$type = 'subchecklist';
				
				$subChecklistFile = Task_list_subchecklists::with('get_subchecklist_files')->where('task_list_id', $action->task_list_id)->where('task_list_checklist_id', $action->checklist_id)->where('subchecklist_id', $action->subchecklist_id)->first();
				
				$image = $subChecklistFile && $subChecklistFile->get_subchecklist_files->isNotEmpty() ? $subChecklistFile->get_subchecklist_files->first()->file : null;
			}
			
			$correctiveActionArray[] = [
				'type' => $type ,
				'task_id' => $action->task_list_id,
				'checklist_id' => $action->checklist_id,
				'subchecklist_id' => $action->subchecklist_id,
				'rejected_region' => $action->lo_corrective_action_plan,
				'inspector_action' => $action->inspector_action,
				'los_action' => $action->los_action,
				'second_checked' => $action->lo_corrective_action_plan_second_check,
				'lo_direct_approve' => $action->lo_direct_approve,
				'image' => $image,
			];
			
		}
		//---------------------------------------
		$correctiveActionCount = Task_list_corrective_action::whereIn('lo_direct_approve', [1])->whereIn('task_list_id', $taskListIds)->whereIn('category_id', $categoryIds)
			->where(function ($q) {
				$q->where(function ($q) {
					$q->where('inspector_action', 0)->where('los_action', 1);
				})->orWhere(function ($q) {
					$q->where('inspector_action', 1)->where('los_action', 0);
				})->orWhere(function ($q) {
					$q->where('inspector_action', 0)->where('los_action', 0);
				});
			})->count();
			
		//echo "<pre>";print_r($correctiveActionArray);die;
		$data['location_id'] = $location_id;
		$data['mode'] = 'corrective_action';
		$data['correctiveActionArray'] = $correctiveActionArray;
		$html = view('management.loadmore.mgnt-load-more-data', $data)->render();
		//----------------
		$count  = $request->moreload =='' ? config('custom.LOAD_MORE_LIST_SHOW') : $request->moreload + $interval;
		$remain = $correctiveActionCount - $count;
		
		return response()->json(['location_id'=> $location_id, 'html'=> $html, 'loadmore'=> $lower+$upper, 'remain'=> $remain]);
			
			
	}
	public function mgnt_load_more_plan_data(Request $request)
	{
		$location_id = $request->location_id;
		
		$data = array();
		$interval = config('custom.LOAD_MORE_INTERVAL');
		$lower = empty($request->moreload) ? 0 : $request->moreload;
		$upper = empty($request->moreload) ? config('custom.LOAD_MORE_LIST_SHOW') : config('custom.LOAD_MORE_INTERVAL');
		//---------------------------------------------------
		/*$taskListIds = Task_lists::where('location_id', $location_id)
			->where('inspector_id', auth()->user()->id)
			->pluck('id');*/
			
		//$taskListIds = Task_lists::where('location_id', $location_id)->pluck('id');
		
		$task_id = $request->task_id;
		$taskListIds = array($task_id);

		$categoryIds = Task_list_subcategories::whereIn('task_list_id', $taskListIds)
			->pluck('task_list_category_id');
		
		$correctivePlanArray = [];
		
		$correctivePlanData = Task_list_corrective_action::whereIn('lo_direct_approve', [0])->whereIn('task_list_id', $taskListIds)->whereIn('category_id', $categoryIds)
			->where(function ($q) {
				$q->where(function ($q) {
					$q->where('inspector_action', 0)->where('los_action', 1);
				})->orWhere(function ($q) {
					$q->where('inspector_action', 1)->where('los_action', 0);
				})->orWhere(function ($q) {
					$q->where('inspector_action', 0)->where('los_action', 0);
				});
			})
			->offset($lower)
			->limit($upper)
			->get();
			
		//echo "<pre>";print_r($correctiveActionData);die;
		foreach($correctivePlanData as $plan)
		{
			//echo $action->checklist_id. "</br>";
			
			$type = '';
			$image = '';
			if($plan->subchecklist_id == null)
			{
				$type = 'checklist';
				
				$checklistFile = Task_list_checklists::with('get_checklist_files')->where('task_list_id', $plan->task_list_id)->where('checklist_id', $plan->checklist_id)->first();
				
				$image = $checklistFile && $checklistFile->get_checklist_files->isNotEmpty() ? $checklistFile->get_checklist_files->first()->file : null;
				
			}
			else
			{
				$type = 'subchecklist';
				
				$subChecklistFile = Task_list_subchecklists::with('get_subchecklist_files')->where('task_list_id', $plan->task_list_id)->where('task_list_checklist_id', $plan->checklist_id)->where('subchecklist_id', $plan->subchecklist_id)->first();
				
				$image = $subChecklistFile && $subChecklistFile->get_subchecklist_files->isNotEmpty() ? $subChecklistFile->get_subchecklist_files->first()->file : null;
			}
			
			$correctivePlanArray[] = [
				'type' => $type ,
				'task_id' => $plan->task_list_id,
				'checklist_id' => $plan->checklist_id,
				'subchecklist_id' => $plan->subchecklist_id,
				'rejected_region' => $plan->lo_corrective_action_plan,
				'inspector_action' => $plan->inspector_action,
				'los_action' => $plan->los_action,
				'second_checked' => $plan->lo_corrective_action_plan_second_check,
				'lo_direct_approve' => $plan->lo_direct_approve,
				'image' => $image,
			];
			
		}
		
		$correctivePlanCount = Task_list_corrective_action::whereIn('lo_direct_approve', [0])->whereIn('task_list_id', $taskListIds)->whereIn('category_id', $categoryIds)
			->where(function ($q) {
				$q->where(function ($q) {
					$q->where('inspector_action', 0)->where('los_action', 1);
				})->orWhere(function ($q) {
					$q->where('inspector_action', 1)->where('los_action', 0);
				})->orWhere(function ($q) {
					$q->where('inspector_action', 0)->where('los_action', 0);
				});
			})->count();
		
		//$totalRecord = $correctiveActionArray;
		//$correctiveActionArray = array_slice($correctiveActionArray, $lower, $upper);
		//echo "<pre>";print_r($correctiveActionArray);
		$data['location_id'] = $location_id;
		$data['mode'] = 'corrective_plan';
		$data['correctivePlanArray'] = $correctivePlanArray;
		$html = view('management.loadmore.mgnt-load-more-data', $data)->render();
		//----------------
		$count  = $request->moreload =='' ? config('custom.LOAD_MORE_LIST_SHOW') : $request->moreload + $interval;
		$remain = $correctivePlanCount - $count;
		
		return response()->json(['location_id'=> $location_id, 'html'=> $html, 'loadmore'=> $lower+$upper, 'remain'=> $remain]);
			
	}
	public function mgnt_load_more_appr_data(Request $request)
	{
		$location_id = $request->location_id;
		$data = array();
		$interval = config('custom.LOAD_MORE_INTERVAL');
		$lower = empty($request->moreload) ? 0 : $request->moreload;
		$upper = empty($request->moreload) ? config('custom.LOAD_MORE_LIST_SHOW') : config('custom.LOAD_MORE_INTERVAL');
		//---------------------------------------------------
		/*$taskListIds = Task_lists::where('location_id', $location_id)
			->where('inspector_id', auth()->user()->id)
			->pluck('id');*/
			
		//$taskListIds = Task_lists::where('location_id', $location_id)->pluck('id');
		
		$task_id = $request->task_id;
		$taskListIds = array($task_id);

		$categoryIds = Task_list_subcategories::whereIn('task_list_id', $taskListIds)
			->pluck('task_list_category_id');
			
		//-----05-08-2025 if submit checklist then submit task data show-----
		$submit_task_id = Task_list_subcategories::whereIn('task_list_category_id', $categoryIds)->pluck('task_list_id')->toArray();
			
		$approvedCompletedArray = [];
		
		$subcategoriesTaskIds = Task_list_subcategories::whereIn('task_list_category_id', $categoryIds)
			->pluck('task_list_id');
			
		$matchingTaskListIds = array_values(array_intersect($taskListIds,  $subcategoriesTaskIds->toArray()));
		
		// Checklist IDs with existing corrective needed
		$correctiveChecklistApprIds = Task_list_corrective_action::whereIn('task_list_id', $matchingTaskListIds)
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
			
			//print_r($correctiveChecklistApprIds);die;
		//------------------------------------
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
			
				
		//print_r($checklistApprIds);die;
		$correctiveApprChecklistIds = array_merge($correctiveChecklistApprIds,$checklistApprIds);
		//print_r($correctiveApprChecklistIds);die;
		//------------------------------------

		$correctiveSubChecklistApprIds = Task_list_corrective_action::whereIn('task_list_id', $matchingTaskListIds)
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
			
			//print_r($correctiveSubChecklistApprIds);die;
			
			$subchecklistApprIds = DB::table('task_list_subchecklists')
			->where('approve', 1)
			->whereIn('task_list_id', $matchingTaskListIds)
			->whereIn('category_id', $categoryIds)
			->get(['task_list_id', 'task_list_checklist_id', 'subchecklist_id'])
			->map(function ($item) {
				return $item->task_list_id . '-' . $item->task_list_checklist_id . '-' . $item->subchecklist_id;
			 })
			->toArray();
			
			//print_r($subchecklistIds);die;
			$correctiveApprSubChecklistIds = array_merge($correctiveSubChecklistApprIds,$subchecklistApprIds);	
			//print_r($correctiveApprSubChecklistIds);die;

		// Raw union query
			$correctiveApproved = DB::table(function ($query) use (
				$taskListIds,
				$categoryIds,
				$submit_task_id,
				$correctiveApprChecklistIds,
				$correctiveApprSubChecklistIds
			) {
				$query->select(
						'id',
						'checklist_id',
						DB::raw("'checklist' as type"),
						'task_list_id',
						'category_id',
						'approve',
						'rejected_region',
						'updated_at',
						DB::raw('NULL as subchecklist_id'),
						DB::raw('NULL as task_list_checklist_id')
					)
					->from('task_list_checklists')
					//->whereIn('task_list_id', $submit_task_id)
					->whereIn('category_id', $categoryIds)
					->whereIn('approve', [0,1])
					->where(function ($query) use ($correctiveApprChecklistIds) {
						foreach ($correctiveApprChecklistIds as $pair) {
							[$taskListId, $checklistId] = explode('-', $pair);
							$query->orWhere(function ($q) use ($taskListId, $checklistId) {
								$q->where('task_list_id', $taskListId)
								  ->where('checklist_id', $checklistId);
							});
						}
					})
				->unionAll(
					DB::table('task_list_subchecklists')
						->select(
							'id',
							'subchecklist_id as item_id',
							DB::raw("'subchecklist' as type"),
							'task_list_id',
							'category_id',
							'approve',
							'rejected_region',
							'updated_at',
							'subchecklist_id',
							'task_list_checklist_id'
						)
						//->whereIn('task_list_id', $taskListIds)
						//->whereIn('task_list_id', $submit_task_id)
						->whereIn('category_id', $categoryIds)
						->whereIn('approve', [0,1])
						->where(function ($query) use ($correctiveApprSubChecklistIds) {
							foreach ($correctiveApprSubChecklistIds as $pair) {
									[$taskListId, $checklistId, $subchecklistId] = explode('-', $pair);
									$query->orWhere(function ($q) use ($taskListId, $checklistId, $subchecklistId) {
										$q->where('task_list_id', $taskListId)
										  ->where('task_list_checklist_id', $checklistId)
										  ->where('subchecklist_id', $subchecklistId);
									});
								}
						})
				);
			}, 'combined')
			//->orderByDesc('updated_at')
			->orderBy('updated_at', 'asc')
			->offset($lower)
			->limit($upper)
			->get();
			//echo "<pre>";print_r($correctiveApproved);die;
			
			$correctiveApprovedCount = DB::table(function ($query) use (
				$taskListIds,
				$categoryIds,
				$submit_task_id,
				$correctiveApprChecklistIds,
				$correctiveApprSubChecklistIds
			) {
				$query->select(
						'id',
						'checklist_id',
						DB::raw("'checklist' as type"),
						'task_list_id',
						'category_id',
						'approve',
						'rejected_region',
						'updated_at',
						DB::raw('NULL as subchecklist_id'),
						DB::raw('NULL as task_list_checklist_id')
					)
					->from('task_list_checklists')
					//->whereIn('task_list_id', $submit_task_id)
					->whereIn('category_id', $categoryIds)
					->whereIn('approve', [0,1])
					->where(function ($query) use ($correctiveApprChecklistIds) {
						foreach ($correctiveApprChecklistIds as $pair) {
							[$taskListId, $checklistId] = explode('-', $pair);
							$query->orWhere(function ($q) use ($taskListId, $checklistId) {
								$q->where('task_list_id', $taskListId)
								  ->where('checklist_id', $checklistId);
							});
						}
					})
				->unionAll(
					DB::table('task_list_subchecklists')
						->select(
							'id',
							'subchecklist_id as item_id',
							DB::raw("'subchecklist' as type"),
							'task_list_id',
							'category_id',
							'approve',
							'rejected_region',
							'updated_at',
							'subchecklist_id',
							'task_list_checklist_id'
						)
						//->whereIn('task_list_id', $taskListIds)
						//->whereIn('task_list_id', $submit_task_id)
						->whereIn('category_id', $categoryIds)
						->whereIn('approve', [0,1])
						->where(function ($query) use ($correctiveApprSubChecklistIds) {
							foreach ($correctiveApprSubChecklistIds as $pair) {
									[$taskListId, $checklistId, $subchecklistId] = explode('-', $pair);
									$query->orWhere(function ($q) use ($taskListId, $checklistId, $subchecklistId) {
										$q->where('task_list_id', $taskListId)
										  ->where('task_list_checklist_id', $checklistId)
										  ->where('subchecklist_id', $subchecklistId);
									});
								}
						})
				);
			}, 'combined')->count();
		
		//echo $correctiveApprovedCount;die;
		
		foreach($correctiveApproved as $appr)
		{
			
			if($appr->approve == 0)
			{
				if($appr->type == 'checklist')
				{
					$task_list_checklist_corrective_needed = Task_list_corrective_action::where('task_list_id', $appr->task_list_id)->where('checklist_id', $appr->checklist_id)->first();
					if($task_list_checklist_corrective_needed)
					{
						$chklstData  = Task_list_checklists::where('checklist_id', $appr->checklist_id)->first();
						$id = $chklstData ? $chklstData->id : '';
						$isfiles = '';
						$images = '';
						$isfiles = Task_list_checklist_rejected_files::where('task_list_checklist_id', $id)->first();
						$images = $isfiles ? $isfiles->file  : '';
						$approvedCompletedArray[] = [
							'type' => 'checklist',
							'task_id' => $appr->task_list_id,
							'checklist_id' => $appr->checklist_id,
							'rejected_region' => $appr->rejected_region,
							'image' => $images,
							'inspector_action'=> $task_list_checklist_corrective_needed->inspector_action,
							'los_action'=> $task_list_checklist_corrective_needed->los_action,
							'updated_at'=> change_date_format($task_list_checklist_corrective_needed->updated_at, 'Y-m-d H:i:s', 'd M Y, h:i A'),
							//'second_checked'=> $task_list_checklist_corrective_action->lo_corrective_action_plan_second_check,
						];
					}
				}
				else if($appr->type == 'subchecklist')
				{
					$task_list_subchecklist_corrective_needed = Task_list_corrective_action::where('task_list_id', $appr->task_list_id)->where('checklist_id', $appr->task_list_checklist_id)->where('subchecklist_id', $appr->subchecklist_id)->first();
					{
						if($task_list_subchecklist_corrective_needed)
						{
							$subchklstData  = task_list_subchecklists::where('task_list_checklist_id', $appr->checklist_id)->where('subchecklist_id',$appr->subchecklist_id)->first();
							$id = $subchklstData ? $subchklstData->id : '';
						
							$isSubChecklistfiles = '';
							$subChecklistimages = '';
							$isSubChecklistfiles = Task_list_subchecklist_rejected_files::where('task_list_subchecklist_id', $id)->first();
									
							$approvedCompletedArray[] = [
								'type' => 'subchecklist',
								'task_id' => $appr->task_list_id,
								'checklist_id' => $appr->task_list_checklist_id,
								'subchecklist_id'=>$appr->subchecklist_id,
								'rejected_region' => $appr->rejected_region,
								'image' => $subChecklistimages,
								'inspector_action'=> $task_list_subchecklist_corrective_needed->inspector_action,
								'los_action'=> $task_list_subchecklist_corrective_needed->los_action,
								'updated_at'=>change_date_format($task_list_subchecklist_corrective_needed->updated_at, 'Y-m-d H:i:s', 'd M Y, h:i A'),
								//'second_checked'=> $task_list_subchecklist_corrective_action->lo_corrective_action_plan_second_check,
							];
						}
					}
				}
			}
			elseif($appr->approve == 1)
			{
				if($appr->type == 'checklist')
				{
					$approvedCompletedArray[] = [
						'type' => 'checklist',
						'task_id' => $appr->task_list_id,
						'checklist_id' => $appr->checklist_id,
						'rejected_region' => $appr->rejected_region,
						'inspector_action' => 1,
						'los_action' => 1,
						'updated_at'=> change_date_format($appr->updated_at, 'Y-m-d H:i:s', 'd M Y, h:i A'),
					];
				}
				else
				{
					$approvedCompletedArray[] = [
						'type' => 'subchecklist',
						'task_id' => $appr->task_list_id,
						'checklist_id' => $appr->task_list_checklist_id,
						'subchecklist_id'=>$appr->subchecklist_id,
						'rejected_region' => $appr->rejected_region,
						'inspector_action' => 1,
						'los_action' => 1,
						'updated_at'=>change_date_format($appr->updated_at, 'Y-m-d H:i:s', 'd M Y, h:i A'),
					];
				}
			}
			
		}
		
		//--------------------------------------------------
		usort($approvedCompletedArray, function ($a, $b) {
			return strtotime($b['updated_at']) <=> strtotime($a['updated_at']);
		});
		
		//$totalRecord = $approvedCompletedArray;
		//$approvedCompletedArray = array_slice($approvedCompletedArray, $lower, $upper);
		$data['approvedCompletedArray'] = $approvedCompletedArray;
		
		$data['location_id'] = $location_id;
		$data['mode'] = 'corrective_appr';
		
		$html = view('management.loadmore.mgnt-load-more-data', $data)->render();
		
		//--------------------------------------
		$count  = $request->moreload =='' ? config('custom.LOAD_MORE_LIST_SHOW') : $request->moreload + $interval;
		$remain = $correctiveApprovedCount - $count;
		
		return response()->json(['location_id'=> $location_id, 'html'=> $html, 'loadmore'=> $lower+$upper, 'remain'=> $remain]);
	}
}
