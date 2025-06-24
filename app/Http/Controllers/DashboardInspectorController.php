<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Manage_location;
use App\Models\Manage_location_category;
use App\Models\Category;
use App\Models\Task_lists;
use App\Models\Checklist;
use App\Models\Subchecklist;
use App\Models\Task_list_checklist_temp_rejected_files;
use App\Models\Task_list_checklists;
use App\Models\Task_list_checklist_rejected_files;
use App\Models\Task_list_subchecklists;
use App\Models\Task_list_subchecklist_rejected_files;
use App\Models\Task_list_subchecklist_temp_rejected_files;
use App\Models\Task_list_subcategories;
use App\Models\Subcategory;
use App\Models\Task_list_corrective_action;
use App\Models\Task_list_corrective_action_file;
use App\Models\Task_location_categories;

class DashboardInspectorController extends Controller
{
    public function inspector_dashboard()
    {
		$data = [];

		$id = auth()->user()->id;
		$data['userdata'] = User::with('get_user_location')->where('id', $id)->first();
        return view('inspector.inspector-dashboard', $data);
	}
	public function location_details($id='')
    {
		$data = [];
		$data['location_categories'] = Manage_location::with('category_by_location')->where('id', $id)->get();
		
		//$data['task_list'] = Task_lists::where('location_id', $id)->where('inspector_id', auth()->user()->id)->get();
		
		$data['task_list_data'] = collect(); // default empty collection

		if (auth()->user()->user_type == 1) {
			// Only return data if a match is found
			$hasData = Task_lists::where('location_id', $id)
						->where('inspector_id', auth()->user()->id)
						->exists();

			if ($hasData) {
				$data['task_list_data'] = Task_lists::where('location_id', $id)
											->where('inspector_id', auth()->user()->id)->orderBy('id', 'DESC')
											->get();
			}

		} elseif (auth()->user()->user_type == 2 || auth()->user()->user_type == 3) {
			$hasData = Task_lists::where('location_id', $id)->exists();

			if ($hasData) {
				$data['task_list_data'] = Task_lists::where('location_id', $id)->orderBy('id', 'DESC')->get();
			}
		}

		
		//$data['task_list_data'] = $dataArr->get();
		
		$data['locationcategory'] = Category::where('location_id', $id)->where('status' ,'!=', 2)->get(); // use in dropdown for add task
		$data['location_id'] = $id;
		
		// fetch subcategory by location
		$data['locationWisecategory'] = Category::where('location_id', $id)->get();
											
		//echo "<pre>";print_r($locationWisesubcategory);die;
		
        return view('inspector.location-details', $data);
    }
	public function category($lid='',$task_id='', $active='')
    {
		if (auth()->user()->user_type == 2) {
			return redirect('inspector-dashboard');
		}
		
		$data = [];
		
		$correctiveNeddedChecklistArray = [];
		$completedApprChecklistArray = [];
		
		$correctiveNeddedSubchecklistArray = [];
		$completedApprSubcheckListArray = [];
		// -- if inspector login 
		//$data['categoryData'] = Category::with('get_subcategory')->where('id', $catid)->where('location_id', $lid)->get();

		$data['categoryData'] = Category::whereIn('id', function($query) use ($task_id) {
				$query->select('category_id')
				  ->from('task_location_categories')
				  ->where('task_list_id', $task_id);
		})->orderBy('order_no')->get();
        //echo "<pre>";print_r($categoryData);die;
		
		$data['location_id'] = $lid;
		//$details = Task_lists::where('id',$task_id)->where('inspector_id', auth()->user()->id)->where('location_id', $lid)->first();
		$details = Task_lists::where('id',$task_id)->where('location_id', $lid)->first();
		$data['location_details'] = $details ? $details->location_details : null;
		$data['task_id'] = $details ? $details->id : null;
		$data['task_name'] = $details ? $details->task_title : null;
		$data['isactive'] = $active;
		
		// for corrective checked work 
		$correctiveActionChecklistArray = [];
		$taskData = Task_lists::where('location_id', $lid)->where('id', $task_id)->get();
		if($taskData->isNotEmpty())
		{
			foreach($taskData as $val)
			{
				//$correctiveActions = Task_list_corrective_action::where('task_list_id', $val->id)->where('inspector_id', auth()->user()->id)->get();
				
				$correctiveActions = Task_list_corrective_action::where('task_list_id', $val->id)->get();
				
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
				
				//----------------------12-05-2025----------------------------
				// checklist and  respective files approve=1 
				$taskChklist = Task_list_checklists::where('task_list_id', $val->id)->get();
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
											//'second_checked'=> $task_list_checklist_corrective_action->lo_corrective_action_plan_second_check,
										];
							}
							
						}
						else{
							$completedApprChecklistArray[] = [
											'type' => 'checklist',
											'task_id' => $val->id,
											'checklist_id' => $task->checklist_id,
											'rejected_region' => $task->rejected_region,
											'inspector_action' => 1,
											'los_action' => 1,
										];
						}
					}
				}
				
				// subchecklist and respective files
				$taskSubChklist = Task_list_subchecklists::where('task_list_id', $val->id)->get();
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
											//'second_checked'=> $task_list_subchecklist_corrective_action->lo_corrective_action_plan_second_check,
										];
								
							}
						}
						else
						{
							$completedApprSubcheckListArray[] = [
									'type' => 'subchecklist',
									'task_id' => $val->id,
									'checklist_id' => $subtask->task_list_checklist_id,
									'subchecklist_id'=>$subtask->subchecklist_id,
									'rejected_region' => $subtask->rejected_region,
									'inspector_action' => 1,
									'los_action' => 1,
									
								];
							
						}
					}
					
				}
					
			} // task array end 
		}
		
		//-----------12-06-2025--------------------
		$data['correctiveNeeded'] = array_merge($correctiveNeddedChecklistArray,$correctiveNeddedSubchecklistArray);
		$data['approvedCompleted'] = array_merge($completedApprChecklistArray,$completedApprSubcheckListArray);
		//------------------------------------------
		//echo "<pre>";print_r($correctiveActionChecklistArray);die;
		$data['correctiveAction'] = $correctiveActionChecklistArray;
		//-----
		return view('inspector.category-subcategory', $data);
    }
	public function send_location_details(Request $request)
	{
		$location_id = $request->post('location_id');
		//$category_id = $request->post('category_id');
		$task_id = $request->post('task_id');
		$details = $request->post('details');
		$inspectorId = auth()->user()->id;
		
		$taskList = Task_lists::where('inspector_id', $inspectorId)
                      ->where('location_id', $location_id)
					  ->where('id', $task_id)
                      ->first();

		$existingCategory = $taskList 
		? Task_lists::where('id', $taskList->id)->first() : null;
							  
		if ($existingCategory) {
			$existingCategory->location_details = $details;
			$existingCategory->save();
		} else {
				$taskList = new Task_lists();
				$taskList->inspector_id = $inspectorId;
				$taskList->location_id = $location_id;
				//$taskList->category_id = $category_id;
				$taskList->location_details = $details;
				$taskList->save();
		}
		
		return response()->json(['status' => 'success', 'message' => 'Data saved successfully.']);

	}
	
	public function check_task_id(Request $request)
	{
		$category_id = $request->post('cat_id');
		//$subcategory_id = $request->post('subcat_id');
		$location_id = $request->post('location_id');
		$inspector_id = auth()->user()->id;
		$task_id = $request->post('task_id');
		$exists = Task_lists::where('inspector_id', $inspector_id)->where('location_id', $location_id)->where('id', $task_id)->exists();
		if($exists)
		{
			//$taskid = Task_lists::where('inspector_id', $inspector_id)->where('location_id', $location_id)->where('category_id', $category_id)->first()->id;
			
			$taskLocationDtls = Task_lists::where('inspector_id', $inspector_id)->where('location_id', $location_id)->where('id', $task_id)->first()->location_details ;
			
			//$hasChecklists = Checklist::where('category_id', $category_id)->where('subcategory_id', $subcategory_id)->exists();
			
			$hasChecklists = Checklist::where('category_id', $category_id)->first();
			
			//---21-06-2025 direct get the final edit page----
			$totalChecklist = Checklist::where('category_id', $category_id)->count();
			$countTaskChecklist = Task_list_checklists::where('task_list_id', $task_id)->where('category_id', $category_id)->count();
			$countTaskSubChecklist = Task_list_subchecklists::distinct('task_list_checklist_id')->where('task_list_id', $task_id)->where('category_id', $category_id)->count();
			$allChecklistDone = $countTaskChecklist + $countTaskSubChecklist;
			
			$finalEditPage = 0;
			if($totalChecklist == $allChecklistDone)
			{
				$finalEditPage = 1;
			}
			
			//----- first time when no checklist or subchecklist added --
			$ifExistsChecklist  = Task_list_checklists::where('task_list_id', $task_id)->where('category_id', $category_id)->exists();
			
			$ifExistsSubChecklist  = Task_list_subchecklists::where('task_list_id', $task_id)->where('category_id', $category_id)->exists();
			if(!$ifExistsChecklist && !$ifExistsSubChecklist)
			{
				return response()->json(['hasData'=> true, 'taskid'=>$task_id, 'finalEditPage'=> 2, 'order_no'=>0]);
			}
			//------------reaching exists  checklist id-----------------
			$order_no = 0;
			$checklistQuestion = Checklist::where('category_id', $category_id)->where('status', '!=', 2)->orderBy('order_no')->get();
			foreach ($checklistQuestion as $list) {
				$hasChecklists = Task_list_checklists::where('category_id', $category_id)
					->where('checklist_id', $list->id)
					->exists();

				$hasSubChecklists = Task_list_subchecklists::where('category_id', $category_id)
					->where('task_list_checklist_id', $list->id)
					->exists();

				if (!$hasChecklists && !$hasSubChecklists) {
					$order_no = $list->order_no;
					break;
				}
			}
			if(!empty($order_no))
			{
				return response()->json(['hasData'=> true, 'taskid'=>$task_id, 'finalEditPage'=> 3, 'order_no'=>$order_no]);
			}
			//-------------------------------------------
			/*if(!$taskLocationDtls)
			{
				return response()->json(['hasData'=> false,'message'=>'Please enter location details.', 'id'=>NULL]);
			}
			
			if(!$hasChecklists)
			{
				return response()->json(['hasData'=> false,'message'=>'This category has no checklist. Create checklist and subchecklist', 'id'=>NULL]);
			}*/
			
			return response()->json(['hasData'=> true, 'taskid'=>$task_id, 'finalEditPage'=>$finalEditPage, 'order_no'=>$order_no]);
		}
		else{
			return response()->json(['hasData'=> false, 'id'=>NULL]);
		}
	}
	
	public function checklist_question($taskid='', $cat_id='', $mode='' ,$order_no='')
    {
		if (auth()->user()->user_type == 2 || auth()->user()->user_type == 3) {
			return redirect('inspector-dashboard');
		} 
		
		$data = [];
		//echo $cat_id.' '.$subcat_id; die;
		/*$data['checklistdata'] = Checklist::with('get_subchecklist','get_category','get_subcategory')->where('category_id',$cat_id)->where('status','!=', 2)->first();*/
		
		$data['checklistdata'] = Checklist::with('get_subchecklist','get_category')->where('category_id',$cat_id)->where('status','!=', 2)->orderBy('order_no')->first();
		
		//echo "<pre>";print_r($checklistdata);die;
		/*$nextQuestion = Checklist::where('category_id', $cat_id)
		->where('subcategory_id', $subcat_id)
		->where('status', '!=', 2)
		->where('id', '>', $current_question_id)
		->orderBy('id', 'asc')
		->first();*/
		$data['previous_checklist_id'] = '';
		$data['task_id'] = $taskid;
		$task_data = Task_lists::where('id', $taskid)->first();
		$data['location_id'] = $task_data ? $task_data->location_id : '';
		$data['isFinalEdit'] = $mode;
		$data['skip_order_no'] = $order_no;
        return view('inspector.checklist-question', $data);
    }
	public function checklist_next_question(Request $request)
	{
		$approveStatus = $request->post('approveStatus');
		$mode = $request->post('mode');
		$order_no = $request->post('order_no');
		$rejectTextsSingle = $request->post('rejectTextsSingle');
		$rejectTextsMultiple = json_decode($request->input('rejectTextsMultiple'), true);
		//echo "<pre>";print_r($rejectTextsMultiple);die;
		/*if(!empty($rejectTextsMultiple) && is_array($rejectTextsMultiple)) {
				foreach ($rejectTextsMultiple as $subChecklistId => $text) {
					echo "SubChecklist ID: " . $subChecklistId . " - Reason: " . $text['text'] ." status- ".$text['approve_status'] . "<br>";
				}
			}
		*/
		//-------------------------------------
		$task_id = $request->post('task_id');
		$current_question_id = $request->post('current_question_id');
		$category_id = $request->post('category_id');
		//$subcategory_id = $request->post('subcategory_id');
		$nextQuestionExists = Checklist::where('category_id', $category_id)
		//->where('subcategory_id', $subcategory_id)
		->where('status', '!=', 2)
		->where('order_no', '>', $order_no)
		//->where('id', '>', $current_question_id)
		->orderByRaw('CAST(order_no as UNSIGNED) ASC')
		//->orderBy('id', 'asc')
		->exists();
		
		$nextId = '';
		$name  = '';
		$subchecklist = '';
		$subcategoryname = '';
		$categoryDtls = Category::where('id',$category_id)->first();
		$categoryName = $categoryDtls ? $categoryDtls->name : '';
		//$subChklistArr = [];
		
		//---add record to table
		if($mode == 'single')
		{
			if($approveStatus !='')
			{
				// 21-05-2025--
				/*$checkTastChecklistExists  = Task_list_checklists::where('task_list_id', $task_id)->where('task_list_subcategory_id', $subcategory_id)->where('checklist_id', $current_question_id)->first();
				$hasid = $checkTastChecklistExists ? $checkTastChecklistExists->id : null;*/
				
				$checkTastChecklistExists  = Task_list_checklists::where('task_list_id', $task_id)->where('checklist_id', $current_question_id)->first();
				$hasid = $checkTastChecklistExists ? $checkTastChecklistExists->id : null;
				
				if($hasid)
				{
					$model = Task_list_checklists::find($hasid);
					$model->rejected_region = $approveStatus == 0 ? $rejectTextsSingle : null;
					$model->approve 	= $approveStatus;
					$model->save();
					$task_list_checklist_id = $hasid;
					
					// if file uploaded and get next or back if i choose tick sign then delete the files
					if($approveStatus=='1')
					{
						$chklistFiles = Task_list_checklist_rejected_files::where('task_list_checklist_id', $hasid)->get();
						if($chklistFiles->isNotEmpty()){
							
							foreach($chklistFiles as $filemn)
							{
								$f_name = $filemn->file;
								$filePath = public_path('uploads/reject-files/' . $f_name);
								if (file_exists($filePath)) {
									unlink($filePath);
								}
							}
							
							Task_list_checklist_rejected_files::where('task_list_checklist_id', $hasid)->delete();
						}
					}
					
				}
				else
				{
					
					$model = new Task_list_checklists();	
					$model->task_list_id = $task_id ?? null;
					$model->category_id = $category_id ?? null; //- 21-06-2025
					//$model->task_list_subcategory_id = $subcategory_id ?? null; // 21-05-2025
					$model->checklist_id = $current_question_id ?? null;
					$model->rejected_region = $approveStatus == 0 ? $rejectTextsSingle :'';
					$model->approve 	= $approveStatus;
					$model->save();
					$task_list_checklist_id = $model->id;
				}
				
				$checkTemps = Task_list_checklist_temp_rejected_files::where(
				[
					'inspector_id'=> auth()->user()->id,
					'task_id'=> $task_id,
					'task_list_checklist_id'=>$current_question_id,
					//'subcategory_id'=>$subcategory_id // 21-05-2025
				])->get();
				
				if ($checkTemps->isNotEmpty()) {
					foreach ($checkTemps as $tempFile) {
						$filename = $tempFile->file;

						$sourcePath = public_path('uploads/temp-reject-files/' . $filename);
						$destinationPath = public_path('uploads/reject-files/' . $filename);
						
						if($approveStatus=='0')
						{
							if (!file_exists(dirname($destinationPath))) {
								mkdir(dirname($destinationPath), 0777, true);
							}

							if (file_exists($sourcePath)) {
								rename($sourcePath, $destinationPath);
							}

							$fileModel = new Task_list_checklist_rejected_files();
							$fileModel->task_list_checklist_id = $task_list_checklist_id;
							$fileModel->file = $filename;
							$fileModel->save();
						}
						else{
							$filePath = public_path('uploads/temp-reject-files/' . $filename);
							if(file_exists($filePath)) {
								unlink($filePath);
							}
						}
						//$tempFile->delete();
						Task_list_checklist_temp_rejected_files::where('file', $filename)->delete();
					}
				}
			}

		}
		else
		{
			if (!empty($rejectTextsMultiple) && is_array($rejectTextsMultiple)) {
				foreach ($rejectTextsMultiple as $subChecklistId => $text) {
					//echo "SubChecklist ID: " . $subChecklistId . " - Reason: " . $text['text'] ." status- ".$text['approve_status'] . "<br>";
					if($text['approve_status'] !='')
					{
						$checkTastSubChecklistExists  = Task_list_subchecklists::where('task_list_id', $task_id)
						//->where('task_list_subcategory_id', $subcategory_id) // 21-05-2025
						->where('task_list_checklist_id', $current_question_id)
						->where('subchecklist_id', $subChecklistId)
						->first();
						$hasid = $checkTastSubChecklistExists ? $checkTastSubChecklistExists->id : null;
						if($hasid)
						{
							$model = Task_list_subchecklists::find($hasid);
							
							$model->rejected_region = $text['approve_status'] == 0 ? $text['text'] : '';
							$model->approve = $text['approve_status'];
							$model->save();
							$task_list_subchecklist_id = $hasid;
						}
						else
						{
							$model = new Task_list_subchecklists();
							$model->task_list_id = $task_id ?? null;
							$model->category_id = $category_id ?? null; // 21-06-2025
							//$model->task_list_subcategory_id = $subcategory_id ?? null; //21-05-2025
							$model->task_list_checklist_id = $current_question_id ?? null;
							$model->subchecklist_id = $subChecklistId ?? null;
							$model->rejected_region = $text['text'] ?? '';
							$model->approve = $text['approve_status'];
							$model->save();
							$task_list_subchecklist_id = $model->id;
						}
						
						// file transffer from temp folder to main folder
						$checkSubChecklistTemps = Task_list_subchecklist_temp_rejected_files::where(
						[
							'inspector_id'=> auth()->user()->id,
							'task_list_id'=> $task_id,
							'task_list_checklist_id'=>$current_question_id,
							'subchecklist_id'=>$subChecklistId
						])->get();
						
						if ($checkSubChecklistTemps->isNotEmpty()) {
								foreach ($checkSubChecklistTemps as $tempFile) {
									$filename = $tempFile->file;

									$sourcePath = public_path('uploads/temp-reject-files/' . $filename);
									$destinationPath = public_path('uploads/reject-files/subchecklist/' . $filename);

									if (!file_exists(dirname($destinationPath))) {
										mkdir(dirname($destinationPath), 0777, true);
									}

									if (file_exists($sourcePath)) {
										rename($sourcePath, $destinationPath);
									}

									$fileModel = new Task_list_subchecklist_rejected_files();
									$fileModel->task_list_checklist_id = $current_question_id;
									$fileModel->task_list_subchecklist_id = $task_list_subchecklist_id;
									$fileModel->file = $filename;
									$fileModel->save();

									//$tempFile->delete();
									Task_list_subchecklist_temp_rejected_files::where('file', $filename)->delete();
								}
							}
					}
				}
			}
		}
		//-------
		$subChklistArr = [];
		$existingFiles = [];
		$existingSubChecklistFiles = [];
		$fetchsubChklistArr = '';
		$next_approve = '';
		$previous_checklist_id = '';
		
		if($nextQuestionExists)
		{
			/*$nextQuestion = Checklist::with('get_subchecklist','get_category','get_subcategory')->where('category_id', $category_id)
			//->where('subcategory_id', $subcategory_id) // 21-05-2025
			->where('status', '!=', 2)
			->where('id', '>', $current_question_id)
			->orderBy('id', 'asc')
			->first();*/
			
			$nextQuestion = Checklist::with('get_subchecklist','get_category')->where('category_id', $category_id)
			->where('status', '!=', 2)
			->where('order_no', '>', $order_no)
			->orderByRaw('CAST(order_no as UNSIGNED) ASC')
			->first();
			
			//echo "<pre>";print_r($nextQuestion);die;
			$nextId = $nextQuestion->id;
			$name = $nextQuestion->name;
			$next_order_no = $nextQuestion->order_no;
			//echo $next_order_no; die;
			//$subChklistArr = [];
			if(!empty($nextQuestion->get_subchecklist))
			{
				//$subchecklist = $nextQuestion->get_subchecklist;
				foreach($nextQuestion->get_subchecklist as $subchecklists)
				{
					$subChklistArr[] = [
						'id' => $subchecklists->id,
						'name' => $subchecklists->name
					];
				}
				
				$subcategoryname = '';
				//$subcategoryname = $nextQuestion->get_subcategory->name;
			}
			
			// fetch data from task_list_checklist
			//-- 21-05-2025-----
			/*$iffetch  = Task_list_checklists::where('task_list_id', $task_id)->where('task_list_subcategory_id', $subcategory_id)->where('checklist_id', $nextId)->first();*/
			//------
			
			$iffetch  = Task_list_checklists::where('task_list_id', $task_id)->where('checklist_id', $nextId)->first();
			
			$next_rejected_region = $iffetch ? $iffetch->rejected_region : null;
			$next_approve = $iffetch ? $iffetch->approve : '';
			
			// fetch files 
			$task_list_checklist_id = $iffetch ? $iffetch->id : null;
			//$existingFiles = [];
			if (isset($task_list_checklist_id)) {
				$imageData = Task_list_checklist_rejected_files::where('task_list_checklist_id', $task_list_checklist_id)->get();
				foreach ($imageData as $file) {
					$filename = $file->file;
					$existingFiles[] = [
						'name' => $filename,
						'size' => file_exists(public_path('uploads/reject-files/' . $filename)) ? filesize(public_path('uploads/reject-files/' . $filename)) : 123456, // default if unknown
						'url' => asset('uploads/reject-files/' . $filename),
					];
				}
			}
			
			// fetch data from task_list_subchecklist
			$fetchsubChklistArr = [];
			$ifsubfetch  = Task_list_subchecklists::where('task_list_id', $task_id)
							//->where('task_list_subcategory_id', $subcategory_id) // 21-05-2025
							->where('task_list_checklist_id', $nextId)
							->get();
			if($ifsubfetch->isNotEmpty())
			{
				foreach($ifsubfetch as $subchecklistval)
				{
					$fetchsubChklistArr[] = [
						'subchecklist_id' => $subchecklistval->subchecklist_id,
						'rejected_region' => $subchecklistval->rejected_region ?? '',
						'approve' => $subchecklistval->approve
					];
					
					// fetch files for subchecklist
					if(isset($subchecklistval->id))
					{
						$imageSubChecklistData = Task_list_subchecklist_rejected_files::where('task_list_subchecklist_id', $subchecklistval->id)->get();
						foreach ($imageSubChecklistData as $file) {
							$filename = $file->file;
							$existingSubChecklistFiles[] = [
								'name' => $filename,
								//'subchecklist_id' => $file->task_list_subchecklist_id,
								'subchecklist_id' => $subchecklistval->subchecklist_id,
								'size' => file_exists(public_path('uploads/reject-files/subchecklist/' . $filename)) ? filesize(public_path('uploads/reject-files/subchecklist/' . $filename)) : 123456, // default if unknown
								'url' => asset('uploads/reject-files/subchecklist/' . $filename),
							];
						}
					}
				}
			}
			
		}
		
		$checklistdata = [];
		
		//-------- if no next checklist ----
		
		//$chklistdata = '';
		//$checklistdata = [];
		if((empty($nextId) && $nextId==''))
		{
			$checklists = Checklist::where('category_id',$category_id)
									//->where('subcategory_id', $subcategory_id) // 21-05-2025
									->where('status','!=', 2)->orderBy('order_no')->get();
									
			foreach ($checklists as $chklist) {
				$status = '';
				$hasTaskChecklist = Task_list_checklists::where('task_list_id', $task_id)
							//->where('task_list_subcategory_id', $subcategory_id)//21-05-2025
							->where('checklist_id', $chklist->id)->exists();
				if($hasTaskChecklist)
				{
					$status = Task_list_checklists::where('task_list_id', $task_id)
							//->where('task_list_subcategory_id', $subcategory_id) // 21-05-2025
							->where('checklist_id', $chklist->id)->first()->approve;
					
				}
				else
				{
					$hasTaskSubChecklist = Task_list_subchecklists::where('task_list_id', $task_id)
							//->where('task_list_subcategory_id', $subcategory_id) // 21-05-2025
							->where('task_list_checklist_id', $chklist->id)->exists();
					if($hasTaskSubChecklist)
					{
						/*$status = Task_list_subchecklists::where('task_list_id', $task_id)
							//->where('task_list_subcategory_id', $subcategory_id) // 21-05-2025
							->where('task_list_checklist_id', $chklist->id)->first()->approve;*/
							
						$getstatus = Task_list_subchecklists::where('task_list_id', $task_id)
									->where('task_list_checklist_id', $chklist->id)->get();
						if($getstatus->isNotEmpty())
						{
							$status = 1;
							foreach($getstatus as $val)
							{
								if($val->approve == 0)
								{
									$status = 0;
								}
							}
						}
					}
				}

				$checklistdata[] = [
					'id' => $chklist->id,
					'name' => $chklist->name,
					'approve' => $status,
				];
			}						
			
				//echo "<pre>"; print_r($checklistdata);die;
									
			//$subcategoryname = Subcategory::where('id', $subcategory_id)->first()->name;
			
			// update the status of Task_list table
			Task_lists::where('id', $task_id)->update(['status'=> 1]);
		}
		else if(isset($request->directEdit) && $request->directEdit == 'directEdit')
		{
			// if click the edit button after save get the final edit page no next checklist
			
			$nextId = '';
			$next_order_no = '';
			$name = '';
			$subChklistArr = [];
			$subcategoryname = '';
			$next_rejected_region = '';
			$next_approve = '';
			$existingFiles = [];
			$fetchsubChklistArr = '';
			$existingSubChecklistFiles = [];
			
			$checklists = Checklist::where('category_id',$category_id)
									//->where('subcategory_id', $subcategory_id) // 21-05-2025
									->where('status','!=', 2)->orderBy('order_no')->get();
									
			foreach ($checklists as $chklist) {
				$status = '';
				$hasTaskChecklist = Task_list_checklists::where('task_list_id', $task_id)
							//->where('task_list_subcategory_id', $subcategory_id)//21-05-2025
							->where('checklist_id', $chklist->id)->exists();
				if($hasTaskChecklist)
				{
					$status = Task_list_checklists::where('task_list_id', $task_id)
							//->where('task_list_subcategory_id', $subcategory_id) // 21-05-2025
							->where('checklist_id', $chklist->id)->first()->approve;
					
				}
				else
				{
					$hasTaskSubChecklist = Task_list_subchecklists::where('task_list_id', $task_id)
							//->where('task_list_subcategory_id', $subcategory_id) // 21-05-2025
							->where('task_list_checklist_id', $chklist->id)->exists();
					if($hasTaskSubChecklist)
					{
						/*$status = Task_list_subchecklists::where('task_list_id', $task_id)
							//->where('task_list_subcategory_id', $subcategory_id) // 21-05-2025
							->where('task_list_checklist_id', $chklist->id)->first()->approve;*/
							
						$getstatus = Task_list_subchecklists::where('task_list_id', $task_id)
									->where('task_list_checklist_id', $chklist->id)->get();
						if($getstatus->isNotEmpty())
						{
							$status = 1;
							foreach($getstatus as $val)
							{
								if($val->approve == 0)
								{
									$status = 0;
								}
							}
						}
					}
				}

				$checklistdata[] = [
					'id' => $chklist->id,
					'name' => $chklist->name,
					'approve' => $status,
				];
			}
		}
		//--------------------------------------------
		
			// for progress bar 
			//$previous_checklist_id = $current_question_id;
			$progressStatus = '';
			$hasTaskChecklist = Task_list_checklists::where('task_list_id', $task_id)
							//->where('task_list_subcategory_id', $subcategory_id) // 21-05-2025
							->where('checklist_id', $current_question_id)->exists();
			if($hasTaskChecklist)
			{
				$progressStatus = 1;
			}
			else 
			{
				$hasTaskSubChecklist = Task_list_subchecklists::where('task_list_id', $task_id)
							//->where('task_list_subcategory_id', $subcategory_id) // 21-05-2025
							->where('task_list_checklist_id', $current_question_id)->exists();
				if($hasTaskSubChecklist)
				{
					$progressStatus = 1;
				}
			}
		//---------------------------------------------
		return response()->json
		(
			[
				'task_id'=>$task_id,
				'currentid'=> $nextId ?? null,
				'order_no'=> $next_order_no ?? null,
				'name' => $name ?? null,
				'subchecklist' => $subChklistArr,
				'subcategoryname' => $subcategoryname,
				'categoryName' => $categoryName,
				'next_rejected_region'=> $next_rejected_region ?? '',
				'next_approve'=>$next_approve,
				'existingNextFiles'=>$existingFiles,
				'fetchsubChklistArr'=>$fetchsubChklistArr,
				'existingSubChecklistFiles'=>$existingSubChecklistFiles,
				'checklistdata'=>$checklistdata,
				'progressStatus'=>$progressStatus
			]
		);
	}
	public function checklist_previous_question(Request $request)
	{
		$task_id = $request->post('task_id');
		$current_question_id = $request->post('current_question_id');
		$order_no = $request->post('order_no');
		$category_id = $request->post('category_id');
		//$subcategory_id = $request->post('subcategory_id'); // 21-05-2025
		$categoryDtls = Category::where('id',$category_id)->first();
		$categoryName = $categoryDtls ? $categoryDtls->name : '';
		
		$nextQuestionExists = Checklist::where('category_id', $category_id)
		//->where('subcategory_id', $subcategory_id) // 21-05-2025
		->where('status', '!=', 2)
		//->where('id', '<', $current_question_id)
		->where('order_no', '<', $order_no)
		->orderByRaw('CAST(order_no as UNSIGNED) desc')
		//->orderBy('id', 'desc')
		->exists();
		
		$nextId = '';
		$name  = '';
		$subchecklist = '';
		$subcategoryname = '';
		$next_approve  = '';
		
		$subChklistArr = [];
		$existingSubChecklistFiles = [];
		$existingFiles = [];
		$fetchsubChklistArr = [];
		
		if($nextQuestionExists)
		{
			$nextQuestion = Checklist::with('get_subchecklist','get_category')->where('category_id', $category_id)
			->where('category_id', $category_id)
			//->where('subcategory_id', $subcategory_id) // 21-05-2025
			->where('status', '!=', 2)
			->where('order_no', '<', $order_no)
			//->where('id', '<', $current_question_id)
			->orderByRaw('CAST(order_no as UNSIGNED) desc')
			//->orderBy('id', 'desc')
			->first();
			//echo "<pre>";print_r($nextQuestion);die;
			$nextId = $nextQuestion->id;
			$name = $nextQuestion->name;
			$order_no = $nextQuestion->order_no;
			//$subChklistArr = [];
			if(!empty($nextQuestion->get_subchecklist))
			{
				//$subchecklist = $nextQuestion->get_subchecklist;
				foreach($nextQuestion->get_subchecklist as $subchecklists)
				{
					$subChklistArr[] = [
						'id' => $subchecklists->id,
						'name' => $subchecklists->name
					];
				}
				
				$subcategoryname ='';
				//$subcategoryname = $nextQuestion->get_subcategory->name;
			}
			
			// fetch data from task_list_checklist
			// -- 21-05-2025---
			/*$iffetch  = Task_list_checklists::where('task_list_id', $task_id)->where('task_list_subcategory_id', $subcategory_id)->where('checklist_id', $nextId)->first();*/
			//--------------
			
			$iffetch  = Task_list_checklists::where('task_list_id', $task_id)->where('checklist_id', $nextId)->first();
			
			$next_rejected_region = $iffetch ? $iffetch->rejected_region : null;
			$next_approve = $iffetch ? $iffetch->approve : '';
			
			// fetch files 
			$task_list_checklist_id = $iffetch ? $iffetch->id : null;
			//$existingFiles = []; // 22-05-2025
			if (isset($task_list_checklist_id)) {
				$imageData = Task_list_checklist_rejected_files::where('task_list_checklist_id', $task_list_checklist_id)->get();
				foreach ($imageData as $file) {
					$filename = $file->file;
					$existingFiles[] = [
						'name' => $filename,
						'size' => file_exists(public_path('uploads/reject-files/' . $filename)) ? filesize(public_path('uploads/reject-files/' . $filename)) : 123456, // default if unknown
						'url' => asset('uploads/reject-files/' . $filename),
					];
				}
			}
			
			// fetch data from task_list_subchecklist
			//$fetchsubChklistArr = []; // 22-05-2025
			$ifsubfetch  = Task_list_subchecklists::where('task_list_id', $task_id)
							//->where('task_list_subcategory_id', $subcategory_id) // 21-05-2025
							->where('task_list_checklist_id', $nextId)
							->get();
			if($ifsubfetch->isNotEmpty())
			{
				foreach($ifsubfetch as $subchecklistval)
				{
					$fetchsubChklistArr[] = [
						'subchecklist_id' => $subchecklistval->subchecklist_id,
						'rejected_region' => $subchecklistval->rejected_region ?? '',
						'approve' => $subchecklistval->approve
					];
					
					// fetch files for subchecklist
					if(isset($subchecklistval->id))
					{
						$imageSubChecklistData = Task_list_subchecklist_rejected_files::where('task_list_subchecklist_id', $subchecklistval->id)->get();
						foreach ($imageSubChecklistData as $file) {
							$filename = $file->file;
							$existingSubChecklistFiles[] = [
								'name' => $filename,
								'subchecklist_id' => $subchecklistval->subchecklist_id,
								'size' => file_exists(public_path('uploads/reject-files/subchecklist/' . $filename)) ? filesize(public_path('uploads/reject-files/subchecklist/' . $filename)) : 123456, // default if unknown
								'url' => asset('uploads/reject-files/subchecklist/' . $filename),
							];
						}
					}
				}
			}
		}
		return response()->json(
			[
				'task_id'=>$task_id,
				'currentid'=> $nextId ?? null,
				'order_no'=> $order_no ?? null,
				'name' => $name ?? null,
				'subchecklist' => $subChklistArr,
				'subcategoryname' => $subcategoryname,
				'categoryName' => $categoryName,
				'next_rejected_region'=> $next_rejected_region ?? '',
				'next_approve'=>$next_approve,
				'existingPreviousFiles'=>$existingFiles,
				'fetchsubChklistArr'=>$fetchsubChklistArr,
				'existingSubChecklistFiles'=>$existingSubChecklistFiles
			]
		);
	}
	public function single_reject_files(Request $request)
	{
		$current_checklist_id = $request->post('current_checklist_id');
		$subcategory_id = '';
		//$subcategory_id = $request->post('subcategory_id'); // 21-05-2025
		$task_id = $request->post('task_id');
		
		if($request->hasFile('file')) {
			$file = $request->file('file');
			$destinationPath = public_path('uploads/temp-reject-files');

			if (!file_exists($destinationPath)) {
				mkdir($destinationPath, 0777, true);
			}

			$filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();

			$file->move($destinationPath, $filename);
			
			
			//---add record to table 
			$tempmodel = new Task_list_checklist_temp_rejected_files();
			$tempmodel->inspector_id = auth()->user()->id;
			$tempmodel->task_id = $task_id ?? null;
			$tempmodel->task_list_checklist_id = $current_checklist_id ?? null;
			//$tempmodel->subcategory_id = $subcategory_id ?? null; // 21-05-2025
			$tempmodel->file = $filename;
			$tempmodel->save();
			//-------
			
			return response()->json(['success' => true, 'filename' => $filename, 'checklist_id' =>$current_checklist_id, 'subcategory_id' =>$subcategory_id, 'task_id' =>$task_id]);
		}

      return response()->json(['success' => false, 'message' => 'No file uploaded.'], 400);
	}
	
	public function delete_reject_file(Request $request)
	{
		$filename = $request->post('filename');

		if (!$filename) {
			return response()->json(['success' => false, 'message' => 'Filename missing.'], 400);
		}

		$deleted = Task_list_checklist_temp_rejected_files::where('file', $filename)->delete();

		$filePath = public_path('uploads/temp-reject-files/' . $filename);
		if (file_exists($filePath)) {
			unlink($filePath);
		}

		return response()->json(['success' => true, 'message' => 'File deleted.']);
	}
	public function checklist_file_delete(Request $request)
	{
		$filename = $request->post('filename');

		if (!$filename) {
			return response()->json(['success' => false, 'message' => 'Filename missing.'], 400);
		}
		
		// --get the task_list_checklist_id for count files
		$task_list_checklist_id = Task_list_checklist_rejected_files::where('file', $filename)->first()->task_list_checklist_id;
		// ----------------------------------------------

		Task_list_checklist_rejected_files::where('file', $filename)->delete();

		$filePath = public_path('uploads/reject-files/' . $filename);
		if (file_exists($filePath)) {
			unlink($filePath);
		}
		
		// for check form validation 
		$count = Task_list_checklist_rejected_files::where('task_list_checklist_id', $task_list_checklist_id)->count();
		
		return response()->json(['success' => true, 'message' => 'File deleted.', 'count'=>$count]);
	}
	
	public function reject_subchecklist_files(Request $request)
	{
		$current_checklist_id = $request->post('current_checklist_id');
		$subchecklist_id = $request->post('subchecklist_id');
		$task_id = $request->post('task_id');
		
		if($request->hasFile('file')) {
			$file = $request->file('file');
			$destinationPath = public_path('uploads/temp-reject-files');

			if (!file_exists($destinationPath)) {
				mkdir($destinationPath, 0777, true);
			}

			$filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();

			$file->move($destinationPath, $filename);
			
			
			//---add record to table 
			$tempmodel = new Task_list_subchecklist_temp_rejected_files();
			$tempmodel->inspector_id = auth()->user()->id;
			$tempmodel->task_list_id = $task_id ?? null;
			$tempmodel->task_list_checklist_id = $current_checklist_id ?? null;
			$tempmodel->subchecklist_id = $subchecklist_id ?? null;
			$tempmodel->file = $filename;
			$tempmodel->save();
			//-------
			
			return response()->json(['success' => true, 'filename' => $filename, 'checklist_id' =>$current_checklist_id, 'subchecklist_id' =>$subchecklist_id, 'task_id' =>$task_id]);
		}

      return response()->json(['success' => false, 'message' => 'No file uploaded.'], 400);
	}
	
	public function reject_subckecklist_file_delete(Request $request)
	{
		$filename = $request->post('filename');

		if (!$filename) {
			return response()->json(['success' => false, 'message' => 'Filename missing.'], 400);
		}

		$deleted = Task_list_subchecklist_temp_rejected_files::where('file', $filename)->delete();

		$filePath = public_path('uploads/temp-reject-files/' . $filename);
		if (file_exists($filePath)) {
			unlink($filePath);
		}

		return response()->json(['success' => true, 'message' => 'File deleted.']);
	}
	
	public function subchecklist_file_delete(Request $request)
	{
		$filename = $request->post('filename');

		if (!$filename) {
			return response()->json(['success' => false, 'message' => 'Filename missing.'], 400);
		}
		
		// --get the task_list_subchecklist_id for count files
		$task_list_subchecklist_id = Task_list_subchecklist_rejected_files::where('file', $filename)->first()->task_list_subchecklist_id;
		// ----------------------------------------------

		Task_list_subchecklist_rejected_files::where('file', $filename)->delete();

		$filePath = public_path('uploads/reject-files/subchecklist/' . $filename);
		if (file_exists($filePath)) {
			unlink($filePath);
		}
		
		// for check form validation 
		$count = Task_list_subchecklist_rejected_files::where('task_list_subchecklist_id', $task_list_subchecklist_id)->count();
		
		$subchecklist_id = Task_list_subchecklists::where('id', $task_list_subchecklist_id)->first()->subchecklist_id;
		return response()->json(['success' => true, 'message' => 'File deleted.', 'count'=>$count, 'subchecklist_id'=>$subchecklist_id]);
	}
	public function completed_task($task_id ='', $cat_id='', $subcat_id='')
	{
		
		$data['checklistdata'] = Checklist::where('category_id',$cat_id)
									->where('subcategory_id', $subcat_id)
									->where('status','!=', 2)->get();
									
		$data['task_id'] = 	$task_id;						
		$data['category_id'] = 	$cat_id;						
		$data['subcategory_id'] = 	$subcat_id;						
		//echo "<pre>";print_r($checklistdata);die;
		return view('inspector.completed-task', $data);
	}
	public function submit_completed_task(Request $request)
	{
		$task_id = $request->task_id;
		$category_id = $request->category_id;
		//$subcategory_id = $request->subcategory_id; // 21-05-2025
		$exists = Task_list_subcategories::where('task_list_id', $task_id)
				->where('task_list_category_id', $category_id)->exists();;
				//->where('subcategory_id', $subcategory_id) // 21-05-2025
				//->exists();
		if(!$exists)
		{
			$model = new Task_list_subcategories();
			$model->task_list_id = $task_id ?? null;
			$model->task_list_category_id = $category_id ?? null;
			//$model->subcategory_id = null; // 21-05-2025
			$model->total_task = 0;
			$model->completed_task = 0;
			$model->is_submit = 1;
			$model->save();
		}
		return response()->json(['msg'=>'success']);
	}
	public function get_checklist_page(Request $request)
	{
		$current_question_id = $request->checklist_id;
		$task_id = $request->task_id;
		$category_id = $request->cat_id;
		$categoryDtls = Category::where('id',$category_id)->first();
		$categoryName = $categoryDtls ? $categoryDtls->name : '';
		$checklistDtls = Checklist::where('id', $current_question_id)->where('category_id', $category_id)->first();
		$order_no = $checklistDtls ? $checklistDtls->order_no : '';
		//$subcategory_id = $request->subcat_id; // 21-05-2025
		$subChklistArr = [];
		$existingFiles = [];
		$existingSubChecklistFiles = [];
		
		/*$checklistdata= Checklist::with('get_subchecklist','get_category','get_subcategory')
		->where('category_id',$category_id)->where('subcategory_id', $subcategory_id)
		->where('status','!=', 2)->first();*/
		
		// fetch record with respect ti checklist
		$nextQuestion = Checklist::with('get_subchecklist','get_category')->where('category_id', $category_id)
			->where('category_id', $category_id)
			//->where('subcategory_id', $subcategory_id) // 21-05-2025
			->where('status', '!=', 2)
			->where('id', $current_question_id)
			->orderBy('id', 'asc')
			->first();
			//echo "<pre>";print_r($nextQuestion);die;
			$nextId = $nextQuestion->id;
			$name = $nextQuestion->name;
			
			if(!empty($nextQuestion->get_subchecklist))
			{
				//$subchecklist = $nextQuestion->get_subchecklist;
				foreach($nextQuestion->get_subchecklist as $subchecklists)
				{
					$subChklistArr[] = [
						'id' => $subchecklists->id,
						'name' => $subchecklists->name
					];
				}
				
				$subcategoryname = '';
				//$subcategoryname = $nextQuestion->get_subcategory->name; //21-05-2025
			}
			
			// fetch data from task_list_checklist
			// -- 21-05-2025 ---
			/*$iffetch  = Task_list_checklists::where('task_list_id', $task_id)->where('task_list_subcategory_id', $subcategory_id)->where('checklist_id', $nextId)->first();*/
			//--------
			
			$iffetch  = Task_list_checklists::where('task_list_id', $task_id)->where('checklist_id', $nextId)->first();
			
			$next_rejected_region = $iffetch ? $iffetch->rejected_region : null;
			$next_approve = $iffetch ? $iffetch->approve : '';
			
			// fetch files 
			$task_list_checklist_id = $iffetch ? $iffetch->id : null;
			//$existingFiles = [];
			if (isset($task_list_checklist_id)) {
				$imageData = Task_list_checklist_rejected_files::where('task_list_checklist_id', $task_list_checklist_id)->get();
				foreach ($imageData as $file) {
					$filename = $file->file;
					$existingFiles[] = [
						'name' => $filename,
						'size' => file_exists(public_path('uploads/reject-files/' . $filename)) ? filesize(public_path('uploads/reject-files/' . $filename)) : 123456, // default if unknown
						'url' => asset('uploads/reject-files/' . $filename),
					];
				}
			}
			
			// fetch data from task_list_subchecklist
			$fetchsubChklistArr = [];
			$ifsubfetch  = Task_list_subchecklists::where('task_list_id', $task_id)
							//->where('task_list_subcategory_id', $subcategory_id) // 21-05-2025
							->where('task_list_checklist_id', $nextId)
							->get();
			if($ifsubfetch->isNotEmpty())
			{
				foreach($ifsubfetch as $subchecklistval)
				{
					$fetchsubChklistArr[] = [
						'subchecklist_id' => $subchecklistval->subchecklist_id,
						'rejected_region' => $subchecklistval->rejected_region ?? '',
						'approve' => $subchecklistval->approve
					];
					
					// fetch files for subchecklist
					if(isset($subchecklistval->id))
					{
						$imageSubChecklistData = Task_list_subchecklist_rejected_files::where('task_list_subchecklist_id', $subchecklistval->id)->get();
						foreach ($imageSubChecklistData as $file) {
							$filename = $file->file;
							$existingSubChecklistFiles[] = [
								'name' => $filename,
								//'subchecklist_id' => $file->task_list_subchecklist_id,
								'subchecklist_id' => $subchecklistval->subchecklist_id,
								'size' => file_exists(public_path('uploads/reject-files/subchecklist/' . $filename)) ? filesize(public_path('uploads/reject-files/subchecklist/' . $filename)) : 123456, // default if unknown
								'url' => asset('uploads/reject-files/subchecklist/' . $filename),
							];
						}
					}
				}
			}
			
			//------- progress bar work ---------------
			$total_checklist = Checklist::where('category_id', $category_id)
								//->where('subcategory_id', $subcategory_id) // 21-05-2025
								->get();
			$countCheklist  = $total_checklist->count();
			$percentage = ceil(100/$countCheklist);
			
			if(!empty($total_checklist))
			{
				$barHtml = '<div class="d-flex justify-content-between mb-3" style="gap: 4px;" id="progress-bar-section">';
				foreach($total_checklist as $val)
				{
						$progressStatus = '';
						$hasTaskChecklist = Task_list_checklists::where('task_list_id', $task_id)
										//->where('task_list_subcategory_id', $subcategory_id) // 21-05-2025
										->where('checklist_id', $val->id)->exists();
						if($hasTaskChecklist)
						{
							$progressStatus = 'completed';
						}
						else 
						{
							$hasTaskSubChecklist = Task_list_subchecklists::where('task_list_id', $task_id)
										//->where('task_list_subcategory_id', $subcategory_id)  // 21-05-2025
										->where('task_list_checklist_id', $val->id)->exists();
							if($hasTaskSubChecklist)
							{
								$progressStatus = 'completed';
							}
						}
					
					$barHtml .= '<div class="step-block '.$progressStatus .'" style="width:{{ $percentage  }}%;" id="progress-status-{{ $val->id }}"></div>';
				}
				$barHtml .= '</div>';
			}
			
			
			
			//-----------------------------------------
			
			return response()->json
			(
				[
					'task_id'=>$task_id,
					'currentid'=> $nextId ?? null,
					'name' => $name ?? null,
					'subchecklist' => $subChklistArr,
					'subcategoryname' => $subcategoryname,
					'order_no' => $order_no,
					'categoryName' => $categoryName,
					'next_rejected_region'=> $next_rejected_region ?? '',
					'next_approve'=>$next_approve,
					'existingNextFiles'=>$existingFiles,
					'fetchsubChklistArr'=>$fetchsubChklistArr,
					'existingSubChecklistFiles'=>$existingSubChecklistFiles,
					'category_id'=>$category_id,
					'subcategory_id'=>'',
					'directEdit' => $request->directEdit,
					//'subcategory_id'=>$subcategory_id,// 21-05-2025
					'barHtml'=>$barHtml
				]
			);
	}
	
	public function thank_you($id='')
	{
		if (auth()->user()->user_type != 1) {
			return redirect('inspector-dashboard');
		}
		
		$data = [];
		$data['task_id'] = $id;
		return view('inspector.thankyou', $data);
	}
	public function location_owner($lid='',$taskid='', $active='')
	{
		if (auth()->user()->user_type == 1 || auth()->user()->user_type == 3) {
			return redirect('inspector-dashboard');
		}
		//--- if location owner login ---
		$correctiveActionChecklistArray = [];
		$correctiveActionSubcheckListArray = [];
		$correctiveCheckChecklistArray = [];
		$correctiveCheckSubcheckListArray = [];
		
		$correctiveNeddedChecklistArray = [];
		$completedApprChecklistArray = [];
		
		$correctiveNeddedSubchecklistArray = [];
		$completedApprSubcheckListArray = [];
		
		if(auth()->user()->user_type == 2)
		{
			$user_type = auth()->user()->user_type;
			//$taskData = Task_lists::where('location_id', $lid)->where('category_id', $catid)->get();
			$taskData = Task_lists::where('location_id', $lid)->where('id', $taskid)->get();
			if($taskData->isNotEmpty())
			{
				foreach($taskData as $val)
				{
					// checklist and  respective files approve=0 
					$taskChklist = Task_list_checklists::where('task_list_id', $val->id)->where('approve', 0)->get();
					if($taskChklist->isNotEmpty())
					{
						foreach($taskChklist as $task)
						{
							
							$task_list_checklist_corrective_action = Task_list_corrective_action::where('task_list_id', $val->id)
							->where('checklist_id', $task->checklist_id)
							->first();
							
							if(!$task_list_checklist_corrective_action)
							{								
								$isfiles = '';
								$images = '';
								$isfiles = Task_list_checklist_rejected_files::where('task_list_checklist_id', $task->id)->first();
								
								$images = $isfiles ? $isfiles->file  : '';
								$correctiveActionChecklistArray[] = [
											'type' => 'checklist',
											'task_id' => $val->id,
											'checklist_id' => $task->checklist_id,
											'rejected_region' => $task->rejected_region,
											'image' => $images,
										];
							}
							else
							{
								$isfiles = '';
								$images = '';
								$isfiles = Task_list_checklist_rejected_files::where('task_list_checklist_id', $task->id)->first();
								$images = $isfiles ? $isfiles->file  : '';
								$correctiveCheckChecklistArray[] = [
											'type' => 'checklist',
											'task_id' => $val->id,
											'checklist_id' => $task->checklist_id,
											'rejected_region' => $task->rejected_region,
											'image' => $images,
											'inspector_action'=> $task_list_checklist_corrective_action->inspector_action,
											'los_action'=> $task_list_checklist_corrective_action->los_action,
											'second_checked'=> $task_list_checklist_corrective_action->lo_corrective_action_plan_second_check,
											'lo_direct_approve'=> $task_list_checklist_corrective_action->lo_direct_approve,
										];
							}
							
							
						}
					}
					
					// subchecklist and respective files
					$taskSubChklist = Task_list_subchecklists::where('task_list_id', $val->id)->where('approve', 0)->get();
					if($taskSubChklist->isNotEmpty())
					{
						foreach($taskSubChklist as $subtask)
						{
							$task_list_subchecklist_corrective_action = Task_list_corrective_action::where('task_list_id', $val->id)
							->where('checklist_id', $subtask->task_list_checklist_id)
							->where('subchecklist_id', $subtask->subchecklist_id)
							->first();
							
							if(!$task_list_subchecklist_corrective_action)
							{
								$isSubChecklistfiles = '';
								$subChecklistimages = '';
								$isSubChecklistfiles = Task_list_subchecklist_rejected_files::where('task_list_subchecklist_id', $subtask->id)->first();
								
								$subChecklistimages = $isSubChecklistfiles ? $isSubChecklistfiles->file  : '';
								$correctiveActionSubcheckListArray[] = [
											'type' => 'subchecklist',
											'task_id' => $val->id,
											'checklist_id' => $subtask->task_list_checklist_id,
											'subchecklist_id'=>$subtask->subchecklist_id,
											'rejected_region' => $subtask->rejected_region,
											'image' => $subChecklistimages,
										];
							}
							else
							{
								$isSubChecklistfiles = '';
								$subChecklistimages = '';
								$isSubChecklistfiles = Task_list_subchecklist_rejected_files::where('task_list_subchecklist_id', $subtask->id)->first();
								
								$subChecklistimages = $isSubChecklistfiles ? $isSubChecklistfiles->file  : '';
								$correctiveCheckSubcheckListArray[] = [
											'type' => 'subchecklist',
											'task_id' => $val->id,
											'checklist_id' => $subtask->task_list_checklist_id,
											'subchecklist_id'=>$subtask->subchecklist_id,
											'rejected_region' => $subtask->rejected_region,
											'image' => $subChecklistimages,
											'inspector_action'=> $task_list_subchecklist_corrective_action->inspector_action,
											'los_action'=> $task_list_subchecklist_corrective_action->los_action,
											'second_checked'=> $task_list_subchecklist_corrective_action->lo_corrective_action_plan_second_check,
											'lo_direct_approve'=> $task_list_subchecklist_corrective_action->lo_direct_approve,
										];
								
							}
						}
						
					}
					//----------------------12-06-2025----------------------------
					// checklist and  respective files approve=1 
					$taskChklist = Task_list_checklists::where('task_list_id', $val->id)->get();
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
										];
								}
								else
								{
									$isfiles = '';
									$images = '';
									$isfiles = Task_list_checklist_rejected_files::where('task_list_checklist_id', $task->id)->first();
									$images = $isfiles ? $isfiles->file  : '';
									
									//---- new implement 
									$correctiveNeddedChecklistArray[] = [
											'type' => 'checklist',
											'task_id' => $val->id,
											'checklist_id' => $task->checklist_id,
											'rejected_region' => $task->rejected_region,
											'image' => $images,
											'inspector_action'=> $task_list_checklist_corrective_needed->inspector_action,
											'los_action'=> $task_list_checklist_corrective_needed->los_action,
										];
									//------
									
									$completedApprChecklistArray[] = [
												'type' => 'checklist',
												'task_id' => $val->id,
												'checklist_id' => $task->checklist_id,
												'rejected_region' => $task->rejected_region,
												'image' => $images,
												'inspector_action'=> $task_list_checklist_corrective_needed->inspector_action,
												'los_action'=> $task_list_checklist_corrective_needed->los_action,
												//'second_checked'=> $task_list_checklist_corrective_action->lo_corrective_action_plan_second_check,
											];
								}
								
							}
							else{
								$completedApprChecklistArray[] = [
												'type' => 'checklist',
												'task_id' => $val->id,
												'checklist_id' => $task->checklist_id,
												'rejected_region' => $task->rejected_region,
												'inspector_action'=> 1,
												'los_action'=> 1,
											];
							}
						}
					}
					
					// subchecklist and respective files
					$taskSubChklist = Task_list_subchecklists::where('task_list_id', $val->id)->get();
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
											];
								}
								else
								{
									$isSubChecklistfiles = '';
									$subChecklistimages = '';
									$isSubChecklistfiles = Task_list_subchecklist_rejected_files::where('task_list_subchecklist_id', $subtask->id)->first();
									
									$subChecklistimages = $isSubChecklistfiles ? $isSubChecklistfiles->file  : '';
									
									// new implement 
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
									//-------
									
									$completedApprSubcheckListArray[] = [
												'type' => 'subchecklist',
												'task_id' => $val->id,
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
							else
							{
								$completedApprSubcheckListArray[] = [
										'type' => 'subchecklist',
										'task_id' => $val->id,
										'checklist_id' => $subtask->task_list_checklist_id,
										'subchecklist_id'=>$subtask->subchecklist_id,
										'rejected_region' => $subtask->rejected_region,
										'inspector_action' => 1,
										'los_action' => 1,
									];
								
							}
						}
						
					}
					
					
				}
				
				//echo count($correctiveActionSubcheckListArray); die;
				/*$data['correctiveAction'] = array_merge($correctiveActionChecklistArray, $correctiveActionSubcheckListArray);
				
				$data['correctiveCheck'] = array_merge($correctiveCheckChecklistArray, $correctiveCheckSubcheckListArray);
				
				$data['total_corrective_action'] = count($correctiveActionChecklistArray) + count($correctiveActionSubcheckListArray);*/
				
			}
			
			$data['correctiveAction'] = array_merge($correctiveActionChecklistArray, $correctiveActionSubcheckListArray);
				
			$data['correctiveCheck'] = array_merge($correctiveCheckChecklistArray, $correctiveCheckSubcheckListArray);
			
			$data['total_corrective_action'] = count($correctiveActionChecklistArray) + count($correctiveActionSubcheckListArray);
			
			//-----------12-06-2025--------------------
			$data['correctiveNeeded'] = array_merge($correctiveNeddedChecklistArray,$correctiveNeddedSubchecklistArray);
			$data['approvedCompleted'] = array_merge($completedApprChecklistArray,$completedApprSubcheckListArray);
			//------------------------------------------
			
			$data['userdata'] = User::with('get_user_location')->where('id', auth()->user()->id)->first();
			$data['location_id'] = $lid;
			//$data['category_id'] = $catid;
			$data['task_id'] = $taskid;
			$data['isactive'] = $active;
			
			return view('inspector.location-owner', $data);
		}
	}
	public function location_owner_question_reply($task_id='',$checklist_id='',$type='', $tab='', $lid='')
	{
		if (auth()->user()->user_type == 1 || auth()->user()->user_type == 3) {
			return redirect('inspector-dashboard');
		}
		
		$data = [];
		$data['task_id'] = $task_id ?? '';
		$data['checklist_id'] = $checklist_id ?? '';
		$data['type'] = $type ?? '';
		$data['tab'] = $tab ?? '';
		$data['location_id'] = $lid ?? '';
		
		return view('inspector.location-owner-question-reply', $data);
	}
	public function location_owner_subchecklist_question_reply($task_id='',$checklist_id='',$subchecklist_id='',$type='', $tab='', $lid='')
	{
		if (auth()->user()->user_type == 1 || auth()->user()->user_type == 3) {
			return redirect('inspector-dashboard');
		}
		
		$data = [];
		$data['task_id'] = $task_id ?? '';
		$data['checklist_id'] = $checklist_id ?? '';
		$data['subchecklist_id'] = $subchecklist_id ?? '';
		$data['type'] = $type ?? '';
		$data['tab'] = $tab ?? '';
		$data['location_id'] = $lid ?? '';
		return view('inspector.location-owner-question-reply', $data);
	}
	public function submit_lo_corrective_action(Request $request)
	{
		//echo "<pre>"; print_r($request->all());die;
		$type 				= $request->type;
		$task_list_id 		= $request->task_id;
		$checklist_id 		= $request->checklist_id;
		$subchecklist_id 	= $request->subchecklist_id ?? null;
		$tab 				= $request->tab;
		
		if($request->lo_direct_approve == 'false')
		{
			$date = date('Y-m-d', strtotime($request->post('hidden_set_date')));
			$time = $request->post('hidden_set_time');
			$datetime  = $date.' '.$time;
			$lo_completed_by = date('Y-m-d', strtotime($datetime));
		}
		else{
			$lo_completed_by = date('Y-m-d h:i:s');
		}
		//echo $lo_completed_by; die;
		
		$taskData  = Task_lists::where('id', $task_list_id)->first();
		$inspector_id = $taskData ? $taskData->inspector_id : null;
		$location_id = $taskData ? $taskData->location_id : null;
		//$category_id = $taskData ? $taskData->category_id : null; 22-05-2025
		$los_id = $taskData ? $taskData->los_id : null;
		
		$model = new Task_list_corrective_action();
		$model->task_list_id = $task_list_id;
		$model->checklist_id = $checklist_id;
		$model->subchecklist_id = $subchecklist_id;
		$model->lo_id = auth()->user()->id;
		$model->lo_corrective_action_plan = $request->lo_corrective_action_plan ?? '';
		$model->lo_completed_by = $lo_completed_by;
		$model->lo_direct_approve = $request->lo_direct_approve == 'true' ? 1 : 0;
		$model->inspector_id = $inspector_id;
		$model->los_id = $los_id;
		$model->save();
		
		// update the status Task lists table
		$taskData  = Task_lists::where('id', $task_list_id)->update(['status'=>2]);
		
		return response()->json(['location_id'=>$location_id, 'task_id'=>$task_list_id]);
	}
	
	public function add_new_task($lid)
	{
		if (auth()->user()->user_type != 1) {
			return redirect('inspector-dashboard');
		}
		
		$data['location_id']  = $lid;
		$locationWisecategory = [];
		$categories = Category::where('location_id', $lid)->get();
		foreach($categories as $category)
		{
			$exists = Checklist::where('category_id', $category->id)->exists();
			if($exists)
			{
				$locationWisecategory[] = [
					'id'  => $category->id,
					'name'  => $category->name,
				];
			}
		}
		$data['locationWisecategory']= $locationWisecategory;
		return view('inspector.add-new-task', $data);
	}
	
	public function save_task_data(Request $request)
	{
		//echo "<pre>";print_r($request->all());
		
		$existingTask = Task_lists::where('location_id', $request->post('location_id'))->where('category_id', $request->post('category_id'))->where('task_title', $request->post('task_title'))->where('status', '!=', 2)
        ->first();
		
		if ($existingTask) {
			return response()->json([
				'success' => false,
				'message' => 'Task name name already exists.'
			]);
		}
		
		$los_id = User::where('company_name', auth()->user()->company_name)->where('user_type', 3)->first()->id;
		
		$date = date('Y-m-d', strtotime($request->post('hidden_set_date')));
		//$time = $request->post('hidden_set_time');
		$time = date('h:i:s');
		$datetime  = $date.' '.$time;
		$created_at = date('Y-m-d H:i:s', strtotime($datetime));
		
		$model=new Task_lists();
		$model->inspector_id	=	auth()->user()->id;
		$model->location_id		=	$request->post('location_id');
		//$model->category_id		=	$request->post('category_id');
		//$model->lo_id			=	;
		$model->los_id			=	$los_id ?? null;
		$model->task_title		=	$request->post('task_title');
		$model->status		=	0;
		$model->created_at	=	$created_at ?? '';
		$model->save();
		$id = $model->id;
		
		// add task_location_category
		$location_category = $request->location_category;
		
		if(!empty($location_category))
		{
			foreach($location_category as $category)
			{
				$locModel = new Task_location_categories();
				$locModel->task_list_id 	= $id;
				$locModel->category_id 		= $category;
				$locModel->save();
			}
		}
		
		// add file
		$fileName = '';
		if($request->hasFile('task_image')) {
			$destinationPath = public_path('uploads/task/');
			if (!file_exists($destinationPath)) {
				mkdir($destinationPath, 0777, true);
			}
			$file = $request->file('task_image');
			$fileName = time() . '_' . $file->getClientOriginalName();
			$file->move($destinationPath, $fileName);
			
			$updtmodel= Task_lists::find($id);
			$updtmodel->image = $fileName;
			$updtmodel->save();
		}
		else{
			$updtmodel= Task_lists::find($id);
			$updtmodel->image = 'default-task-pic.jpg';
			$updtmodel->save();
		}
		
		return response()->json([
			'success' => true
		]);
	}
	public function inspector_checklist_question_reply($location_id='',$task_id='',$checklist_id='',$type='', $tab='')
	{
		if (auth()->user()->user_type == 2) {
			return redirect('inspector-dashboard');
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
		return view('inspector.inspector-check-reply', $data);
	}
	public function inspector_subchecklist_question_reply($location_id='',$task_id='',$checklist_id='',$subchecklist_id='',$type='', $tab='')
	{
		if (auth()->user()->user_type == 2) {
			return redirect('inspector-dashboard');
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
		return view('inspector.inspector-check-reply', $data);
	}
	public function submit_inspector_status(Request $request)
	{
		// when lo send first check and here inspector or los agree or reject
		$task_list_id = $request->task_id;
		$checklist_id = $request->checklist_id;
		$subchecklist_id = $request->subchecklist_id ?? null;
		$user_id = auth()->user()->id;
		$inspector_action = $request->inspector_action;
		
		//$id = Task_list_corrective_action::where('task_list_id', $task_list_id)->where('checklist_id', $checklist_id)->where('inspector_id', $inspector_id)->first()->id;
		
		$id = Task_list_corrective_action::where('task_list_id', $task_list_id)->where('checklist_id', $checklist_id)->first()->id;
		
		$model = Task_list_corrective_action::find($id);
		if($inspector_action == 1)
		{
			if(auth()->user()->user_type == 1)
			{
				$model->inspector_action_date = date('Y-m-d h:i:s');
				$model->inspector_action = $inspector_action;
				$model->inspector_id = $user_id;
			}
			
			if(auth()->user()->user_type == 3)
			{
				$model->los_action_date = date('Y-m-d h:i:s');
				$model->los_action = $inspector_action;
				$model->los_id = $user_id;
			}
		}
		else if($inspector_action == 2)
		{
			$model->inspector_action_date = date('Y-m-d h:i:s');
			$model->inspector_action = $inspector_action;
			//$model->inspector_id = $user_id;
			$model->los_action_date = date('Y-m-d h:i:s');
			$model->los_action = $inspector_action;
			//$model->los_id = $user_id;
		}
		
		$model->save();
		
		// update the status of Task lists 
		if($inspector_action==1)
		{
			Task_lists ::where('id',$task_list_id)->update(['status'=>3]);
		}
		
		return response()->json(['message'=>'success']);
	}
	
	public function location_owner_checklist_rejected_question_reply($task_id='',$checklist_id='',$type='')
	{
		if (auth()->user()->user_type == 1 || auth()->user()->user_type == 3) {
			return redirect('inspector-dashboard');
		}
		
		$data = [];
		$data['task_id'] = $task_id ?? '';
		$data['checklist_id'] = $checklist_id ?? '';
		$data['type'] = $type ?? '';
		$data['tab'] = $tab ?? '';
		
		return view('inspector.location-owner-rejected-question-reply', $data);
	}
	public function location_owner_subchecklist_rejected_question_reply($task_id='',$checklist_id='',$subchecklist_id='',$type='')
	{
		if (auth()->user()->user_type == 1 || auth()->user()->user_type == 3) {
			return redirect('inspector-dashboard');
		}
		
		$data = [];
		$data['task_id'] = $task_id ?? '';
		$data['checklist_id'] = $checklist_id ?? '';
		$data['subchecklist_id'] = $subchecklist_id ?? '';
		$data['type'] = $type ?? '';
		$data['tab'] = $tab ?? '';
		return view('inspector.location-owner-rejected-question-reply', $data);
	}
	
	public function save_lo_reply_rejected_question(Request $request)
	{
		//echo "<pre>";print_r($request->all());die;
		$task_id = $request->task_id;
		$checklist_id = $request->checklist_id;
		$subchecklist_id = $request->subchecklist_id ?? null;
		$type = $request->type;
		$content = $request->content ?? null;
		
		$corrective_action_data = Task_list_corrective_action::where('task_list_id', $task_id)->where('checklist_id', $checklist_id)->first();
		
		$id = $corrective_action_data ? $corrective_action_data->id : '';
		
		$model = Task_list_corrective_action::find($id);
		$model->lo_corrective_action_plan_second_check = $request->inspector_action == 2 ? $content : null;
		//$model->inspector_action = $request->inspector_action;
		$model->inspector_action = 0;
		//$model->los_action = $request->los_action;
		$model->los_action = 0;
		$model->save();
		
		$lo_files = $request->file('lo_file');

		if ($lo_files && is_array($lo_files)) {
			
			// unlink previous file 
			$correctiveFiles = Task_list_corrective_action_file::where('task_list_corrective_actions_id', $id)->get();
			if($correctiveFiles->isNotEmpty()){
				
				foreach($correctiveFiles as $filemn)
				{
					$f_name = $filemn->file;
					$filePath = public_path('uploads/corrective_action/' . $f_name);
					if (file_exists($filePath)) {
						unlink($filePath);
					}
				}
				
				Task_list_corrective_action_file::where('task_list_corrective_actions_id', $id)->delete();
			}
			
			// save new files
			foreach ($lo_files as $file) {
				
				$destinationPath = public_path('uploads/corrective_action');
				if (!file_exists($destinationPath)) {
					mkdir($destinationPath, 0777, true);
				}
				
				$filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
				$file->move($destinationPath, $filename);

				$fileModel = new Task_list_corrective_action_file();
				$fileModel->task_list_corrective_actions_id = $id;
				$fileModel->file = $filename;
				$fileModel->save();
			}
		}
		
		// update the status of Task lists after approve by lo 
		
		Task_lists ::where('id',$task_id)->update(['status'=>4]);
		

		return response()->json(['message'=>'success']);
	}
	
	public function inspector_checklist_second_approve_by_lo($location_id='',$task_id='',$checklist_id='',$type='', $tab='')
	{
		if (auth()->user()->user_type == 2) {
			return redirect('inspector-dashboard');
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
		
		return view('inspector.inspector-check-reply-approved-by-lo', $data);
	}
	public function inspector_subchecklist_second_approve_by_lo($location_id='',$task_id='',$checklist_id='',$subchecklist_id='',$type='', $tab='')
	{
		if (auth()->user()->user_type == 2) {
			return redirect('inspector-dashboard');
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
		
		return view('inspector.inspector-check-reply-approved-by-lo', $data);
	}
	
	public function submit_inspector_approved(Request $request)
	{
		$task_list_id = $request->task_id;
		$checklist_id = $request->checklist_id;
		$subchecklist_id = $request->subchecklist_id ?? null;
		$inspector_id = auth()->user()->id;
		$inspector_action = $request->inspector_action;
		$los_action = $request->los_action;
		
		/*$id = Task_list_corrective_action::where('task_list_id', $task_list_id)->where('checklist_id', $checklist_id)->where('inspector_id', $inspector_id)->first()->id;*/
		$id = Task_list_corrective_action::where('task_list_id', $task_list_id)->where('checklist_id', $checklist_id)->first()->id;
		
		$model = Task_list_corrective_action::find($id);
		$model->inspector_action_date = date('Y-m-d h:i:s');
		if($inspector_action == 2 || $los_action == 2)
		{
			$model->lo_corrective_action_plan_second_check = null;
		}
		
		if($inspector_action != '')
		{
			$model->inspector_action = $inspector_action;
		}
		
		if($los_action != '')
		{
			$model->los_action = $los_action;
		}
		$model->inspector_id = $inspector_id;
		$model->save();
		
		// update the status of Task lists after final approve by inspector or los 
		Task_lists ::where('id',$task_list_id)->update(['status'=>5]);
		
		return response()->json(['message'=>'success']);
	}
	public function get_final_edit_page(Request $request)
	{
		$task_id = $request->task_id;
		$category_id = $request->category_id;
		$categoryName = Category::where('id', $category_id)->first()->name;
		
		$checklists = Checklist::where('category_id',$category_id)->where('status','!=', 2)->orderBy('order_no')->get();
									
			foreach ($checklists as $chklist) {
				$status = '';
				$hasTaskChecklist = Task_list_checklists::where('task_list_id', $task_id)->where('checklist_id', $chklist->id)->exists();
				if($hasTaskChecklist)
				{
					$status = Task_list_checklists::where('task_list_id', $task_id)->where('checklist_id', $chklist->id)->first()->approve;
					
				}
				else
				{
					$hasTaskSubChecklist = Task_list_subchecklists::where('task_list_id', $task_id)->where('task_list_checklist_id', $chklist->id)->exists();
					if($hasTaskSubChecklist)
					{
						$getstatus = Task_list_subchecklists::where('task_list_id', $task_id)
									->where('task_list_checklist_id', $chklist->id)->get();
						if($getstatus->isNotEmpty())
						{
							$status = 1;
							foreach($getstatus as $val)
							{
								if($val->approve == 0)
								{
									$status = 0;
								}
							}
						}
					}
				}

				$checklistdata[] = [
					'id' => $chklist->id,
					'name' => $chklist->name,
					'approve' => $status,
				];
			}						
			
			//echo "<pre>"; print_r($checklistdata);die;
			
			// update the status of Task_list table
			Task_lists::where('id', $task_id)->update(['status'=> 1]);
		
		return response()->json
		(
			[
				'task_id'=>$task_id,
				'category_id'=>$category_id,
				'currentid'=> '',
				'order_no'=> null,
				'name' => '',
				'subchecklist' => array(),
				'subcategoryname' => '',
				'categoryName' => $categoryName,
				'next_rejected_region'=> '',
				'next_approve'=>'',
				'existingNextFiles'=> array(),
				'fetchsubChklistArr'=> '',
				'existingSubChecklistFiles'=> array(),
				'checklistdata'=>$checklistdata,
				'progressStatus'=>1
			]
		);
	}
	
	public function inspector_filter($lid='', $active='')
    {
		if (auth()->user()->user_type == 2) {
			return redirect('inspector-dashboard');
		}
		
		$data = [];
		//$active = 1;
		$correctiveNeddedChecklistArray = [];
		$completedApprChecklistArray = [];
		
		$correctiveNeddedSubchecklistArray = [];
		$completedApprSubcheckListArray = [];
		// -- if inspector login 
		//$data['categoryData'] = Category::with('get_subcategory')->where('id', $catid)->where('location_id', $lid)->get();

		/*$data['categoryData'] = Category::whereIn('id', function($query) use ($task_id) {
				$query->select('category_id')
				  ->from('task_location_categories')
				  ->where('task_list_id', $task_id);
		})->orderBy('order_no')->get();*/
		
		
        //echo "<pre>";print_r($categoryData);die;
		
		$data['location_id'] = $lid;
		//$details = Task_lists::where('id',$task_id)->where('inspector_id', auth()->user()->id)->where('location_id', $lid)->first();
		/*$details = Task_lists::where('id',$task_id)->where('location_id', $lid)->first();
		$data['location_details'] = $details ? $details->location_details : null;
		$data['task_id'] = $details ? $details->id : null;
		$data['task_name'] = $details ? $details->task_title : null;*/
		$data['task_id'] = '';
		$data['task_name'] = '';
		$data['isactive'] = $active;
		
		// for corrective checked work 
		$correctiveActionChecklistArray = [];
		//$taskData = Task_lists::where('location_id', $lid)->where('id', $task_id)->get();
		
		//-- 23-06-2025--
		$taskData = Task_lists::where('location_id', $lid)->get();
		//------
		
		if($taskData->isNotEmpty())
		{
			foreach($taskData as $val)
			{
				//$correctiveActions = Task_list_corrective_action::where('task_list_id', $val->id)->where('inspector_id', auth()->user()->id)->get();
				
				$correctiveActions = Task_list_corrective_action::where('task_list_id', $val->id)->get();
				
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
				
				//----------------------12-05-2025----------------------------
				// checklist and  respective files approve=1 
				$taskChklist = Task_list_checklists::where('task_list_id', $val->id)->get();
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
											//'second_checked'=> $task_list_checklist_corrective_action->lo_corrective_action_plan_second_check,
										];
							}
							
						}
						else{
							$completedApprChecklistArray[] = [
											'type' => 'checklist',
											'task_id' => $val->id,
											'checklist_id' => $task->checklist_id,
											'rejected_region' => $task->rejected_region,
											'inspector_action' => 1,
											'los_action' => 1,
										];
						}
					}
				}
				
				// subchecklist and respective files
				$taskSubChklist = Task_list_subchecklists::where('task_list_id', $val->id)->get();
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
											//'second_checked'=> $task_list_subchecklist_corrective_action->lo_corrective_action_plan_second_check,
										];
								
							}
						}
						else
						{
							$completedApprSubcheckListArray[] = [
									'type' => 'subchecklist',
									'task_id' => $val->id,
									'checklist_id' => $subtask->task_list_checklist_id,
									'subchecklist_id'=>$subtask->subchecklist_id,
									'rejected_region' => $subtask->rejected_region,
									'inspector_action' => 1,
									'los_action' => 1,
									
								];
							
						}
					}
					
				}
					
			} // task array end 
		}
		
		//-----------12-06-2025--------------------
		$data['correctiveNeeded'] = array_merge($correctiveNeddedChecklistArray,$correctiveNeddedSubchecklistArray);
		$data['approvedCompleted'] = array_merge($completedApprChecklistArray,$completedApprSubcheckListArray);
		//------------------------------------------
		//echo "<pre>";print_r($correctiveActionChecklistArray);die;
		$data['correctiveAction'] = $correctiveActionChecklistArray;
		//-----
		return view('inspector.inspector-filter', $data);
    }
	public function los_task_status($lid='',$active='')
    {
		if (auth()->user()->user_type == 2) {
			return redirect('inspector-dashboard');
		}
		
		$data = [];
		
		$correctiveNeddedChecklistArray = [];
		$completedApprChecklistArray = [];
		
		$correctiveNeddedSubchecklistArray = [];
		$completedApprSubcheckListArray = [];
		// -- if inspector login 
		//$data['categoryData'] = Category::with('get_subcategory')->where('id', $catid)->where('location_id', $lid)->get();

		/*$data['categoryData'] = Category::whereIn('id', function($query) use ($task_id) {
				$query->select('category_id')
				  ->from('task_location_categories')
				  ->where('task_list_id', $task_id);
		})->orderBy('order_no')->get();
        //echo "<pre>";print_r($categoryData);die;*/
		
		$data['location_id'] = $lid;
		//$details = Task_lists::where('id',$task_id)->where('inspector_id', auth()->user()->id)->where('location_id', $lid)->first();
		/*$details = Task_lists::where('id',$task_id)->where('location_id', $lid)->first();
		$data['location_details'] = $details ? $details->location_details : null;
		$data['task_id'] = $details ? $details->id : null;
		$data['task_name'] = $details ? $details->task_title : null;*/
		$data['task_id'] ='';
		$data['task_name'] = '';
		$data['isactive'] = $active;
		
		// for corrective checked work 
		$correctiveActionChecklistArray = [];
		/*$taskData = Task_lists::where('location_id', $lid)->where('id', $task_id)->get();*/
		
		$taskData = Task_lists::where('location_id', $lid)->get();
		if($taskData->isNotEmpty())
		{
			foreach($taskData as $val)
			{
				//$correctiveActions = Task_list_corrective_action::where('task_list_id', $val->id)->where('inspector_id', auth()->user()->id)->get();
				
				$correctiveActions = Task_list_corrective_action::where('task_list_id', $val->id)->get();
				
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
				
				//----------------------12-05-2025----------------------------
				// checklist and  respective files approve=1 
				$taskChklist = Task_list_checklists::where('task_list_id', $val->id)->get();
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
											//'second_checked'=> $task_list_checklist_corrective_action->lo_corrective_action_plan_second_check,
										];
							}
							
						}
						else{
							$completedApprChecklistArray[] = [
											'type' => 'checklist',
											'task_id' => $val->id,
											'checklist_id' => $task->checklist_id,
											'rejected_region' => $task->rejected_region,
											'inspector_action' => 1,
											'los_action' => 1,
										];
						}
					}
				}
				
				// subchecklist and respective files
				$taskSubChklist = Task_list_subchecklists::where('task_list_id', $val->id)->get();
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
											//'second_checked'=> $task_list_subchecklist_corrective_action->lo_corrective_action_plan_second_check,
										];
								
							}
						}
						else
						{
							$completedApprSubcheckListArray[] = [
									'type' => 'subchecklist',
									'task_id' => $val->id,
									'checklist_id' => $subtask->task_list_checklist_id,
									'subchecklist_id'=>$subtask->subchecklist_id,
									'rejected_region' => $subtask->rejected_region,
									'inspector_action' => 1,
									'los_action' => 1,
									
								];
							
						}
					}
					
				}
					
			} // task array end 
		}
		
		//-----------12-06-2025--------------------
		$data['correctiveNeeded'] = array_merge($correctiveNeddedChecklistArray,$correctiveNeddedSubchecklistArray);
		$data['approvedCompleted'] = array_merge($completedApprChecklistArray,$completedApprSubcheckListArray);
		//------------------------------------------
		//echo "<pre>";print_r($correctiveActionChecklistArray);die;
		$data['correctiveAction'] = $correctiveActionChecklistArray;
		//-----
		return view('inspector.los-task-status', $data);
    }
	//public function lo_task_status($lid='',$taskid='', $active='')
	public function lo_task_status($lid='', $active='')
	{
		if (auth()->user()->user_type == 1 || auth()->user()->user_type == 3) {
			return redirect('inspector-dashboard');
		}
		//--- if location owner login ---
		$correctiveActionChecklistArray = [];
		$correctiveActionSubcheckListArray = [];
		$correctiveCheckChecklistArray = [];
		$correctiveCheckSubcheckListArray = [];
		
		$correctiveNeddedChecklistArray = [];
		$completedApprChecklistArray = [];
		
		$correctiveNeddedSubchecklistArray = [];
		$completedApprSubcheckListArray = [];
		
		if(auth()->user()->user_type == 2)
		{
			$user_type = auth()->user()->user_type;
			//$taskData = Task_lists::where('location_id', $lid)->where('category_id', $catid)->get();
			/*$taskData = Task_lists::where('location_id', $lid)->where('id', $taskid)->get();*/
			
			$taskData = Task_lists::where('location_id', $lid)->get();
			
			if($taskData->isNotEmpty())
			{
				foreach($taskData as $val)
				{
					// checklist and  respective files approve=0 
					$taskChklist = Task_list_checklists::where('task_list_id', $val->id)->where('approve', 0)->get();
					if($taskChklist->isNotEmpty())
					{
						foreach($taskChklist as $task)
						{
							
							$task_list_checklist_corrective_action = Task_list_corrective_action::where('task_list_id', $val->id)
							->where('checklist_id', $task->checklist_id)
							->first();
							
							if(!$task_list_checklist_corrective_action)
							{								
								$isfiles = '';
								$images = '';
								$isfiles = Task_list_checklist_rejected_files::where('task_list_checklist_id', $task->id)->first();
								
								$images = $isfiles ? $isfiles->file  : '';
								$correctiveActionChecklistArray[] = [
											'type' => 'checklist',
											'task_id' => $val->id,
											'checklist_id' => $task->checklist_id,
											'rejected_region' => $task->rejected_region,
											'image' => $images,
										];
							}
							else
							{
								$isfiles = '';
								$images = '';
								$isfiles = Task_list_checklist_rejected_files::where('task_list_checklist_id', $task->id)->first();
								$images = $isfiles ? $isfiles->file  : '';
								$correctiveCheckChecklistArray[] = [
											'type' => 'checklist',
											'task_id' => $val->id,
											'checklist_id' => $task->checklist_id,
											'rejected_region' => $task->rejected_region,
											'image' => $images,
											'inspector_action'=> $task_list_checklist_corrective_action->inspector_action,
											'los_action'=> $task_list_checklist_corrective_action->los_action,
											'second_checked'=> $task_list_checklist_corrective_action->lo_corrective_action_plan_second_check,
											'lo_direct_approve'=> $task_list_checklist_corrective_action->lo_direct_approve,
										];
							}
							
							
						}
					}
					
					// subchecklist and respective files
					$taskSubChklist = Task_list_subchecklists::where('task_list_id', $val->id)->where('approve', 0)->get();
					if($taskSubChklist->isNotEmpty())
					{
						foreach($taskSubChklist as $subtask)
						{
							$task_list_subchecklist_corrective_action = Task_list_corrective_action::where('task_list_id', $val->id)
							->where('checklist_id', $subtask->task_list_checklist_id)
							->where('subchecklist_id', $subtask->subchecklist_id)
							->first();
							
							if(!$task_list_subchecklist_corrective_action)
							{
								$isSubChecklistfiles = '';
								$subChecklistimages = '';
								$isSubChecklistfiles = Task_list_subchecklist_rejected_files::where('task_list_subchecklist_id', $subtask->id)->first();
								
								$subChecklistimages = $isSubChecklistfiles ? $isSubChecklistfiles->file  : '';
								$correctiveActionSubcheckListArray[] = [
											'type' => 'subchecklist',
											'task_id' => $val->id,
											'checklist_id' => $subtask->task_list_checklist_id,
											'subchecklist_id'=>$subtask->subchecklist_id,
											'rejected_region' => $subtask->rejected_region,
											'image' => $subChecklistimages,
										];
							}
							else
							{
								$isSubChecklistfiles = '';
								$subChecklistimages = '';
								$isSubChecklistfiles = Task_list_subchecklist_rejected_files::where('task_list_subchecklist_id', $subtask->id)->first();
								
								$subChecklistimages = $isSubChecklistfiles ? $isSubChecklistfiles->file  : '';
								$correctiveCheckSubcheckListArray[] = [
											'type' => 'subchecklist',
											'task_id' => $val->id,
											'checklist_id' => $subtask->task_list_checklist_id,
											'subchecklist_id'=>$subtask->subchecklist_id,
											'rejected_region' => $subtask->rejected_region,
											'image' => $subChecklistimages,
											'inspector_action'=> $task_list_subchecklist_corrective_action->inspector_action,
											'los_action'=> $task_list_subchecklist_corrective_action->los_action,
											'second_checked'=> $task_list_subchecklist_corrective_action->lo_corrective_action_plan_second_check,
											'lo_direct_approve'=> $task_list_subchecklist_corrective_action->lo_direct_approve,
										];
								
							}
						}
						
					}
					//----------------------12-06-2025----------------------------
					// checklist and  respective files approve=1 
					$taskChklist = Task_list_checklists::where('task_list_id', $val->id)->get();
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
										];
								}
								else
								{
									$isfiles = '';
									$images = '';
									$isfiles = Task_list_checklist_rejected_files::where('task_list_checklist_id', $task->id)->first();
									$images = $isfiles ? $isfiles->file  : '';
									
									//---- new implement 
									$correctiveNeddedChecklistArray[] = [
											'type' => 'checklist',
											'task_id' => $val->id,
											'checklist_id' => $task->checklist_id,
											'rejected_region' => $task->rejected_region,
											'image' => $images,
											'inspector_action'=> $task_list_checklist_corrective_needed->inspector_action,
											'los_action'=> $task_list_checklist_corrective_needed->los_action,
										];
									//------
									
									$completedApprChecklistArray[] = [
												'type' => 'checklist',
												'task_id' => $val->id,
												'checklist_id' => $task->checklist_id,
												'rejected_region' => $task->rejected_region,
												'image' => $images,
												'inspector_action'=> $task_list_checklist_corrective_needed->inspector_action,
												'los_action'=> $task_list_checklist_corrective_needed->los_action,
												//'second_checked'=> $task_list_checklist_corrective_action->lo_corrective_action_plan_second_check,
											];
								}
								
							}
							else{
								$completedApprChecklistArray[] = [
												'type' => 'checklist',
												'task_id' => $val->id,
												'checklist_id' => $task->checklist_id,
												'rejected_region' => $task->rejected_region,
												'inspector_action'=> 1,
												'los_action'=> 1,
											];
							}
						}
					}
					
					// subchecklist and respective files
					$taskSubChklist = Task_list_subchecklists::where('task_list_id', $val->id)->get();
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
											];
								}
								else
								{
									$isSubChecklistfiles = '';
									$subChecklistimages = '';
									$isSubChecklistfiles = Task_list_subchecklist_rejected_files::where('task_list_subchecklist_id', $subtask->id)->first();
									
									$subChecklistimages = $isSubChecklistfiles ? $isSubChecklistfiles->file  : '';
									
									// new implement 
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
									//-------
									
									$completedApprSubcheckListArray[] = [
												'type' => 'subchecklist',
												'task_id' => $val->id,
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
							else
							{
								$completedApprSubcheckListArray[] = [
										'type' => 'subchecklist',
										'task_id' => $val->id,
										'checklist_id' => $subtask->task_list_checklist_id,
										'subchecklist_id'=>$subtask->subchecklist_id,
										'rejected_region' => $subtask->rejected_region,
										'inspector_action' => 1,
										'los_action' => 1,
									];
								
							}
						}
						
					}
					
					
				}
				
				//echo count($correctiveActionSubcheckListArray); die;
				/*$data['correctiveAction'] = array_merge($correctiveActionChecklistArray, $correctiveActionSubcheckListArray);
				
				$data['correctiveCheck'] = array_merge($correctiveCheckChecklistArray, $correctiveCheckSubcheckListArray);
				
				$data['total_corrective_action'] = count($correctiveActionChecklistArray) + count($correctiveActionSubcheckListArray);*/
				
			}
			
			$data['correctiveAction'] = array_merge($correctiveActionChecklistArray, $correctiveActionSubcheckListArray);
				
			$data['correctiveCheck'] = array_merge($correctiveCheckChecklistArray, $correctiveCheckSubcheckListArray);
			
			$data['total_corrective_action'] = count($correctiveActionChecklistArray) + count($correctiveActionSubcheckListArray);
			
			//-----------12-06-2025--------------------
			$data['correctiveNeeded'] = array_merge($correctiveNeddedChecklistArray,$correctiveNeddedSubchecklistArray);
			$data['approvedCompleted'] = array_merge($completedApprChecklistArray,$completedApprSubcheckListArray);
			//------------------------------------------
			
			$data['userdata'] = User::with('get_user_location')->where('id', auth()->user()->id)->first();
			$data['location_id'] = $lid;
			//$data['category_id'] = $catid;
			//$data['task_id'] = $taskid;
			$data['task_id'] = '';
			$data['isactive'] = $active;
			
			return view('inspector.lo-task-status', $data);
		}
	}
	public function get_save_exist_checklist_page(Request $request)
	{
		$current_question_id = Checklist::where('category_id', $request->cat_id)->where('order_no', $request->order_no)->first()->id;
		
		$task_id = $request->task_id;
		$category_id = $request->cat_id;
		$categoryDtls = Category::where('id',$category_id)->first();
		$categoryName = $categoryDtls ? $categoryDtls->name : '';
		$checklistDtls = Checklist::where('id', $current_question_id)->where('category_id', $category_id)->first();
		$order_no = $checklistDtls ? $checklistDtls->order_no : '';
		//$subcategory_id = $request->subcat_id; // 21-05-2025
		$subChklistArr = [];
		$existingFiles = [];
		$existingSubChecklistFiles = [];
		
		/*$checklistdata= Checklist::with('get_subchecklist','get_category','get_subcategory')
		->where('category_id',$category_id)->where('subcategory_id', $subcategory_id)
		->where('status','!=', 2)->first();*/
		
		// fetch record with respect ti checklist
		$nextQuestion = Checklist::with('get_subchecklist','get_category')->where('category_id', $category_id)
			->where('category_id', $category_id)
			//->where('subcategory_id', $subcategory_id) // 21-05-2025
			->where('status', '!=', 2)
			->where('id', $current_question_id)
			->orderBy('id', 'asc')
			->first();
			//echo "<pre>";print_r($nextQuestion);die;
			$nextId = $nextQuestion->id;
			$name = $nextQuestion->name;
			
			if(!empty($nextQuestion->get_subchecklist))
			{
				//$subchecklist = $nextQuestion->get_subchecklist;
				foreach($nextQuestion->get_subchecklist as $subchecklists)
				{
					$subChklistArr[] = [
						'id' => $subchecklists->id,
						'name' => $subchecklists->name
					];
				}
				
				$subcategoryname = '';
				//$subcategoryname = $nextQuestion->get_subcategory->name; //21-05-2025
			}
			
			// fetch data from task_list_checklist
			// -- 21-05-2025 ---
			/*$iffetch  = Task_list_checklists::where('task_list_id', $task_id)->where('task_list_subcategory_id', $subcategory_id)->where('checklist_id', $nextId)->first();*/
			//--------
			
			$iffetch  = Task_list_checklists::where('task_list_id', $task_id)->where('checklist_id', $nextId)->first();
			
			$next_rejected_region = $iffetch ? $iffetch->rejected_region : null;
			$next_approve = $iffetch ? $iffetch->approve : '';
			
			// fetch files 
			$task_list_checklist_id = $iffetch ? $iffetch->id : null;
			//$existingFiles = [];
			if (isset($task_list_checklist_id)) {
				$imageData = Task_list_checklist_rejected_files::where('task_list_checklist_id', $task_list_checklist_id)->get();
				foreach ($imageData as $file) {
					$filename = $file->file;
					$existingFiles[] = [
						'name' => $filename,
						'size' => file_exists(public_path('uploads/reject-files/' . $filename)) ? filesize(public_path('uploads/reject-files/' . $filename)) : 123456, // default if unknown
						'url' => asset('uploads/reject-files/' . $filename),
					];
				}
			}
			
			// fetch data from task_list_subchecklist
			$fetchsubChklistArr = [];
			$ifsubfetch  = Task_list_subchecklists::where('task_list_id', $task_id)
							//->where('task_list_subcategory_id', $subcategory_id) // 21-05-2025
							->where('task_list_checklist_id', $nextId)
							->get();
			if($ifsubfetch->isNotEmpty())
			{
				foreach($ifsubfetch as $subchecklistval)
				{
					$fetchsubChklistArr[] = [
						'subchecklist_id' => $subchecklistval->subchecklist_id,
						'rejected_region' => $subchecklistval->rejected_region ?? '',
						'approve' => $subchecklistval->approve
					];
					
					// fetch files for subchecklist
					if(isset($subchecklistval->id))
					{
						$imageSubChecklistData = Task_list_subchecklist_rejected_files::where('task_list_subchecklist_id', $subchecklistval->id)->get();
						foreach ($imageSubChecklistData as $file) {
							$filename = $file->file;
							$existingSubChecklistFiles[] = [
								'name' => $filename,
								//'subchecklist_id' => $file->task_list_subchecklist_id,
								'subchecklist_id' => $subchecklistval->subchecklist_id,
								'size' => file_exists(public_path('uploads/reject-files/subchecklist/' . $filename)) ? filesize(public_path('uploads/reject-files/subchecklist/' . $filename)) : 123456, // default if unknown
								'url' => asset('uploads/reject-files/subchecklist/' . $filename),
							];
						}
					}
				}
			}
			
			//------- progress bar work ---------------
			$total_checklist = Checklist::where('category_id', $category_id)
								//->where('subcategory_id', $subcategory_id) // 21-05-2025
								->get();
			$countCheklist  = $total_checklist->count();
			$percentage = ceil(100/$countCheklist);
			
			if(!empty($total_checklist))
			{
				$barHtml = '<div class="d-flex justify-content-between mb-3" style="gap: 4px;" id="progress-bar-section">';
				foreach($total_checklist as $val)
				{
						$progressStatus = '';
						$hasTaskChecklist = Task_list_checklists::where('task_list_id', $task_id)
										//->where('task_list_subcategory_id', $subcategory_id) // 21-05-2025
										->where('checklist_id', $val->id)->exists();
						if($hasTaskChecklist)
						{
							$progressStatus = 'completed';
						}
						else 
						{
							$hasTaskSubChecklist = Task_list_subchecklists::where('task_list_id', $task_id)
										//->where('task_list_subcategory_id', $subcategory_id)  // 21-05-2025
										->where('task_list_checklist_id', $val->id)->exists();
							if($hasTaskSubChecklist)
							{
								$progressStatus = 'completed';
							}
						}
					
					$barHtml .= '<div class="step-block '.$progressStatus .'" style="width:{{ $percentage  }}%;" id="progress-status-{{ $val->id }}"></div>';
				}
				$barHtml .= '</div>';
			}
			
			
			
			//-----------------------------------------
			
			return response()->json
			(
				[
					'task_id'=>$task_id,
					'currentid'=> $nextId ?? null,
					'name' => $name ?? null,
					'subchecklist' => $subChklistArr,
					'subcategoryname' => $subcategoryname,
					'order_no' => $order_no,
					'categoryName' => $categoryName,
					'next_rejected_region'=> $next_rejected_region ?? '',
					'next_approve'=>$next_approve,
					'existingNextFiles'=>$existingFiles,
					'fetchsubChklistArr'=>$fetchsubChklistArr,
					'existingSubChecklistFiles'=>$existingSubChecklistFiles,
					'category_id'=>$category_id,
					'subcategory_id'=>'',
					'directEdit' => $request->directEdit,
					//'subcategory_id'=>$subcategory_id,// 21-05-2025
					'barHtml'=>$barHtml
				]
			);
	}
}
