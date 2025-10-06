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

//---- my dashboard ----
$locations = App\Models\Users_location::where('user_id', auth()->user()->id)->pluck('location_id')->toArray();
//echo "<pre>";print_r($locations);die;
if(auth()->user()->user_type == 1)
{
	$taskListIds = App\Models\Task_lists::whereIn('location_id', $locations)
			->where('inspector_id', auth()->user()->id)
			->pluck('id');
}
else{
	$taskListIds = App\Models\Task_lists::whereIn('location_id', $locations)
			->pluck('id');
}


//echo "<pre>";print_r($taskListIds);die;
$categoryIds = App\Models\Task_list_subcategories::whereIn('task_list_id', $taskListIds)
			->pluck('task_list_category_id');

$submit_task_id = App\Models\Task_list_subcategories::whereIn('task_list_category_id', $categoryIds)->pluck('task_list_id')->toArray();

//----------------corrective needed ------------
if(auth()->user()->user_type == 1 || auth()->user()->user_type == 3)
{
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
}

if(auth()->user()->user_type == 2)
{
	$company_id = App\Models\User::where('user_type', 2)->where('id', auth()->user()->id)->first()->company_name;
		
	$users_location = App\Models\Users_location::where('company_id', $company_id)->where('user_type', 2)->where('user_id', auth()->user()->id)->whereIn('location_id', $locations)->first();
	$primary_owner = $users_location ? $users_location->primary_owner : '';
	//echo "<pre>";print_r($primary_owner);die;
	
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
		
		
		
		if($primary_owner == 1)
		{
			$excludedSubChecklistPairs = App\Models\Task_list_corrective_action::whereNotIn('rejected_repeated', [0])
				->whereIn('task_list_id', $taskListIds)
				->where('lo_id', '!=' ,auth()->user()->id) //new 24-09-2025
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
				//->orWhereIn('lo_direct_approve', [0, 1]) // new 25-09-2025
				->where(function ($q) {
					$q->where(function ($q) {
						$q->where('inspector_action', 0)->where('los_action', 0);
					})->orWhere(function ($q) {
						$q->where('inspector_action', 2)->where('los_action', 2);
					});
					/*->orWhere(function ($q) {
						$q->where('inspector_action', 1)->where('los_action', 0);
					})->orWhere(function ($q) {
						$q->where('inspector_action', 0)->where('los_action', 1);
					})->orWhere(function ($q) {
						$q->where('inspector_action', 2)->where('los_action', 2);
					});*/
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
}

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
					<div class="heading-panel">
					   <div class="col-xs-12 col-md-7 col-sm-6 left-side">
						  <!-- Main Title -->
						  <h1>My dashboard</h1>
					   </div>
					</div>
					<div class="pt-0 pb-0">
						<div class="row flex-wrap d-flex">
							<div class="col-md-6 col-sm-6 col-xs-6 small-card-first d-flex">
								<div class="bg small-card my-dashboard-upper position-relative">
								<span class="notification-badge"></span>
									<div class="small-card-title">Open corrective action/plan</div>
									<div class="d-flex align-items-center small-card-upper-counter-wrapper">
										<div class="small-card-upper-counter me-2">{{ $correctiveActionCount + $correctivePlanCount}}</div>
										<div class="small-card-upper-counter-title">Pending task</div>
									</div>
								</div>
							</div>
							<div class="col-md-6 col-sm-6 col-xs-6 small-card-second d-flex">
								<div class="bg small-card my-dashboard-upper position-relative">
									<span class="notification-badge"></span>
									<div class="small-card-title">Inspection closure</div>
									<div class="d-flex align-items-center small-card-upper-counter-wrapper">
										<div class="small-card-upper-counter me-2"><span id="tot_no_of_obs">{{ $correctiveApprovedCount }}</span></div>
										<div class="small-card-upper-counter-title">Pending task</div>
									</div>
								</div>
							</div>
						</div>
						<div class="row flex-wrap d-flex">
							<div class="col-md-4 col-sm-4 col-xs-4 small-card-first d-flex">
								<div class="bg small-card my-dashboard-lower">
									<div class="small-card-title">Inspection completed</div>
									<div class="small-card-counter">{{ $correctiveApprovedCount + $correctiveActionCount + $correctivePlanCount + $correctiveNeededCount}}</div>
									{{--<div class="small-card-counter-title">WEEKLY</div>--}}
								</div>
							</div>
							<div class="col-md-4 col-sm-4 col-xs-4 small-card-second d-flex">
								<div class="bg small-card my-dashboard-lower">
									<div class="small-card-title">Observations</div>
									<div class="small-card-counter"><span id="tot_no_of_obs">{{ $correctiveNeededCount }}</span></div>
									{{--<div class="small-card-counter-title">WEEKLY</div>--}}
								</div>
							</div>
							<div class="col-md-4 col-sm-4 col-xs-4 small-card-second d-flex">
								<div class="bg small-card my-dashboard-lower">
									<div class="small-card-title">Pending closure</div>
									<div class="small-card-counter"><span id="tot_no_of_obs">{{ $correctiveActionCount + $correctivePlanCount + $correctiveNeededCount}}</span></div>
									{{--<div class="small-card-counter-title">WEEKLY</div>--}}
								</div>
							</div>
						</div>
					</div>
				</div>
               <div class="row my-location">
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

