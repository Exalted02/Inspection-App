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
use App\Models\Task_list_corrective_action_details;
use Illuminate\Support\Facades\DB;

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
		if (auth()->user()->user_type == 2 || auth()->user()->user_type == 3)
		{
			return redirect('inspector-dashboard');
		}
		
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
											->where('inspector_id', auth()->user()->id)->orderBy('created_at', 'ASC')
											->get();
			}

		} elseif (auth()->user()->user_type == 2 || auth()->user()->user_type == 3) {
			$hasData = Task_lists::where('location_id', $id)->exists();

			if ($hasData) {
				$data['task_list_data'] = Task_lists::where('location_id', $id)->orderBy('id', 'ASC')->get();
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
		
		// after all categories did submit checklist then redirect to location details 
		$getCats = Task_location_categories::where('task_list_id', $task_id)->pluck('category_id')->toArray();
		
		$presentCategoryIds = Task_list_subcategories::where('task_list_id', $task_id)
			->pluck('task_list_category_id')->unique()->toArray();
		
		$allCategoriesPresent = empty(array_diff($getCats, $presentCategoryIds));
		if($allCategoriesPresent)
		{
			return redirect('location-details/' . $lid);
		}
		//--------
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
			$totalChecklist = Checklist::where('category_id', $category_id)->where('status', '!=', 2)->count();
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
		
		// update Task_list table if whenever checklist start added then status =1
		
		$modelTask = Task_lists::find($task_id);
		$modelTask->status = 1;
		$modelTask->save();
		//-----------
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
				//$status = '';
				$status = [];
				$hasTaskChecklist = Task_list_checklists::where('task_list_id', $task_id)
							//->where('task_list_subcategory_id', $subcategory_id)//21-05-2025
							->where('checklist_id', $chklist->id)->exists();
				if($hasTaskChecklist)
				{
					$status[] = Task_list_checklists::where('task_list_id', $task_id)
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
							//$status = 1;
							$status = [];
							foreach($getstatus as $val)
							{
								if($val->approve == 0)
								{
									$status[] = 0;
								}
								elseif($val->approve == 1)
								{
									$status[] = 1;
								}
								else{
									$status[] = 2;
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
				//$status = '';
				$status = [];
				$hasTaskChecklist = Task_list_checklists::where('task_list_id', $task_id)
							//->where('task_list_subcategory_id', $subcategory_id)//21-05-2025
							->where('checklist_id', $chklist->id)->exists();
				if($hasTaskChecklist)
				{
					$status[] = Task_list_checklists::where('task_list_id', $task_id)
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
							$status = [];
							//$status = 1;
							foreach($getstatus as $val)
							{
								
								if($val->approve == 0)
								{
									$status[] = 0;
								}
								elseif($val->approve == 1)
								{
									$status[] = 1;
								}
								else{
									$status[] = 2;
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
			$url = url('uploads/temp-reject-files/' . $filename);
			
			return response()->json(['success' => true, 'filename' => $filename, 'checklist_id' =>$current_checklist_id, 'subcategory_id' =>$subcategory_id, 'task_id' =>$task_id, 'new'=>1 , 'url'=>$url]);
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
		//echo "<pre>";print_r($request->all());die;
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
			$url = url('uploads/temp-reject-files/' . $filename);
			
			return response()->json(['success' => true, 'filename' => $filename, 'checklist_id' =>$current_checklist_id, 'subchecklist_id' =>$subchecklist_id, 'task_id' =>$task_id, 'new'=>1, 'url'=>$url]);
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
			
			// update task_list table 03-07-2025
			
			$taskLocationCat = Task_location_categories::where('task_list_id', $task_id)->get();
		   foreach($taskLocationCat as $categories)
		   {
			   $getCategotyArr[] = $categories->category_id;
		   }
		   //echo "<pre>";print_r($getCategotyArr);die;
		   $matchedCount = Task_list_subcategories::where('task_list_id',$task_id)->whereIn('task_list_category_id',$getCategotyArr)->distinct('task_list_category_id')->count('task_list_category_id');
		   
		   $ifAllCategoryExists = $matchedCount === count($getCategotyArr);
			
			$taskModel = Task_lists::find($task_id);
			if(!$ifAllCategoryExists)
			{
				$taskModel->status = 1;
			}
			else{
				$taskModel->status = 2;
			}
			$taskModel->save();
			//---
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
			//$time = $request->post('hidden_set_time');
			$time = date('h:i:s');
			
			$datetime  = $date.' '.$time;
			$lo_completed_by = date('Y-m-d h:i:s', strtotime($datetime));
			//echo $lo_completed_by;die;
		}
		else{
			$lo_completed_by = date('Y-m-d h:i:s');
		}
		//echo $lo_completed_by; die;
		
		//----- new add 03-07-2025 =======
		$category_id = null;
		if(!empty($checklist_id) && !empty($subchecklist_id))
		{
			$categoryData = Task_list_subchecklists::where('task_list_id', $task_list_id)->where('task_list_checklist_id', $checklist_id)->where('subchecklist_id', $subchecklist_id)->first();
			$category_id = $categoryData ? $categoryData->category_id : null;
		}
		else if(!empty($checklist_id) && empty($subchecklist_id))
		{
			$categoryData = Task_list_checklists::where('task_list_id', $task_list_id)->where('checklist_id', $checklist_id)->first();
			$category_id = $categoryData ? $categoryData->category_id : null;
		}
		//------------------------
		
		$taskData  = Task_lists::where('id', $task_list_id)->first();
		$inspector_id = $taskData ? $taskData->inspector_id : null;
		$location_id = $taskData ? $taskData->location_id : null;
		//$category_id = $taskData ? $taskData->category_id : null; 22-05-2025
		$los_id = $taskData ? $taskData->los_id : null;
		
		$model = new Task_list_corrective_action();
		$model->task_list_id = $task_list_id;
		$model->category_id = $category_id;
		$model->checklist_id = $checklist_id;
		$model->subchecklist_id = $subchecklist_id;
		$model->lo_id = auth()->user()->id;
		$model->lo_corrective_action_plan = $request->lo_corrective_action_plan ?? '';
		$model->lo_completed_by = $lo_completed_by;
		$model->lo_direct_approve = $request->lo_direct_approve == 'true' ? 1 : 0;
		$model->inspector_id = $inspector_id;
		$model->los_id = $los_id;
		$model->rejected_repeated = 1; // 04-08-2025
		$model->created_at = date('Y-m-d h:i:s'); // 11-08-2025
		$model->save();
		$id = $model->id;
		
		// update the status Task lists table
		//$taskData  = Task_lists::where('id', $task_list_id)->update(['status'=>2]); // 11-07-2025
		
		//---------03-07-2025-----------
		$lo_files = $request->file('lo_file');

		if ($lo_files && is_array($lo_files)) {
			
			// unlink previous file 
			$correctiveFiles = Task_list_corrective_action_file::where('task_list_corrective_actions_id', $id)->where('status',1)->get();
			if($correctiveFiles->isNotEmpty()){
				
				foreach($correctiveFiles as $filemn)
				{
					$f_name = $filemn->file;
					$filePath = public_path('uploads/corrective_action/' . $f_name);
					if (file_exists($filePath)) {
						unlink($filePath);
					}
				}
				
				Task_list_corrective_action_file::where('task_list_corrective_actions_id', $id)->where('status', 1)->delete();
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
				$fileModel->status = 1;
				$fileModel->save();
			}
		}
		//---------------------------
		
		$correctiveActionDtldModel = new Task_list_corrective_action_details();
		$correctiveActionDtldModel->task_list_corrective_action_id = $id;
		$correctiveActionDtldModel->order = 1;
		$correctiveActionDtldModel->lo_corrective_action_plan_final_checks =$request->lo_corrective_action_plan;
		$correctiveActionDtldModel->lo_completed_by = $lo_completed_by;
		$correctiveActionDtldModel->lo_direct_approve = $request->lo_direct_approve == 'true' ? 1 : 0;
		$correctiveActionDtldModel->save();
		
		return response()->json(['location_id'=>$location_id, 'task_id'=>$task_list_id]);
	}
	
	public function add_new_task($lid)
	{
		if (auth()->user()->user_type != 1) {
			return redirect('inspector-dashboard');
		}
		
		$data['task_id']  = '';
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
		//echo "<pre>";print_r($request->all());die;
		
		/*$existingTask = Task_lists::where('location_id', $request->post('location_id'))->where('category_id', $request->post('category_id'))->where('task_title', $request->post('task_title'))->where('status', '!=', 2)
        ->first();*/
		/*$existingStage = Subchecklist::where('checklist_id', $request->post('checklist'))->where('name', $request->post('name'))->where('status', '!=', 2)
        ->when($request->post('id'), function ($query) use ($request) {
            $query->where('id', '!=', $request->post('id'));
        })
        ->first();*/
		
		$existingTask = Task_lists::where('location_id', $request->post('location_id'))->where('category_id', $request->post('category_id'))->where('task_title', $request->post('task_title'))
		->when($request->post('id'), function ($query) use ($request) {
            $query->where('id', '!=', $request->post('id'));
        })
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
		
		$id = $request->id ?? '';
		if(empty($request->id))
		{
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
		}
		else{
			$model = Task_lists::find($id);
			$model->inspector_id	=	auth()->user()->id;
			$model->location_id		=	$request->post('location_id');
			//$model->category_id		=	$request->post('category_id');
			//$model->lo_id			=	;
			$model->los_id			=	$los_id ?? null;
			$model->task_title		=	$request->post('task_title');
			$model->status		=	0;
			$model->created_at	=	$created_at ?? '';
			$model->save();
		}
		
		// add task_location_category
		$location_category = $request->location_category;
		
		if(!empty($location_category))
		{
			Task_location_categories::where('task_list_id', $id)->delete();
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
			
			//-- unlink---
			if($request->hid_task_image)
			{
				$f_name = $request->hid_task_image;
				if($f_name != 'default-task-pic.jpg')
				{
					$filePath = public_path('uploads/task/' . $f_name);
					if (file_exists($filePath)) {
						unlink($filePath);
					}
				}
			}
			//-----------
			
			$updtmodel= Task_lists::find($id);
			$updtmodel->image = $fileName;
			$updtmodel->save();
		}
		/*else{
			$updtmodel= Task_lists::find($id);
			$updtmodel->image = $request->hid_task_image;
			//$updtmodel->image = 'default-task-pic.jpg';
			$updtmodel->save();
		}*/
		
		return response()->json([
			'success' => true
		]);
	}
	
	public function delete_task_image(Request $request)
	{
		$id = $request->task_id;
		$task_image = $request->task_image;
		$model = Task_lists::find($id);
		$model->image = null;
		$model->save();
		
		$f_name = $task_image;
		$filePath = public_path('uploads/task/' . $f_name);
		if (file_exists($filePath)) {
			unlink($filePath);
		}
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
		$first_rejected_reason = $request->first_rejected_reason;
		
		//$id = Task_list_corrective_action::where('task_list_id', $task_list_id)->where('checklist_id', $checklist_id)->where('inspector_id', $inspector_id)->first()->id;
		
		$query = Task_list_corrective_action::where('task_list_id', $task_list_id)->where('checklist_id', $checklist_id);
		
		if (!empty($subchecklist_id)) {
			$query->where('subchecklist_id', $subchecklist_id);
		}
		
		$record = $query->first();
		$id = $record ? $record->id : null;
		
		$model = Task_list_corrective_action::find($id);
		if($inspector_action == 1)
		{
			//echo $inspector_action; die; 
			if(auth()->user()->user_type == 1)
			{
				$model->inspector_action_date = date('Y-m-d h:i:s');
				$model->inspector_action = $inspector_action;
				$model->inspector_id = $user_id;
				$model->approved_status = 1;
			}
			
			if(auth()->user()->user_type == 3)
			{
				$model->los_action_date = date('Y-m-d h:i:s');
				$model->los_action = $inspector_action;
				$model->los_id = $user_id;
				$model->approved_status = 2;
			}
		}
		else if($inspector_action == 2)
		{
			$actionStatus = Task_list_corrective_action::where('id',$id)->first();
			
			//$model->inspector_action_date = date('Y-m-d h:i:s'); // 11-08-2025
			$model->inspector_action = $inspector_action;
			//$model->inspector_id = $user_id;
			$model->ia_los_first_rejected_reason = $first_rejected_reason ?? '';
			//$model->los_action_date = date('Y-m-d h:i:s'); // 11-08-2025
			$model->los_action = $inspector_action;
			
			if(auth()->user()->user_type == 1)
			{
				$model->inspector_action_date = date('Y-m-d h:i:s'); // 11-08-2025
				//-------
				$ins_action_test = Task_list_corrective_action::where('id', $id)->first()->inspector_action;
				if($ins_action_test == 2)
				{
					return response()->json(['message'=>'error']);
				}
				//-----
				
				$model->rejected_status = 1;
			}
			
			if(auth()->user()->user_type == 3)
			{
				$model->los_action_date = date('Y-m-d h:i:s'); // 11-08-2025
				//-------
				$los_action_test = Task_list_corrective_action::where('id', $id)->first()->los_action;
				if($los_action_test == 2)
				{
					return response()->json(['message'=>'error']);
				}
				//-----
				
				$model->rejected_status = 2;
			}
			
			$model->rejected_repeated = 1;
		}
		
		$model->save();
		
		// update the status of Task lists 
		if($inspector_action==1)
		{
			//Task_lists ::where('id',$task_list_id)->update(['status'=>3]);
		}
		
		$result = Task_list_corrective_action::where('task_list_id', $task_list_id)->where('checklist_id', $checklist_id);
		
		if (!empty($subchecklist_id)) {
			$result->where('subchecklist_id', $subchecklist_id);
		}
		
		$actionData = $result->first();
		$ins_action = $actionData ? $actionData->inspector_action : null;
		$los_action = $actionData ? $actionData->los_action : null;
		
		//----- add to table corrective details
		
		$lastRecord = Task_list_corrective_action_details::where('task_list_corrective_action_id', $id)->orderBy('id', 'desc')->first();
		$last_id = $lastRecord ? $lastRecord->id : null;
		if($last_id)
		{
		
			$correctiveActionDtldModel = Task_list_corrective_action_details::find($last_id);
			
			if($inspector_action == 1)
			{
				if(auth()->user()->user_type == 1)
				{
					$correctiveActionDtldModel->inspector_action_date = date('Y-m-d h:i:s');
					
					$correctiveActionDtldModel->approved_status = 1;
				}
				
				if(auth()->user()->user_type == 3)
				{
					$correctiveActionDtldModel->los_action_date = date('Y-m-d h:i:s');
					$correctiveActionDtldModel->approved_status = 2;
				}
			}
			else if($inspector_action == 2)
			{
				//$correctiveActionDtldModel->inspector_action_date = date('Y-m-d h:i:s');  // 11-08-2025
				
				//$model->inspector_id = $user_id;
				$correctiveActionDtldModel->ia_los_rejected_reason = $first_rejected_reason ?? '';
				//$correctiveActionDtldModel->los_action_date = date('Y-m-d h:i:s');// 11-08-2025
				
				
				if(auth()->user()->user_type == 1)
				{
					$correctiveActionDtldModel->inspector_action_date = date('Y-m-d h:i:s'); // 11-08-2025
					$correctiveActionDtldModel->rejected_status = 1;
				}
				
				if(auth()->user()->user_type == 3)
				{
					$correctiveActionDtldModel->los_action_date = date('Y-m-d h:i:s'); // 11-08-2025
					$correctiveActionDtldModel->rejected_status = 2;
				}
				
				$correctiveActionDtldModel->rejected_repeated = 1;
			}
			
			$correctiveActionDtldModel->save();
		}
		
		return response()->json(['message'=>'success', 'ins_action'=>$ins_action, 'los_action'=>$los_action]);
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
		
		if($request->lo_direct_approve == 'false')
		{
			$date = date('Y-m-d', strtotime($request->post('hidden_set_date')));
			//$time = $request->post('hidden_set_time');
			$time = date('h:i:s');
			$datetime  = $date.' '.$time;
			$lo_completed_by = date('Y-m-d h:i:s', strtotime($datetime));
		}
		else{
			$lo_completed_by = date('Y-m-d h:i:s');
		}
		
		if($type == 'checklist')
		{
			$corrective_action_data = Task_list_corrective_action::where('task_list_id', $task_id)->where('checklist_id', $checklist_id)->first();
		}
		else
		{
			$corrective_action_data = Task_list_corrective_action::where('task_list_id', $task_id)->where('checklist_id', $checklist_id)->where('subchecklist_id', $subchecklist_id)->first();
		}
		
		$id = $corrective_action_data ? $corrective_action_data->id : '';
		
		$model = Task_list_corrective_action::find($id);
		$model->lo_corrective_action_plan_second_check = $request->inspector_action == 2 ? $content : null;
		//$model->inspector_action = $request->inspector_action;
		$model->inspector_action = 0;
		//$model->lo_direct_approve = 1; // 30-07-2025
		//$model->los_action = $request->los_action;
		$model->los_action = 0;
		$model->approved_status = 0;
		$model->rejected_status = 0;
		//$model->lo_completed_by = $lo_completed_by; // comment 13-08-2025
		$model->lo_direct_approve = $request->lo_direct_approve == 'true' ? 1 : 0; // add new 13-08-2025
		//$model->rejected_repeated = 0; // add new 29-07-2025
		$model->save();
		
		$status = Task_list_corrective_action_details::where('task_list_corrective_action_id', $id)->orderBy('id', 'desc')
            ->value('order');
			
		$new_status = $status + 1;
		
		$lo_files = $request->file('lo_file');

		if ($lo_files && is_array($lo_files)) {
			
			// unlink previous file 
			/*$correctiveFiles = Task_list_corrective_action_file::where('task_list_corrective_actions_id', $id)->where('status', 2)->get();
			if($correctiveFiles->isNotEmpty()){
				
				foreach($correctiveFiles as $filemn)
				{
					$f_name = $filemn->file;
					$filePath = public_path('uploads/corrective_action/' . $f_name);
					if (file_exists($filePath)) {
						unlink($filePath);
					}
				}
				
				Task_list_corrective_action_file::where('task_list_corrective_actions_id', $id)->where('status', 2)->delete();
			}*/
			
			//-- 22-07-2025---
			/*$status = Task_list_corrective_action_file::where('task_list_corrective_actions_id', $id)->orderBy('id', 'desc')
            ->value('status');*/
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
				$fileModel->status = $new_status;
				$fileModel->save();
			}
		}
		
		// add to the table Task_list_corrective_action_details
		$correctiveActionDtldModel = new Task_list_corrective_action_details();
		$correctiveActionDtldModel->task_list_corrective_action_id = $id;
		$correctiveActionDtldModel->order = $new_status;
		$correctiveActionDtldModel->lo_corrective_action_plan_final_checks = $content;
		$correctiveActionDtldModel->created_at = date('Y-m-d h:i:s');
		
		$correctiveActionDtldModel->lo_completed_by = $lo_completed_by; // add new 13-08-2025
		$correctiveActionDtldModel->lo_direct_approve = $request->lo_direct_approve == 'true' ? 1 : 0; // add new 13-08-2025
		
		$correctiveActionDtldModel->save();
		
		// update the status of Task lists after approve by lo 
		
		//Task_lists ::where('id',$task_id)->update(['status'=>4]);
		

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
	
	//----------
	public function inspector_checklist_second_approve_plan_by_lo($location_id='',$task_id='',$checklist_id='',$type='', $tab='')
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
		
		return view('inspector.inspector-check-reply-approved-plan-by-lo', $data);
	}
	public function inspector_subchecklist_second_approve_plan_by_lo($location_id='',$task_id='',$checklist_id='',$subchecklist_id='',$type='', $tab='')
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
		
		return view('inspector.inspector-check-reply-approved-plan-by-lo', $data);
	}
	//----
	public function submit_inspector_approved(Request $request)
	{
		$task_list_id = $request->task_id;
		$checklist_id = $request->checklist_id;
		$subchecklist_id = $request->subchecklist_id ?? null;
		$inspector_id = auth()->user()->id;
		$inspector_action = $request->inspector_action;
		$los_action = $request->los_action;
		$first_rejected_reason = $request->first_rejected_reason;
		
		/*$id = Task_list_corrective_action::where('task_list_id', $task_list_id)->where('checklist_id', $checklist_id)->where('inspector_id', $inspector_id)->first()->id;*/
		$query = Task_list_corrective_action::where('task_list_id', $task_list_id)->where('checklist_id', $checklist_id);
		
		if(!empty($subchecklist_id))
		{
			$query->where('subchecklist_id', $subchecklist_id);
		}
		
		$id = $query->first()->id;
		
		$model = Task_list_corrective_action::find($id);
		$model->inspector_action_date = date('Y-m-d h:i:s');
		if($inspector_action == 2 || $los_action == 2)
		{
			$model->lo_corrective_action_plan_second_check = null;
			$model->ia_los_first_rejected_reason = $first_rejected_reason ?? '';
			
			if(auth()->user()->user_type == 1)
			{
				//-------
				$ins_action_test = Task_list_corrective_action::where('id', $id)->first()->inspector_action;
				if($ins_action_test == 2)
				{
					return response()->json(['message'=>'error']);
				}
				//-----
				$model->rejected_status = 1;
				$model->approved_status = 2;
			}
			
			if(auth()->user()->user_type == 3)
			{
				//-------
				$los_action_test = Task_list_corrective_action::where('id', $id)->first()->los_action;
				if($los_action_test == 2)
				{
					return response()->json(['message'=>'error']);
				}
				//-----
				
				$model->rejected_status = 2;
				$model->approved_status = 1;
			}
			
			$model->rejected_repeated = 1;
		}
		
		if($inspector_action == 1 || $los_action == 1)
		{
			if(auth()->user()->user_type == 1)
			{
				$model->approved_status = 1;
			}
			
			if(auth()->user()->user_type == 3)
			{
				$model->approved_status = 2;
			}
		}
		
		
		
		if($inspector_action != '')
		{
			$model->inspector_action = $inspector_action;
		}
		
		if($los_action != '')
		{
			$model->los_action = $los_action;
		}
		//$model->inspector_id = $inspector_id;
		
		$model->save();
		
		//----- add to table corrective details
		$lastRecord = Task_list_corrective_action_details::where('task_list_corrective_action_id', $id)->orderBy('id', 'desc')->first();
		$last_id = $lastRecord ? $lastRecord->id : null;
		if($last_id)
		{
			$correctiveActionDtldModel = Task_list_corrective_action_details::find($last_id);
			if($inspector_action == 2 || $los_action == 2)
			{
				
				$correctiveActionDtldModel->ia_los_rejected_reason = $first_rejected_reason ?? '';
				
				if(auth()->user()->user_type == 1)
				{
					$correctiveActionDtldModel->rejected_status = 1;
					//$correctiveActionDtldModel->approved_status = 2;
					$correctiveActionDtldModel->inspector_action_date = date('Y-m-d h:i:s');
					//$correctiveActionDtldModel->los_action_date = date('Y-m-d h:i:s');
				}
				
				if(auth()->user()->user_type == 3)
				{
					$correctiveActionDtldModel->rejected_status = 2;
					//$correctiveActionDtldModel->approved_status = 1;
					$correctiveActionDtldModel->los_action_date = date('Y-m-d h:i:s');
					//$correctiveActionDtldModel->inspector_action_date = date('Y-m-d h:i:s');
				}
				
				$correctiveActionDtldModel->rejected_repeated = 1;
			}
			
			if($inspector_action == 1 || $los_action == 1)
			{
				if(auth()->user()->user_type == 1)
				{
					$correctiveActionDtldModel->approved_status = 1;
					$correctiveActionDtldModel->inspector_action_date = date('Y-m-d h:i:s');
				}
				
				if(auth()->user()->user_type == 3)
				{
					$correctiveActionDtldModel->approved_status = 2;
					$correctiveActionDtldModel->los_action_date = date('Y-m-d h:i:s');
				}
				
			}
			
			$correctiveActionDtldModel->rejected_repeated = 1;
			
			$correctiveActionDtldModel->save();
		}
		
		// update the status of Task lists after final approve by inspector or los 
		//Task_lists ::where('id',$task_list_id)->update(['status'=>5]);
		
		return response()->json(['message'=>'success']);
	}
	public function get_final_edit_page(Request $request)
	{
		$task_id = $request->task_id;
		$category_id = $request->category_id;
		$categoryName = Category::where('id', $category_id)->first()->name;
		
		$checklists = Checklist::where('category_id',$category_id)->where('status','!=', 2)->orderBy('order_no')->get();
									
			foreach ($checklists as $chklist) {
				//$status = '';
				$status = [];
				$hasTaskChecklist = Task_list_checklists::where('task_list_id', $task_id)->where('checklist_id', $chklist->id)->exists();
				if($hasTaskChecklist)
				{
					$status[] = Task_list_checklists::where('task_list_id', $task_id)->where('checklist_id', $chklist->id)->first()->approve;
					
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
							//$status = 1;
							$status = [];
							foreach($getstatus as $val)
							{
								if($val->approve == 0)
								{
									$status[] = 0;
								}
								elseif($val->approve == 1)
								{
									$status[] = 1;
								}
								else{
									$status[] = 2;
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
		
		$inspectorId = auth()->user()->id;

		$offset = 0;
		$limit = config('custom.LOAD_MORE_LIST_SHOW');

		// Common filters
		$taskListIds = Task_lists::where('location_id', $lid)
			->where('inspector_id', auth()->user()->id)
			->pluck('id');
		
		$categoryIds = Task_list_subcategories::whereIn('task_list_id', $taskListIds)
			->pluck('task_list_category_id');
		//echo "<pre>";print_r($categoryIds);die;
		//-----05-08-2025 if submit checklist then submit task data show-----
		$submit_task_id = Task_list_subcategories::whereIn('task_list_category_id', $categoryIds)->pluck('task_list_id')->toArray();
		//echo "<pre>";print_r($taskListIds);die;
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

			//echo "<pre>";print_r($correctiveChecklistIds);die;
		//------------subchecklist----------------------
		
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
			
			//echo "<pre>";print_r($correctiveSubChecklistIds);die;
			//echo "<pre>";print_r($submit_task_id);die;
			
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
			//->orderByDesc('updated_at')
			->orderBy('updated_at', 'asc')
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
		
		/*usort($correctiveNeddedArray, function ($a, $b) {
			return strtotime($b['updated_at']) <=> strtotime($a['updated_at']);
		});*/
		//echo "<pre>";print_r($correctiveneeded);die;
		//echo "<pre>";print_r($correctiveNeddedArray);die;
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
			->orderBy('updated_at', 'asc')
			->offset($offset)
			->limit($limit)
			->get();
			//->whereNotNull('checklist_id')
			//->pluck('checklist_id')->toArray();
		//echo "<pre>";print_r($correctiveActionData);die;
		foreach($correctiveActionData as $action)
		{
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
				'updated_at' => $action->updated_at,
				'image' => $image,
			];
			
		}
			
		//echo '<pre>';print_r($correctiveActionArray);die;
		usort($correctiveActionArray, function ($a, $b) {
			return strtotime($b['updated_at']) <=> strtotime($a['updated_at']);
		});
		
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
			->orderBy('updated_at', 'asc')
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
				'updated_at' => $plan->updated_at,
				'image' => $image,
			];
			
		}
		
		usort($correctivePlanArray, function ($a, $b) {
			return strtotime($b['updated_at']) <=> strtotime($a['updated_at']);
		});
		//echo '<pre>';print_r($correctivePlanArray);die;
		
		//============= corrective completed approved -------------
		//correctiveChecklistIds
		$approvedCompletedArray = [];
		// Checklist IDs with existing corrective needed
		$correctiveChecklistApprIds = Task_list_corrective_action::whereIn('task_list_id', $taskListIds)
			->where(function ($q) {
				$q->where(function ($q) {
					$q->where('inspector_action', 1)->where('los_action', 1);
				});
			})
			->whereNotNull('checklist_id')
			->pluck('checklist_id')->toArray();
			
			//print_r($correctiveChecklistApprIds);die;
		//------------------------------------
		//echo "<pre>"; print_r($submit_task_id);die;
		
			$existingCorrectiveChecklistApprIds = DB::table('task_list_corrective_actions')
			    ->whereIn('task_list_id', $taskListIds)
				->whereNotNull('checklist_id')
				->pluck('checklist_id')->toArray();

			$checklistApprIds = DB::table('task_list_checklists')
				->where('approve', 1)
				->whereIn('task_list_id', $taskListIds)
				//->whereNotIn('checklist_id', $existingCorrectiveChecklistApprIds)
				->pluck('checklist_id')
				->toArray();
				
		//print_r($checklistApprIds);die;
		$correctiveApprChecklistIds = array_merge($correctiveChecklistApprIds,$checklistApprIds);
		//print_r($correctiveApprChecklistIds);die;
		//------------------------------------

		$correctiveSubChecklistApprIds = Task_list_corrective_action::whereIn('task_list_id', $taskListIds)
			->where(function ($q) {
				$q->where(function ($q) {
					$q->where('inspector_action', 1)->where('los_action', 1);
				});
			})
			->whereNotNull('subchecklist_id')
			->pluck('subchecklist_id')->toArray();
			
		//print_r($correctiveSubChecklistApprIds);die;
			
			$existingCorrectiveSubChecklistIds = DB::table('task_list_corrective_actions')
			    ->whereIn('task_list_id', $taskListIds)
				->whereNotNull('subchecklist_id')
				->pluck('subchecklist_id')->toArray();

			$subchecklistApprIds = DB::table('task_list_subchecklists')
				->where('approve', 1)
				->whereIn('task_list_id', $taskListIds)
				//->whereNotIn('subchecklist_id', $existingCorrectiveSubChecklistIds)
				->pluck('subchecklist_id')
				->toArray();
			
			//print_r($subchecklistApprIds);die;
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
					'created_at',
					'updated_at',
					DB::raw('NULL as subchecklist_id'),
					DB::raw('NULL as task_list_checklist_id')
				)
				->from('task_list_checklists')
				->whereIn('task_list_id', $taskListIds)
				//->whereIn('task_list_id', $submit_task_id)
				->whereIn('category_id', $categoryIds)
				->whereIn('approve', [0,1])
				->whereIn('checklist_id', $correctiveApprChecklistIds)
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
					->whereIn('task_list_id', $taskListIds)
					//->whereIn('task_list_id', $submit_task_id)
					->whereIn('category_id', $categoryIds)
					->whereIn('approve', [0,1])
					->whereIn('subchecklist_id', $correctiveApprSubChecklistIds)
			);
		}, 'combined')
		 ->orderBy('updated_at', 'asc')
		->offset($offset)
		->limit($limit)
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
					'created_at',
					'updated_at',
					DB::raw('NULL as subchecklist_id'),
					DB::raw('NULL as task_list_checklist_id')
				)
				->from('task_list_checklists')
				->whereIn('task_list_id', $taskListIds)
				->whereIn('category_id', $categoryIds)
				//->whereIn('task_list_id', $submit_task_id)
				->whereIn('approve', [0,1])
				->whereIn('checklist_id', $correctiveApprChecklistIds)
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
					->whereIn('task_list_id', $taskListIds)
					->whereIn('category_id', $categoryIds)
					//->whereIn('task_list_id', $submit_task_id)
					->whereIn('approve', [0,1])
					->whereIn('subchecklist_id', $correctiveApprSubChecklistIds)
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
		//echo "<pre>";print_r($approvedCompletedArray);die;
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
		
		
		$data['location_id'] = $lid;
		$data['task_id'] = '';
		$data['task_name'] = '';
		$data['isactive'] = $active;
		$data['userdata'] = User::with('get_user_location')->where('id', auth()->user()->id)->first();
		
		$data['moreloadneeded'] = config('custom.LOAD_MORE_LIST_SHOW');
		$data['moreloadaction'] = config('custom.LOAD_MORE_LIST_SHOW');
		$data['moreloadplan'] = config('custom.LOAD_MORE_LIST_SHOW');
		$data['moreloadappr'] = config('custom.LOAD_MORE_LIST_SHOW');
		
		return view('inspector.inspector-filter', $data);
	}
	
	public function inspector_filter_old($lid='', $active='')
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
		$categoriesArr = [];
		// -- if inspector login 
		$data['location_id'] = $lid;
		
		$data['task_id'] = '';
		$data['task_name'] = '';
		$data['isactive'] = $active;
		
		// for corrective checked work 
		$correctiveActionChecklistArray = [];
		//$taskData = Task_lists::where('location_id', $lid)->where('id', $task_id)->get();
		
		//-- 23-06-2025--
		$taskData = Task_lists::where('inspector_id', auth()->user()->id)->where('location_id', $lid)->get();
		//------
		//$task_cat = [1, 2];
		if($taskData->isNotEmpty())
		{
			foreach($taskData as $val)
			{
				$ifTaskRxists = Task_list_subcategories::where('task_list_id', $val->id)->exists();
				if($ifTaskRxists)
				{		
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
					
					//----------------------12-05-2025----------------------------
					$categoriesChecklistArr = [];
					$categoriesChecklistArr = Task_list_subcategories::where('task_list_id', $val->id)->pluck('task_list_category_id')->toArray();
					// checklist and  respective files approve= 0 or 1 
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
												'updated_at'=>$task_list_subchecklist_corrective_needed->updated_at->format('Y-m-d H:i:s'),
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
										'updated_at'=>$subtask->updated_at->format('Y-m-d H:i:s'),
									];
								
							}
						}
						
					}
				}
					
			} // task array end 
		}
		
		//-----------12-06-2025--------------------
		$correctiveNeeded = array_merge($correctiveNeddedChecklistArray,$correctiveNeddedSubchecklistArray);
		//echo "<pre>";print_r($correctiveNeeded);die;
		$data['correctiveNeeded'] = $correctiveNeeded; // for count tab
		$data['approvedCompleted'] = array_merge($completedApprChecklistArray,$completedApprSubcheckListArray);
		//echo "<pre>";print_r($completedApprChecklistArray);die;
		//------------------------------------------
		$correctiveNeddedArray = [];
		foreach($correctiveNeeded as $needed)
		{
			if(($needed['inspector_action']=='' && $needed['los_action']=='') || ($needed['inspector_action']== 2 && $needed['los_action']==2))
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
		
		//echo "<pre>";print_r($correctiveNeeded);
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
			return strtotime($b['updated_at']) <=> strtotime($a['updated_at']);
		});

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
							'updated_at' => $appr['updated_at'],
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
							'updated_at' => $appr['updated_at'],
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
							'updated_at' => $appr['updated_at'],
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
							'updated_at' => $appr['updated_at'],
						];
					}
					
				}
			}
		}
		//echo "<pre>";print_r($approvedCompletedArray);die;
		$data['userdata'] = User::with('get_user_location')->where('id', auth()->user()->id)->first();
		$data['approvedCompletedArray'] = array_slice($approvedCompletedArray, 0, config('custom.LOAD_MORE_LIST_SHOW'));
		$data['moreloadappr'] = config('custom.LOAD_MORE_LIST_SHOW');
		//=====================================================
		
		return view('inspector.inspector-filter', $data);
    }
	public function los_task_status($lid='',$active='')
    {
		if (auth()->user()->user_type == 2) {
			return redirect('inspector-dashboard');
		}
		
		$inspectorId = auth()->user()->id;

		$offset = 0;
		$limit = config('custom.LOAD_MORE_LIST_SHOW');

		// Common filters
		/*$taskListIds = Task_lists::where('location_id', $lid)
			->where('inspector_id', auth()->user()->id)
			->pluck('id');*/
		
		$taskListIds = Task_lists::where('location_id', $lid)->pluck('id');

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
			//->orderByDesc('updated_at')
			->orderBy('updated_at', 'asc')
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
			->orderBy('updated_at', 'asc')
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
				'updated_at' => $action->updated_at,
				'image' => $image,
			];
			
		}
		
		usort($correctiveActionArray, function ($a, $b) {
			return strtotime($b['updated_at']) <=> strtotime($a['updated_at']);
		});
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
			->orderBy('updated_at', 'asc')
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
				'updated_at' => $plan->updated_at,
				'image' => $image,
			];
			
		}
		
		usort($correctivePlanArray, function ($a, $b) {
			return strtotime($b['updated_at']) <=> strtotime($a['updated_at']);
		});
		//echo '<pre>';print_r($correctivePlanArray);die;
		
		//============= corrective completed approved -------------
		//correctiveChecklistIds
		$approvedCompletedArray = [];
		
		
		// get the task_id from subcategory tables
		$subcategoriesTaskIds = Task_list_subcategories::whereIn('task_list_category_id', $categoryIds)
			->pluck('task_list_id');

		$matchingTaskListIds = array_values(array_intersect($taskListIds->toArray(),  $subcategoriesTaskIds->toArray()));
		//echo "<pre>";print_r($matchingTaskListIds);die;
		
		// Checklist IDs with existing corrective needed
		// old
		/*$correctiveChecklistApprIds = Task_list_corrective_action::whereIn('task_list_id', $matchingTaskListIds)
			->where(function ($q) {
				$q->where(function ($q) {
					$q->where('inspector_action', 1)->where('los_action', 1);
				});
			})
			->whereNotNull('checklist_id')
			->pluck('checklist_id')->toArray();*/
			
			// new
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
			
			// old 
			/*$checklistApprIds = DB::table('task_list_checklists')
				->where('approve', 1)
				->whereIn('task_list_id', $matchingTaskListIds)
				->whereIn('category_id', $categoryIds)
				//->whereNotIn('checklist_id', $existingCorrectiveChecklistApprIds)
				->pluck('checklist_id')
				->toArray();*/
			
			// new 
			
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
		// old
		/*$correctiveSubChecklistApprIds = Task_list_corrective_action::whereIn('task_list_id', $matchingTaskListIds)
			->where(function ($q) {
				$q->where(function ($q) {
					$q->where('inspector_action', 1)->where('los_action', 1);
				});
			})
			->whereNotNull('subchecklist_id')
			->pluck('subchecklist_id')->toArray();*/
		
		// new
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
			
			// old 
			/*$subchecklistApprIds = DB::table('task_list_subchecklists')
				->where('approve', 1)
				->whereIn('task_list_id', $matchingTaskListIds)
				->whereIn('category_id', $categoryIds)
				//->whereNotIn('subchecklist_id', $existingCorrectiveSubChecklistIds)
				->pluck('subchecklist_id')
				->toArray();*/
			
            // new 			
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
		
		//echo $correctiveApprovedCount; die;
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
		//echo "<pre>";print_r($approvedCompletedArray);die;
		
		
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
		
		
		$data['location_id'] = $lid;
		$data['task_id'] = '';
		$data['task_name'] = '';
		$data['isactive'] = $active;
		$data['userdata'] = User::with('get_user_location')->where('id', auth()->user()->id)->first();
		
		$data['moreloadneeded'] = config('custom.LOAD_MORE_LIST_SHOW');
		$data['moreloadaction'] = config('custom.LOAD_MORE_LIST_SHOW');
		$data['moreloadplan'] = config('custom.LOAD_MORE_LIST_SHOW');
		$data['moreloadappr'] = config('custom.LOAD_MORE_LIST_SHOW');
		
		return view('inspector.los-task-status', $data);
	}
	public function los_task_status_old($lid='',$active='')
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
		$categoriesArr = [];
		
		$taskData = Task_lists::where('location_id', $lid)->get();
		if($taskData->isNotEmpty())
		{
			foreach($taskData as $val)
			{
				$ifTaskRxists = Task_list_subcategories::where('task_list_id', $val->id)->exists();
				if($ifTaskRxists)
				{
					//$correctiveActions = Task_list_corrective_action::where('task_list_id', $val->id)->where('inspector_id', auth()->user()->id)->get();
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
					
					//----------------------12-05-2025----------------------------
					// checklist and  respective files approve=1 
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
												'created_at' => $task->created_at->format('Y-m-d H:i:s'),
												'updated_at' => $task->updated_at->format('Y-m-d H:i:s'),
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
										'created_at' => $task->created_at->format('Y-m-d H:i:s'),
										'updated_at' => $task->updated_at->format('Y-m-d H:i:s'),
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
												'created_at' => $subtask->created_at->format('Y-m-d H:i:s'),
												'updated_at' => $subtask->updated_at->format('Y-m-d H:i:s'),
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
											'created_at' => $subtask->created_at->format('Y-m-d H:i:s'),
											'updated_at' => $subtask->updated_at->format('Y-m-d H:i:s'),
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
		
		//-----------12-06-2025--------------------
		$data['correctiveNeeded'] = array_merge($correctiveNeddedChecklistArray,$correctiveNeddedSubchecklistArray);
		$data['approvedCompleted'] = array_merge($completedApprChecklistArray,$completedApprSubcheckListArray);
		//------------------------------------------
		//echo "<pre>";print_r($correctiveActionChecklistArray);die;
		$data['correctiveAction'] = $correctiveActionChecklistArray;
		
		//-----------------------
		$correctiveNeeded = array_merge($correctiveNeddedChecklistArray,$correctiveNeddedSubchecklistArray);
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
						'created_at'=> $needed['created_at'],
						'updated_at'=> $needed['updated_at'],
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
						'created_at'=> $needed['created_at'],
						'updated_at'=> $needed['updated_at'],
					];
				}
			}
		}
		
		usort($correctiveNeddedArray, function ($a, $b) {
			return strtotime($b['created_at']) <=> strtotime($a['created_at']);
		});
		//echo "<pre>";print_r($correctiveNeddedArray);die;
		
		$data['correctiveNeddedArray'] = array_slice($correctiveNeddedArray, 0, config('custom.LOAD_MORE_LIST_SHOW'));
		$data['userdata'] = User::with('get_user_location')->where('id', auth()->user()->id)->first();
		
		//=======================
		
		$data['moreloadneeded'] = config('custom.LOAD_MORE_LIST_SHOW');
		$data['moreloadaction'] = config('custom.LOAD_MORE_LIST_SHOW');
		$data['moreloadplan'] = config('custom.LOAD_MORE_LIST_SHOW');
		$data['moreloadappr'] = config('custom.LOAD_MORE_LIST_SHOW');
		//-----
		return view('inspector.los-task-status', $data);
    }
	public function lo_task_status($lid='', $active='')
	{
		if (auth()->user()->user_type == 1 || auth()->user()->user_type == 3) {
			return redirect('inspector-dashboard');
		}
		
		$inspectorId = auth()->user()->id;

		$offset = 0;
		$limit = config('custom.LOAD_MORE_LIST_SHOW');

		// Common filters
		/*$taskListIds = Task_lists::where('location_id', $lid)
			->where('inspector_id', auth()->user()->id)
			->pluck('id');*/
		
		$taskListIds = Task_lists::where('location_id', $lid)->pluck('id');

		$categoryIds = Task_list_subcategories::whereIn('task_list_id', $taskListIds)
			->pluck('task_list_category_id');
			
		//-----05-08-2025 if submit checklist then submit task data show-----
		$submit_task_id = Task_list_subcategories::whereIn('task_list_category_id', $categoryIds)->pluck('task_list_id')->toArray();

		// Checklist IDs with existing corrective needed
		
		//----- 04-08-2025 --
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
		//echo "<pre>";print_r($correctiveSubChecklistIds);die;
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

			$query->fromSub($baseQuery->unionAll($unionQuery), 'combined');
		}, 'combined')
			//->orderByDesc('updated_at')
			->orderBy('updated_at', 'asc')
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
			->orderBy('updated_at', 'asc')
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
				'updated_at' => $action->updated_at,
				'image' => $image,
			];
			
		}
		
		
		usort($correctiveActionArray, function ($a, $b) {
			return strtotime($b['updated_at']) <=> strtotime($a['updated_at']);
		});
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
			->orderBy('updated_at', 'asc')
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
				'updated_at' => $plan->updated_at,
				'image' => $image,
			];
			
		}
		
		usort($correctivePlanArray, function ($a, $b) {
			return strtotime($b['updated_at']) <=> strtotime($a['updated_at']);
		});
		//echo '<pre>';print_r($correctivePlanArray);die;
		
		//============= corrective completed approved -------------
		//correctiveChecklistIds
		$approvedCompletedArray = [];
		
		// get the task_id from subcategory tables
		$subcategoriesTaskIds = Task_list_subcategories::whereIn('task_list_category_id', $categoryIds)
			->pluck('task_list_id');

		$matchingTaskListIds = array_values(array_intersect($taskListIds->toArray(),  $subcategoriesTaskIds->toArray()));
		
		// Checklist IDs with existing corrective needed
		/*$correctiveChecklistApprIds = Task_list_corrective_action::whereIn('task_list_id', $matchingTaskListIds)
			->where(function ($q) {
				$q->where(function ($q) {
					$q->where('inspector_action', 1)->where('los_action', 1);
				});
			})
			->whereNotNull('checklist_id')
			->pluck('checklist_id')->toArray();*/
			
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
		
			$existingCorrectiveChecklistApprIds = DB::table('task_list_corrective_actions')
			    ->whereIn('task_list_id', $taskListIds)
				->whereNotNull('checklist_id')
				->pluck('checklist_id')->toArray();

			/*$checklistApprIds = DB::table('task_list_checklists')
				->where('approve', 1)
				->whereIn('task_list_id', $matchingTaskListIds)
				//->whereNotIn('checklist_id', $existingCorrectiveChecklistApprIds)
				->pluck('checklist_id')
				->toArray();*/
			
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

		/*$correctiveSubChecklistApprIds = Task_list_corrective_action::whereIn('task_list_id', $matchingTaskListIds)
			->where(function ($q) {
				$q->where(function ($q) {
					$q->where('inspector_action', 1)->where('los_action', 1);
				});
			})
			->whereNotNull('subchecklist_id')
			->pluck('subchecklist_id')->toArray();*/
		
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
			
		//print_r($correctiveSubChecklist_Ids);die;
			
			$existingCorrectiveSubChecklistIds = DB::table('task_list_corrective_actions')
			    ->whereIn('task_list_id', $taskListIds)
				->whereNotNull('subchecklist_id')
				->pluck('subchecklist_id')->toArray();

			/*$subchecklistApprIds = DB::table('task_list_subchecklists')
				->where('approve', 1)
				->whereIn('task_list_id', $matchingTaskListIds)
				//->whereNotIn('subchecklist_id', $existingCorrectiveSubChecklistIds)
				->pluck('subchecklist_id')
				->toArray();*/
			
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
		$data['correctiveNeededArray'] = $correctiveNeddedArray;
		$data['countNedded'] = $correctiveNeededCount;
		$data['correctiveActionArray'] = $correctiveActionArray;
		$data['countAction'] = $correctiveActionCount;
		$data['correctivePlanArray'] = $correctivePlanArray;
		$data['countPlan'] = $correctivePlanCount;
		
		$data['approvedCompletedArray'] = $approvedCompletedArray;
		$data['countCompleted'] = $correctiveApprovedCount;
		//$data['countCompleted'] = count($approvedCompletedArray);
		
		$data['correctiveAction'] = [];
		
		
		$data['location_id'] = $lid;
		$data['task_id'] = '';
		$data['task_name'] = '';
		$data['isactive'] = $active;
		$data['userdata'] = User::with('get_user_location')->where('id', auth()->user()->id)->first();
		
		$data['moreloadneeded'] = config('custom.LOAD_MORE_LIST_SHOW');
		$data['moreloadaction'] = config('custom.LOAD_MORE_LIST_SHOW');
		$data['moreloadplan'] = config('custom.LOAD_MORE_LIST_SHOW');
		$data['moreloadappr'] = config('custom.LOAD_MORE_LIST_SHOW');
		return view('inspector.lo-task-status', $data);
		
	}
	
	//public function lo_task_status($lid='',$taskid='', $active='')
	public function lo_task_status_old($lid='', $active='')
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
			$taskData = Task_lists::where('location_id', $lid)->get();
			
			$categoriesChecklistArr = [];
			
			if($taskData->isNotEmpty())
			{
				foreach($taskData as $val)
				{
					$ifTaskRxists = Task_list_subcategories::where('task_list_id', $val->id)->exists();
					if($ifTaskRxists)
					{
						// checklist and  respective files approve=0 
						
						
					    $categoriesChecklistArr = Task_list_subcategories::where('task_list_id', $val->id)->pluck('task_list_category_id')->toArray();
						
						$taskChklist = Task_list_checklists::where('task_list_id', $val->id)->whereIn('category_id', $categoriesChecklistArr)->where('approve', 0)->get();
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
						$taskSubChklist = Task_list_subchecklists::where('task_list_id', $val->id)->whereIn('category_id', $categoriesChecklistArr)->where('approve', 0)->get();
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
												'created_at' => $task->created_at->format('Y-m-d H:i:s'),
												'updated_at' => $task->updated_at->format('Y-m-d H:i:s'),
												'inspector_action' => '',
												'los_action' => '',
												'rejected_status' => '',
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
												'rejected_status'=> $task_list_checklist_corrective_needed->rejected_status,
												'created_at' => $task->created_at->format('Y-m-d H:i:s'),
												'updated_at' => $task->updated_at->format('Y-m-d H:i:s'),
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
													'inspector_action'=> 1,
													'los_action'=> 1,
													'updated_at'=> $task->updated_at->format('Y-m-d H:i:s'),
												];
								}
							}
						}
						
						// subchecklist and respective files
						$taskSubChklist = Task_list_subchecklists::where('task_list_id', $val->id)->whereIn('category_id', $categoriesChecklistArr)->get();
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
													'created_at' => $subtask->created_at->format('Y-m-d H:i:s'),
													'updated_at' => $subtask->updated_at->format('Y-m-d H:i:s'),
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
												'rejected_status'=> $task_list_subchecklist_corrective_needed->rejected_status,
												'created_at' => $subtask->created_at->format('Y-m-d H:i:s'),
												'updated_at' => $subtask->updated_at->format('Y-m-d H:i:s'),
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
				}
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
			
			$data['moreloadneeded'] = config('custom.LOAD_MORE_LIST_SHOW');
			$data['moreloadaction'] = config('custom.LOAD_MORE_LIST_SHOW');
			$data['moreloadplan'] = config('custom.LOAD_MORE_LIST_SHOW');
			$data['moreloadappr'] = config('custom.LOAD_MORE_LIST_SHOW');
			
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
	public function save_exist_question(Request $request)
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
		
		return response()->json(['message'=> 'success']);
	}
	public function ins_load_more_needed_data(Request $request)
	{
		$location_id = $request->location_id;
		
		$data = array();
		$interval = config('custom.LOAD_MORE_INTERVAL');
		$lower = empty($request->moreload) ? 0 : $request->moreload;
		$upper = empty($request->moreload) ? config('custom.LOAD_MORE_LIST_SHOW') : config('custom.LOAD_MORE_INTERVAL');
		//-----------------------------------------------
		$taskListIds = Task_lists::where('location_id', $location_id)
			->where('inspector_id', auth()->user()->id)
			->pluck('id');

		$categoryIds = Task_list_subcategories::whereIn('task_list_id', $taskListIds)
			->pluck('task_list_category_id');
			
		//-----05-08-2025 if submit checklist then submit task data show-----
		$submit_task_id = Task_list_subcategories::whereIn('task_list_category_id', $categoryIds)->pluck('task_list_id')->toArray();

		// Checklist IDs with existing corrective needed
		//-----------04-08-2025-----------------------
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
		//echo "<pre>";print_r($correctiveChecklistIds);die;
		//------------subchecklist----------------------
		
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
			
			//echo "<pre>";print_r($excludedSubChecklistPairs);die;
			
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
			
		//echo "<pre>";print_r($correctiveSubChecklistIds);die;
		
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
		//echo "<pre>";print_r($correctiveNeddedArray);die;
		
		//$totalRecord = $correctiveNeddedArray;
		//echo count($totalRecord);
		//echo "<pre>";print_r($totalRecord);die;
		//$correctiveNeddedArray = array_slice($correctiveNeddedArray, $lower, $upper);
		//echo "<pre>";print_r($correctiveNeddedArray);die;
		$data['location_id'] = $location_id;
		$data['mode'] = 'corrective_needed';
		$data['correctiveNeddedArray'] = $correctiveNeddedArray;
		$html = view('inspector.loadmore.ins-filter-load-more-data', $data)->render();
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
	public function ins_load_more_needed_data_old(Request $request)
	{
		$location_id = $request->location_id;
		
		$data = array();
		$interval = config('custom.LOAD_MORE_INTERVAL');
		$lower = empty($request->moreload) ? 0 : $request->moreload;
		$upper = empty($request->moreload) ? config('custom.LOAD_MORE_LIST_SHOW') : config('custom.LOAD_MORE_INTERVAL');
		
		//-----------------------------------------------
		$correctiveNeddedChecklistArray = [];
		$correctiveNeddedSubchecklistArray = [];
		$taskData = Task_lists::where('inspector_id', auth()->user()->id)->where('location_id', $location_id)->get();
		if($taskData->isNotEmpty())
		{
			foreach($taskData as $val)
			{
				// checklist and respective files
				$taskChklist = Task_list_checklists::where('task_list_id', $val->id)->get();
				if($taskChklist->isNotEmpty())
				{
					foreach($taskChklist as $task)
					{

						$task_list_checklist_corrective_needed = Task_list_corrective_action::where('task_list_id', $val->id)->where('checklist_id', $task->checklist_id)->first();
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
							}
						}


					}	
				}

				// subchecklist and respective files
				$taskSubChklist = Task_list_subchecklists::where('task_list_id', $val->id)->get();

				if($taskSubChklist->isNotEmpty())
				{
					foreach($taskSubChklist as $subtask)
					{
						$task_list_subchecklist_corrective_needed = Task_list_corrective_action::where('task_list_id', $val->id)->where('checklist_id', $subtask->task_list_checklist_id)->where('subchecklist_id', $subtask->subchecklist_id)->first();
											
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
							}
						}
					
					}

				}
			}
		}

		$correctiveNeeded = array_merge($correctiveNeddedChecklistArray,$correctiveNeddedSubchecklistArray);
		$correctiveNeddedArray = [];
		foreach($correctiveNeeded as $needed)
		{
			if(($needed['inspector_action']=='' && $needed['los_action']=='') || ($needed['inspector_action']== 2 && $needed['los_action']==2))
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
		
		$totalRecord = $correctiveNeddedArray;
		//echo "<pre>";print_r($totalRecord);die;
		$correctiveNeddedArray = array_slice($correctiveNeddedArray, $lower, $upper);
		//echo "<pre>";print_r($correctiveNeddedArray);die;
		$data['location_id'] = $location_id;
		$data['mode'] = 'corrective_needed';
		$data['correctiveNeddedArray'] = $correctiveNeddedArray;
		$html = view('inspector.loadmore.ins-filter-load-more-data', $data)->render();
		//---------------------------------------------
		
		$count  = $request->moreload =='' ? config('custom.LOAD_MORE_LIST_SHOW') : $request->moreload + $interval;
		//echo count($totalRecord) .' '.$count; die;
		$remain = count($totalRecord) - $count;
		
		return response()->json(['location_id'=> $location_id, 'html'=> $html, 'loadmore'=> $lower+$upper, 'remain'=> $remain]);
	}
	public function ins_load_more_action_data(Request $request)
	{
		$location_id = $request->location_id;
		
		$data = array();
		$interval = config('custom.LOAD_MORE_INTERVAL');
		$lower = empty($request->moreload) ? 0 : $request->moreload;
		$upper = empty($request->moreload) ? config('custom.LOAD_MORE_LIST_SHOW') : config('custom.LOAD_MORE_INTERVAL');
		
		//----------------------------------------
		$taskListIds = Task_lists::where('location_id', $location_id)
			->where('inspector_id', auth()->user()->id)
			->pluck('id');

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
			
		//$totalRecord = $correctiveActionArray;
		//$correctiveActionArray = array_slice($correctiveActionArray, $lower, $upper);
		//echo "<pre>";print_r($correctiveActionArray);
		$data['location_id'] = $location_id;
		$data['mode'] = 'corrective_action';
		$data['correctiveActionArray'] = $correctiveActionArray;
		$html = view('inspector.loadmore.ins-filter-load-more-data', $data)->render();
		//----------------
		$count  = $request->moreload =='' ? config('custom.LOAD_MORE_LIST_SHOW') : $request->moreload + $interval;
		$remain = $correctiveActionCount - $count;
		
		return response()->json(['location_id'=> $location_id, 'html'=> $html, 'loadmore'=> $lower+$upper, 'remain'=> $remain]);
			
			
	}
	public function ins_load_more_action_data_old(Request $request)
	{
		$location_id = $request->location_id;
		
		$data = array();
		$interval = config('custom.LOAD_MORE_INTERVAL');
		$lower = empty($request->moreload) ? 0 : $request->moreload;
		$upper = empty($request->moreload) ? config('custom.LOAD_MORE_LIST_SHOW') : config('custom.LOAD_MORE_INTERVAL');
		//---------------------------------------------------
		$correctiveActionChecklistArray = [];
		$correctiveActionArray = [];

		$taskData = Task_lists::where('inspector_id', auth()->user()->id)->where('location_id', $location_id)->get();

		if($taskData->isNotEmpty())
		{
			foreach($taskData as $val)
			{
				$ifTaskRxists = Task_list_subcategories::where('task_list_id', $val->id)->exists();
				if($ifTaskRxists)
				{
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
				
				
				}
			}
		}
		//echo "<pre>";print_r($correctiveActionChecklistArray); die;
		foreach($correctiveActionChecklistArray as $correctiveAction)
		{
			if($correctiveAction['lo_direct_approve'] == 1 && ($correctiveAction['inspector_action'] == 0 || $correctiveAction['los_action'] == 0))
			{
				$correctiveActionArray[] = [
					'type' => $correctiveAction['type'],
					'task_id' => $correctiveAction['task_id'],
					'checklist_id' => $correctiveAction['checklist_id'],
					'subchecklist_id' => $correctiveAction['subchecklist_id'],
					'rejected_region' => $correctiveAction['rejected_region'],
					'inspector_action' => $correctiveAction['inspector_action'],
					'los_action' => $correctiveAction['los_action'],
					'second_checked' => $correctiveAction['second_checked'],
					'lo_direct_approve' => $correctiveAction['lo_direct_approve'],
					'image' => $correctiveAction['image'],
				
				];
				
			}
		}
		
		$totalRecord = $correctiveActionArray;
		$correctiveActionArray = array_slice($correctiveActionArray, $lower, $upper);
		//echo "<pre>";print_r($correctiveActionArray);
		$data['location_id'] = $location_id;
		$data['mode'] = 'corrective_action';
		$data['correctiveActionArray'] = $correctiveActionArray;
		$html = view('inspector.loadmore.ins-filter-load-more-data', $data)->render();
		//----------------
		$count  = $request->moreload =='' ? config('custom.LOAD_MORE_LIST_SHOW') : $request->moreload + $interval;
		$remain = count($totalRecord) - $count;
		
		return response()->json(['location_id'=> $location_id, 'html'=> $html, 'loadmore'=> $lower+$upper, 'remain'=> $remain]);
	}
	public function ins_load_more_plan_data(Request $request)
	{
		$location_id = $request->location_id;
		
		$data = array();
		$interval = config('custom.LOAD_MORE_INTERVAL');
		$lower = empty($request->moreload) ? 0 : $request->moreload;
		$upper = empty($request->moreload) ? config('custom.LOAD_MORE_LIST_SHOW') : config('custom.LOAD_MORE_INTERVAL');
		//---------------------------------------------------
		$taskListIds = Task_lists::where('location_id', $location_id)
			->where('inspector_id', auth()->user()->id)
			->pluck('id');

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
		$html = view('inspector.loadmore.ins-filter-load-more-data', $data)->render();
		//----------------
		$count  = $request->moreload =='' ? config('custom.LOAD_MORE_LIST_SHOW') : $request->moreload + $interval;
		$remain = $correctivePlanCount - $count;
		
		return response()->json(['location_id'=> $location_id, 'html'=> $html, 'loadmore'=> $lower+$upper, 'remain'=> $remain]);
			
	}
	public function ins_load_more_plan_data_old(Request $request)
	{
		$location_id = $request->location_id;
		
		$data = array();
		$interval = config('custom.LOAD_MORE_INTERVAL');
		$lower = empty($request->moreload) ? 0 : $request->moreload;
		$upper = empty($request->moreload) ? config('custom.LOAD_MORE_LIST_SHOW') : config('custom.LOAD_MORE_INTERVAL');
		//---------------------------------------------------
		$correctiveActionChecklistArray = [];
		$correctiveActionArray = [];

		$taskData = Task_lists::where('inspector_id', auth()->user()->id)->where('location_id', $location_id)->get();

		if($taskData->isNotEmpty())
		{
			foreach($taskData as $val)
			{
				$ifTaskRxists = Task_list_subcategories::where('task_list_id', $val->id)->exists();
				if($ifTaskRxists)
				{
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
				
				
				}
			}
		}
		//echo "<pre>";print_r($correctiveActionChecklistArray); die;
		foreach($correctiveActionChecklistArray as $correctiveAction)
		{
			if($correctiveAction['lo_direct_approve'] == 0 && ($correctiveAction['inspector_action'] == 0 || $correctiveAction['los_action'] == 0))
			{
				$correctiveActionArray[] = [
					'type' => $correctiveAction['type'],
					'task_id' => $correctiveAction['task_id'],
					'checklist_id' => $correctiveAction['checklist_id'],
					'subchecklist_id' => $correctiveAction['subchecklist_id'],
					'rejected_region' => $correctiveAction['rejected_region'],
					'inspector_action' => $correctiveAction['inspector_action'],
					'los_action' => $correctiveAction['los_action'],
					'second_checked' => $correctiveAction['second_checked'],
					'lo_direct_approve' => $correctiveAction['lo_direct_approve'],
					'image' => $correctiveAction['image'],
				
				];
				
			}
		}
		
		$totalRecord = $correctiveActionArray;
		$correctiveActionArray = array_slice($correctiveActionArray, $lower, $upper);
		//echo "<pre>";print_r($correctiveActionArray);
		$data['location_id'] = $location_id;
		$data['mode'] = 'corrective_plan';
		$data['correctivePlanArray'] = $correctiveActionArray;
		$html = view('inspector.loadmore.ins-filter-load-more-data', $data)->render();
		//----------------
		$count  = $request->moreload =='' ? config('custom.LOAD_MORE_LIST_SHOW') : $request->moreload + $interval;
		$remain = count($totalRecord) - $count;
		
		return response()->json(['location_id'=> $location_id, 'html'=> $html, 'loadmore'=> $lower+$upper, 'remain'=> $remain]);
	}
	public function ins_load_more_appr_data(Request $request)
	{
		$location_id = $request->location_id;
		$data = array();
		$interval = config('custom.LOAD_MORE_INTERVAL');
		$lower = empty($request->moreload) ? 0 : $request->moreload;
		$upper = empty($request->moreload) ? config('custom.LOAD_MORE_LIST_SHOW') : config('custom.LOAD_MORE_INTERVAL');
		//echo $lower.' '.$upper; die;
		//---------------------------------------------------
		$taskListIds = Task_lists::where('location_id', $location_id)
			->where('inspector_id', auth()->user()->id)
			->pluck('id');

		$categoryIds = Task_list_subcategories::whereIn('task_list_id', $taskListIds)
			->pluck('task_list_category_id');
		
		//-----05-08-2025 if submit checklist then submit task data show-----
		$submit_task_id = Task_list_subcategories::whereIn('task_list_category_id', $categoryIds)->pluck('task_list_id')->toArray();
		//-------
		$approvedCompletedArray = [];
		// Checklist IDs with existing corrective needed
		$correctiveChecklistApprIds = Task_list_corrective_action::whereIn('task_list_id', $taskListIds)
			->where(function ($q) {
				$q->where(function ($q) {
					$q->where('inspector_action', 1)->where('los_action', 1);
				});
			})
			->whereNotNull('checklist_id')
			->pluck('checklist_id')->toArray();
			
			//print_r($correctiveChecklistApprIds);die;
		//------------------------------------
		
			$existingCorrectiveChecklistApprIds = DB::table('task_list_corrective_actions')
			    ->whereIn('task_list_id', $taskListIds)
				->whereNotNull('checklist_id')
				->pluck('checklist_id')->toArray();

			$checklistApprIds = DB::table('task_list_checklists')
				->where('approve', 1)
				->whereIn('task_list_id', $taskListIds)
				//->whereNotIn('checklist_id', $existingCorrectiveChecklistApprIds)
				->pluck('checklist_id')
				->toArray();
				
		//print_r($checklistApprIds);die;
		$correctiveApprChecklistIds = array_merge($correctiveChecklistApprIds,$checklistApprIds);
		//print_r($correctiveApprChecklistIds);die;
		//------------------------------------

		$correctiveSubChecklistApprIds = Task_list_corrective_action::whereIn('task_list_id', $taskListIds)
			->where(function ($q) {
				$q->where(function ($q) {
					$q->where('inspector_action', 1)->where('los_action', 1);
				});
			})
			->whereNotNull('subchecklist_id')
			->pluck('subchecklist_id')->toArray();
			
		//print_r($correctiveSubChecklist_Ids);die;
			
			$existingCorrectiveSubChecklistIds = DB::table('task_list_corrective_actions')
			    ->whereIn('task_list_id', $taskListIds)
				->whereNotNull('subchecklist_id')
				->pluck('subchecklist_id')->toArray();

			$subchecklistApprIds = DB::table('task_list_subchecklists')
				->where('approve', 1)
				->whereIn('task_list_id', $taskListIds)
				//->whereNotIn('subchecklist_id', $existingCorrectiveSubChecklistIds)
				->pluck('subchecklist_id')
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
					->whereIn('task_list_id', $submit_task_id)
					->whereIn('category_id', $categoryIds)
					->whereIn('approve', [0,1])
					->whereIn('checklist_id', $correctiveApprChecklistIds)
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
						->whereIn('task_list_id', $submit_task_id)
						->whereIn('category_id', $categoryIds)
						->whereIn('approve', [0,1])
						->whereIn('subchecklist_id', $correctiveApprSubChecklistIds)
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
					->whereIn('task_list_id', $submit_task_id)
					->whereIn('category_id', $categoryIds)
					->whereIn('approve', [0,1])
					->whereIn('checklist_id', $correctiveApprChecklistIds)
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
						->whereIn('task_list_id', $submit_task_id)
						->whereIn('category_id', $categoryIds)
						->whereIn('approve', [0,1])
						->whereIn('subchecklist_id', $correctiveApprSubChecklistIds)
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
		
		$html = view('inspector.loadmore.ins-filter-load-more-data', $data)->render();
		
		//--------------------------------------
		$count  = $request->moreload =='' ? config('custom.LOAD_MORE_LIST_SHOW') : $request->moreload + $interval;
		$remain = $correctiveApprovedCount - $count;
		
		return response()->json(['location_id'=> $location_id, 'html'=> $html, 'loadmore'=> $lower+$upper, 'remain'=> $remain]);
	}
	public function ins_load_more_appr_data_old(Request $request)
	{
		$location_id = $request->location_id;
		$data = array();
		$interval = config('custom.LOAD_MORE_INTERVAL');
		$lower = empty($request->moreload) ? 0 : $request->moreload;
		$upper = empty($request->moreload) ? config('custom.LOAD_MORE_LIST_SHOW') : config('custom.LOAD_MORE_INTERVAL');
		//---------------------------------------------------
		$completedApprChecklistArray = [];
		$completedApprSubcheckListArray = [];
		$taskData = Task_lists::where('inspector_id', auth()->user()->id)->where('location_id', $location_id)->get();

		if($taskData->isNotEmpty())
		{
			foreach($taskData as $val)
			{
				$ifTaskRxists = Task_list_subcategories::where('task_list_id', $val->id)->exists();
				if($ifTaskRxists)
				{
					$taskChklist = Task_list_checklists::where('task_list_id', $val->id)->get();
					if($taskChklist->isNotEmpty())
					{
						foreach($taskChklist as $task)
						{
							$task_list_checklist_corrective_needed = Task_list_corrective_action::where('task_list_id', $val->id)->where('checklist_id', $task->checklist_id)->first();
							
							if($task->approve == 0)
							{
								if($task_list_checklist_corrective_needed)
								{
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
					$taskSubChklist = Task_list_subchecklists::where('task_list_id', $val->id)->get();
					if($taskSubChklist->isNotEmpty())
					{
						foreach($taskSubChklist as $subtask)
						{
							$task_list_subchecklist_corrective_needed = Task_list_corrective_action::where('task_list_id', $val->id)->where('checklist_id', $subtask->task_list_checklist_id)->where('subchecklist_id', $subtask->subchecklist_id)->first();
							
							if($subtask->approve == 0)
							{
								$isSubChecklistfiles = '';
								$subChecklistimages = '';
								$isSubChecklistfiles = Task_list_subchecklist_rejected_files::where('task_list_subchecklist_id', $subtask->id)->first();
								
								$subChecklistimages = $isSubChecklistfiles ? $isSubChecklistfiles->file  : '';
						
								if($task_list_subchecklist_corrective_needed)
								{
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
			
			
			}
		}
		
		
		$approvedCompletedArray = [];
		$approvedCompleted = array_merge($completedApprChecklistArray,$completedApprSubcheckListArray);
		//$totalRecord = count($approvedCompleted);
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
							'updated_at' => $appr['updated_at'],
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
							'updated_at' => $appr['updated_at'],
						];
					}
				}
				else{
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
							'updated_at' => $appr['updated_at'],
						];
					}
					else{
						$approvedCompletedArray[] = [
							'type' => $appr['type'],
							'task_id' => $appr['task_id'],
							'checklist_id' => $appr['checklist_id'],
							'inspector_action' => $appr['inspector_action'],
							'los_action' => $appr['los_action'],
							'updated_at' => $appr['updated_at'],
						];
					}
				}
			}
		}
		//echo "<pre>";print_r($approvedCompletedArray);die;
		
		usort($approvedCompletedArray, function ($a, $b) {
			return strtotime($b['updated_at']) <=> strtotime($a['updated_at']);
		});
		
		$totalRecord = $approvedCompletedArray;
		$approvedCompletedArray = array_slice($approvedCompletedArray, $lower, $upper);
		$data['approvedCompletedArray'] = $approvedCompletedArray;
		
		$data['location_id'] = $location_id;
		$data['mode'] = 'corrective_appr';
		
		$html = view('inspector.loadmore.ins-filter-load-more-data', $data)->render();
		
		//--------------------------------------
		$count  = $request->moreload =='' ? config('custom.LOAD_MORE_LIST_SHOW') : $request->moreload + $interval;
		$remain = count($totalRecord) - $count;
		
		return response()->json(['location_id'=> $location_id, 'html'=> $html, 'loadmore'=> $lower+$upper, 'remain'=> $remain]);
	}
	public function los_load_more_needed_data(Request $request)
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
			
		$taskListIds = Task_lists::where('location_id', $location_id)->pluck('id');

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
			//->whereNotNull('checklist_id')
			->whereNotNull('subchecklist_id')
			->get(['task_list_id', 'checklist_id', 'subchecklist_id'])
			->map(function ($item) {
				return $item->task_list_id . '-' . $item->checklist_id . '-' . $item->subchecklist_id;
			})
			->toArray();
			
			//echo "<pre>";print_r($excludedSubChecklistPairs);die;
			
			$correctiveSubChecklistIds = DB::table('task_list_subchecklists')
			->where('approve', 0)
			->whereIn('task_list_id', $taskListIds)
			->whereIn('category_id', $categoryIds)
			->get(['task_list_id', 'subchecklist_id', 'task_list_checklist_id'])
			->filter(function ($item) use ($excludedSubChecklistPairs) {
				$pairKey = $item->task_list_id . '-' . $item->task_list_checklist_id . '-' . $item->task_list_checklist_id;
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
		$html = view('inspector.loadmore.los-load-more-data', $data)->render();
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
	public function los_load_more_action_data(Request $request)
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
		$taskListIds = Task_lists::where('location_id', $location_id)->pluck('id');

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
		$html = view('inspector.loadmore.los-load-more-data', $data)->render();
		//----------------
		$count  = $request->moreload =='' ? config('custom.LOAD_MORE_LIST_SHOW') : $request->moreload + $interval;
		$remain = $correctiveActionCount - $count;
		
		return response()->json(['location_id'=> $location_id, 'html'=> $html, 'loadmore'=> $lower+$upper, 'remain'=> $remain]);
			
			
	}
	public function los_load_more_plan_data(Request $request)
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
			
		$taskListIds = Task_lists::where('location_id', $location_id)->pluck('id');

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
		$html = view('inspector.loadmore.los-load-more-data', $data)->render();
		//----------------
		$count  = $request->moreload =='' ? config('custom.LOAD_MORE_LIST_SHOW') : $request->moreload + $interval;
		$remain = $correctivePlanCount - $count;
		
		return response()->json(['location_id'=> $location_id, 'html'=> $html, 'loadmore'=> $lower+$upper, 'remain'=> $remain]);
			
	}
	public function los_load_more_appr_data(Request $request)
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
			
		$taskListIds = Task_lists::where('location_id', $location_id)->pluck('id');

		$categoryIds = Task_list_subcategories::whereIn('task_list_id', $taskListIds)
			->pluck('task_list_category_id');
			
		//-----05-08-2025 if submit checklist then submit task data show-----
		$submit_task_id = Task_list_subcategories::whereIn('task_list_category_id', $categoryIds)->pluck('task_list_id')->toArray();
			
		$approvedCompletedArray = [];
		
		$subcategoriesTaskIds = Task_list_subcategories::whereIn('task_list_category_id', $categoryIds)
			->pluck('task_list_id');
			
		$matchingTaskListIds = array_values(array_intersect($taskListIds->toArray(),  $subcategoriesTaskIds->toArray()));
		
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
		
		$html = view('inspector.loadmore.los-load-more-data', $data)->render();
		
		//--------------------------------------
		$count  = $request->moreload =='' ? config('custom.LOAD_MORE_LIST_SHOW') : $request->moreload + $interval;
		$remain = $correctiveApprovedCount - $count;
		
		return response()->json(['location_id'=> $location_id, 'html'=> $html, 'loadmore'=> $lower+$upper, 'remain'=> $remain]);
	}
	public function los_load_more_data_old(Request $request)
	{
		$location_id = $request->location_id;
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
		
		$taskData = Task_lists::where('location_id', $location_id)->get();
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
		$html = view('inspector.loadmore.los-load-more-data', $data)->render();
		
		//--------------------------------------
		//$count  = $request->moreload =='' ? config('custom.LOAD_MORE_LIST_SHOW') : $request->moreload + $interval;
		$remain = $totalRecord - $count;
		
		return response()->json(['location_id'=> $location_id, 'html'=> $html, 'loadmore'=> $lower+$upper, 'remain'=> $remain]);
	}
	
	public function lo_load_more_needed_data(Request $request)
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
			
		$taskListIds = Task_lists::where('location_id', $location_id)->pluck('id');

		$categoryIds = Task_list_subcategories::whereIn('task_list_id', $taskListIds)
			->pluck('task_list_category_id');
			
		//-----05-08-2025 if submit checklist then submit task data show-----
		$submit_task_id = Task_list_subcategories::whereIn('task_list_category_id', $categoryIds)->pluck('task_list_id')->toArray();
		//echo "<pre>";print_r($submit_task_id);die;
		//---------
		
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
				
		//echo "<pre>";print_r($correctiveChecklistIds);die;
		//--------------subchecklist---------------------

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
			
			//echo "<pre>";print_r($excludedSubChecklistPairs);die;
			
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
		
		//echo "<pre>";print_r($correctiveSubChecklistIds);die;
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
		$correctiveNeddedArray = [];
		//echo "<pre>";print_r($correctiveneeded);die;
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
		$html = view('inspector.loadmore.lo-load-more-data', $data)->render();
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
	public function lo_load_more_action_data(Request $request)
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
		$taskListIds = Task_lists::where('location_id', $location_id)->pluck('id');

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
		$html = view('inspector.loadmore.lo-load-more-data', $data)->render();
		//----------------
		$count  = $request->moreload =='' ? config('custom.LOAD_MORE_LIST_SHOW') : $request->moreload + $interval;
		$remain = $correctiveActionCount - $count;
		
		return response()->json(['location_id'=> $location_id, 'html'=> $html, 'loadmore'=> $lower+$upper, 'remain'=> $remain]);
	}
	public function lo_load_more_plan_data(Request $request)
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
			
		$taskListIds = Task_lists::where('location_id', $location_id)->pluck('id');

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
		$html = view('inspector.loadmore.lo-load-more-data', $data)->render();
		//----------------
		$count  = $request->moreload =='' ? config('custom.LOAD_MORE_LIST_SHOW') : $request->moreload + $interval;
		$remain = $correctivePlanCount - $count;
		
		return response()->json(['location_id'=> $location_id, 'html'=> $html, 'loadmore'=> $lower+$upper, 'remain'=> $remain]);
			
	}
	public function lo_load_more_appr_data(Request $request)
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
			
		$taskListIds = Task_lists::where('location_id', $location_id)->pluck('id');

		$categoryIds = Task_list_subcategories::whereIn('task_list_id', $taskListIds)
			->pluck('task_list_category_id');
		
		//-----05-08-2025 if submit checklist then submit task data show-----
		$submit_task_id = Task_list_subcategories::whereIn('task_list_category_id', $categoryIds)->pluck('task_list_id')->toArray();
		
		$approvedCompletedArray = [];
		
		$subcategoriesTaskIds = Task_list_subcategories::whereIn('task_list_category_id', $categoryIds)
			->pluck('task_list_id');
			
		$matchingTaskListIds = array_values(array_intersect($taskListIds->toArray(),  $subcategoriesTaskIds->toArray()));
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
			
		//print_r($correctiveSubChecklist_Ids);die;
			
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
		
		$html = view('inspector.loadmore.lo-load-more-data', $data)->render();
		
		//--------------------------------------
		$count  = $request->moreload =='' ? config('custom.LOAD_MORE_LIST_SHOW') : $request->moreload + $interval;
		$remain = $correctiveApprovedCount - $count;
		
		return response()->json(['location_id'=> $location_id, 'html'=> $html, 'loadmore'=> $lower+$upper, 'remain'=> $remain]);
	}
	public function lo_load_more_data_old(Request $request)
	{
		$location_id = $request->location_id;
		$data = [];
		$interval = config('custom.LOAD_MORE_INTERVAL');
		$lower = empty($request->moreload) ? 0 : $request->moreload;
		$upper = empty($request->moreload) ? config('custom.LOAD_MORE_LIST_SHOW') : config('custom.LOAD_MORE_INTERVAL');
		$tab = $request->tab;
		//-----------------------------------------------
		$correctiveActionChecklistArray = [];
		$correctiveActionSubcheckListArray = [];
		$correctiveCheckChecklistArray = [];
		$correctiveCheckSubcheckListArray = [];
		
		$correctiveNeddedChecklistArray = [];
		$completedApprChecklistArray = [];
		
		$correctiveNeddedSubchecklistArray = [];
		$completedApprSubcheckListArray = [];
		
		
			$user_type = auth()->user()->user_type;
			$taskData = Task_lists::where('location_id', $location_id)->get();
			
			if($taskData->isNotEmpty())
			{
				foreach($taskData as $val)
				{
					$ifTaskRxists = Task_list_subcategories::where('task_list_id', $val->id)->exists();
					if($ifTaskRxists)
					{
						// checklist and  respective files approve=0 
						
						$categoriesChecklistArr = Task_list_subcategories::where('task_list_id', $val->id)->pluck('task_list_category_id')->toArray();
						
						$taskChklist = Task_list_checklists::where('task_list_id', $val->id)->whereIn('category_id', $categoriesChecklistArr)->where('approve', 0)->get();
						
						//$taskChklist = Task_list_checklists::where('task_list_id', $val->id)->where('approve', 0)->get();
						
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
						//---10-07-2025 
						/*$taskSubChklist = Task_list_subchecklists::where('task_list_id', $val->id)->where('approve', 0)->get();*/
						//-----
						
						$taskSubChklist = Task_list_subchecklists::where('task_list_id', $val->id)->whereIn('category_id', $categoriesChecklistArr)->where('approve', 0)->get();
						
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
						//------------12-06-2025------------
						// checklist and  respective files approve=1 
						/*$taskChklist = Task_list_checklists::where('task_list_id', $val->id)->get();*/
						
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
												'rejected_status'=> $task_list_checklist_corrective_needed->rejected_status,
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
													'inspector_action'=> 1,
													'los_action'=> 1,
													'updated_at'=> $task->updated_at->format('Y-m-d H:i:s'),
												];
								}
							}
						}
						
						// subchecklist and respective files
						/*$taskSubChklist = Task_list_subchecklists::where('task_list_id', $val->id)->get();*/
						
						$taskSubChklist = Task_list_subchecklists::where('task_list_id', $val->id)->whereIn('category_id', $categoriesChecklistArr)->get();
						
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
												'rejected_status'=> $task_list_subchecklist_corrective_needed->rejected_status,
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
				}
			}
			
			/*$data['correctiveAction'] = array_merge($correctiveActionChecklistArray, $correctiveActionSubcheckListArray);*/
				
			/*$data['correctiveCheck'] = array_merge($correctiveCheckChecklistArray, $correctiveCheckSubcheckListArray);*/
			
			/*$data['correctiveNeeded'] = array_merge($correctiveNeddedChecklistArray,$correctiveNeddedSubchecklistArray);*/
			$data['approvedCompleted'] = array_merge($completedApprChecklistArray,$completedApprSubcheckListArray);
		//---------------------------------------------
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
			$data['correctiveAction'] = array_merge($correctiveCheckChecklistArray, $correctiveCheckSubcheckListArray);
			$data['mode'] = 'corrective_action';
			
			$correctiveAction = array_merge($correctiveCheckChecklistArray, $correctiveCheckSubcheckListArray);
			foreach($correctiveAction as $result)
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
			$data['correctivePlan'] = array_merge($correctiveCheckChecklistArray, $correctiveCheckSubcheckListArray);
			
			$correctivePlan = array_merge($correctiveCheckChecklistArray, $correctiveCheckSubcheckListArray);
			$data['mode'] = 'corrective_plan';
			
			//foreach($correctiveActionChecklistArray as $result)
			foreach($correctivePlan as $result)
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
		
		//-----------------------------------------------
		$data['location_id'] = $location_id;
		$data['lower'] = $lower;
		$data['upper'] = $upper;
		$html = view('inspector.loadmore.lo-load-more-data', $data)->render();
		//-----------------------------------------------
		$remain = $totalRecord - $count;
		
		return response()->json(['location_id'=> $location_id, 'html'=> $html, 'loadmore'=> $lower+$upper, 'remain'=> $remain]);
	}
	public function task_list_edit($lid='', $id='')
	{
		$data = [];
		$taskData = Task_lists::where('id', $id)->first();
		if($taskData)
		{
			$data['location_id']  = $lid;
			$data['task_id']  = $id;
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
		else{
			return redirect('inspector-dashboard');
		}
	}
	public function delete_task(Request $request)
	{
		$id = $request->id;
		
		Task_lists::where('id', $id)->delete();
		Task_location_categories::where('task_list_id', $id)->delete();
		
		// delete from checklist and checklist files table
		$checklistData = Task_list_checklists::where('task_list_id', $id)->where('approve', 0)->get();
		if($checklistData->isNotEmpty())
		{
			foreach($checklistData as $checklist)
			{
				$fileDataArr = Task_list_checklist_rejected_files::where('task_list_checklist_id', $checklist->id)->get();
				//echo "<pre>";print_r($fileDataArr);die;
				if($fileDataArr->isNotEmpty())
				{
					foreach($fileDataArr as $files)
						$file_name = $files->file ? $files->file : '';
						
						$filePath = public_path('uploads/reject-files/' . $file_name);
						if (file_exists($filePath)) {
							unlink($filePath);
						}
				}
				
				Task_list_checklist_rejected_files::where('task_list_checklist_id', $checklist->id)->delete();
			}
		}
		Task_list_checklists::where('task_list_id', $id)->delete();
		
		// delete from subchecklist and subchecklist files table
		$filePath = '';
		$subchecklistData = Task_list_subchecklists::where('task_list_id', $id)->where('approve', 0)->get();
		//echo "<pre>";print_r($subchecklistData);die;
		if($subchecklistData->isNotEmpty())
		{
			foreach($subchecklistData as $subchecklist)
			{
				$fileDataArr = Task_list_subchecklist_rejected_files::where('task_list_checklist_id', $subchecklist->task_list_checklist_id)->where('task_list_subchecklist_id', $subchecklist->id)->get();
				if($fileDataArr->isNotEmpty())
				{
					foreach($fileDataArr as $files)
					{
						$file_name = $files->file ? $files->file : '';
						
						$filePath = public_path('uploads/reject-files/subchecklist/' . $file_name);
						if (file_exists($filePath)) {
							unlink($filePath);
						}
					}
				}
				
				Task_list_subchecklist_rejected_files::where('task_list_checklist_id', $subchecklist->task_list_checklist_id)->where('task_list_subchecklist_id', $subchecklist->id)->delete();
			}
		}
		Task_list_subchecklists::where('task_list_id', $id)->delete();
		
		// delete from corrective action  table
		$correctiveFiles = Task_list_corrective_action::where('task_list_id', $id)->get();
		if($correctiveFiles->isNotEmpty())
		{
			foreach($correctiveFiles as $files)
			{
				$corrsctive_files = Task_list_corrective_action_file::where('task_list_corrective_actions_id', $files->id)->first();
				$file_name = $corrsctive_files ? $corrsctive_files->file : '';
				$filePath = public_path('uploads/corrective_action/' . $file_name);
				if (file_exists($filePath)) {
					unlink($filePath);
				}
				
				Task_list_corrective_action_file::where('task_list_corrective_actions_id', $files->id)->delete();
			}
		}
		Task_list_corrective_action::where('task_list_id', $id)->delete();
		return response()->json(['success', 'success']);
	}
	
	public function lo_checklist_first_reply_view($location_id='',$task_id='',$checklist_id='',$type='', $tab='')
	{
		if (auth()->user()->user_type == 1 || auth()->user()->user_type == 3) {
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
		return view('inspector.lo-first-reply-view', $data);
	}
	public function lo_subchecklist_first_reply_view($location_id='',$task_id='',$checklist_id='',$subchecklist_id='',$type='', $tab='')
	{
		if (auth()->user()->user_type == 1 || auth()->user()->user_type == 3) {
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
		return view('inspector.lo-first-reply-view', $data);
	}
	//-------------
	public function lo_checklist_completed_approved_view($location_id='',$task_id='',$checklist_id='',$type='', $tab='')
	{
		if (auth()->user()->user_type == 1 || auth()->user()->user_type == 3) {
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
		return view('inspector.lo-completed-approved-view', $data);
	}
	public function lo_subchecklist_completed_approved_view($location_id='',$task_id='',$checklist_id='',$subchecklist_id='',$type='', $tab='')
	{
		if (auth()->user()->user_type == 1 || auth()->user()->user_type == 3) {
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
		return view('inspector.lo-completed-approved-view', $data);
	}
	
	//----
	public function ia_los_checklist_completed_approved_view($location_id='',$task_id='',$checklist_id='',$type='', $tab='')
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
		
		return view('inspector.ia-los-completed-approved-view', $data);
	}
	public function ia_los_subchecklist_completed_approved_view($location_id='',$task_id='',$checklist_id='',$subchecklist_id='',$type='', $tab='')
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
		
		return view('inspector.ia-los-completed-approved-view', $data);
	}
	
}
