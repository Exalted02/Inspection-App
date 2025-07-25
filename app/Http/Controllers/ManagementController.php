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

class ManagementController extends Controller
{
    public function index()
    {
		$data = [];
		
		//$locations = Manage_location::where('company_id', auth()->user()->company_name)->get();
		//----------------------
		$userLocationArr = [];
		$userLocationArr = Users_location::where('user_id', auth()->user()->id)->pluck('location_id')->toArray();
		
		$locations = Manage_location::whereIn('id', $userLocationArr)->get();
		//----------------------
		$data['userdata'] = User::with('get_user_location')->where('id', auth()->user()->id)->first();
		$data['locations'] = $locations;
		$data['userLocationArr'] =$userLocationArr;
        return view('management.management-dashboard', $data);
    }
    public function management_location($id='')
    {
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
	public function management_location_task_details($id='', $active='')
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
}
