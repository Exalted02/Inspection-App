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
        return view('inspector.location-details', $data);
    }
	public function category($lid='',$catid='')
    {
		
		$data = [];
		$data['categoryData'] = Category::with('get_subcategory')->where('id', $catid)->get();
		$data['location_id'] = $lid;
		$details = Task_lists::where('inspector_id', auth()->user()->id)->where('location_id', $lid)->where('category_id', $catid)->first();
		$data['location_details'] = $details ? $details->location_details : null;
		$data['task_id'] = $details ? $details->id : null;
		return view('inspector.category-subcategory', $data);
    }
	public function send_location_details(Request $request)
	{
		$location_id = $request->post('location_id');
		$category_id = $request->post('category_id');
		$details = $request->post('details');
		$inspectorId = auth()->user()->id;
		
		$taskList = Task_lists::where('inspector_id', $inspectorId)
                      ->where('location_id', $location_id)
					  ->where('category_id', $category_id)
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
				$taskList->category_id = $category_id;
				$taskList->location_details = $details;
				$taskList->save();
		}
		
		return response()->json(['status' => 'success', 'message' => 'Data saved successfully.']);

	}
	
	public function check_task_id(Request $request)
	{
		$category_id = $request->post('cat_id');
		$location_id = $request->post('location_id');
		$inspector_id = auth()->user()->id;
		$exists = Task_lists::where('inspector_id', $inspector_id)->where('location_id', $location_id)->where('category_id', $category_id)->exists();
		if($exists)
		{
			$taskid = Task_lists::where('inspector_id', $inspector_id)->where('location_id', $location_id)->where('category_id', $category_id)->first()->id;
			return response()->json(['hasData'=> true, 'taskid'=>$taskid]);
		}
		else{
			return response()->json(['hasData'=> false, 'id'=>NULL]);
		}
	}
	
	public function checklist_question($taskid='', $cat_id='',$subcat_id='')
    {
		$data = [];
		//echo $cat_id.' '.$subcat_id; die;
		$data['checklistdata'] = Checklist::with('get_subchecklist','get_category','get_subcategory')->where('category_id',$cat_id)->where('subcategory_id', $subcat_id)->where('status','!=', 2)->first();
		//echo "<pre>";print_r($checklistdata);die;
		/*$nextQuestion = Checklist::where('category_id', $cat_id)
		->where('subcategory_id', $subcat_id)
		->where('status', '!=', 2)
		->where('id', '>', $current_question_id)
		->orderBy('id', 'asc')
		->first();*/
		$data['task_id'] = $taskid;
        return view('inspector.checklist-question', $data);
    }
	public function checklist_next_question(Request $request)
	{
		$approveStatus = $request->post('approveStatus');
		$mode = $request->post('mode');
		$rejectTextsSingle = $request->post('rejectTextsSingle');
		$rejectTextsMultiple = json_decode($request->input('rejectTextsMultiple'), true);
		//echo "<pre>";print_r($rejectTextsMultiple);die;
		/*if(!empty($rejectTextsMultiple) && is_array($rejectTextsMultiple)) {
				foreach ($rejectTextsMultiple as $subChecklistId => $text) {
					echo "SubChecklist ID: " . $subChecklistId . " - Reason: " . $text['text'] ." status- ".$text['approve_status'] . "<br>";
				}
			}
		echo 'hello '.$request->post('current_question_id') .'--'. $request->post('task_id');die;*/
		//-------------------------------------
		$task_id = $request->post('task_id');
		$current_question_id = $request->post('current_question_id');
		$category_id = $request->post('category_id');
		$subcategory_id = $request->post('subcategory_id');
		$nextQuestionExists = Checklist::where('category_id', $category_id)
		->where('subcategory_id', $subcategory_id)
		->where('status', '!=', 2)
		->where('id', '>', $current_question_id)
		->orderBy('id', 'asc')
		->exists();
		
		$nextId = '';
		$name  = '';
		$subchecklist = '';
		$subcategoryname = '';
		//$subChklistArr = [];
		
		//---add record to table
		if($mode == 'single')
		{
			if($approveStatus !='')
			{
				$checkTastChecklistExists  = Task_list_checklists::where('task_list_id', $task_id)->where('task_list_subcategory_id', $subcategory_id)->where('checklist_id', $current_question_id)->first();
				$hasid = $checkTastChecklistExists ? $checkTastChecklistExists->id : null;
				if($hasid)
				{
					$model = Task_list_checklists::find($hasid);
					$model->rejected_region = $approveStatus == 0 ? $rejectTextsSingle : null;
					$model->approve 	= $approveStatus;
					$model->save();
					$task_list_checklist_id = $hasid;
					
				}
				else
				{
					
					$model = new Task_list_checklists();	
					$model->task_list_id = $task_id ?? null;
					$model->task_list_subcategory_id = $subcategory_id ?? null;
					$model->checklist_id = $current_question_id ?? null;
					$model->rejected_region = $rejectTextsSingle ?? null;
					$model->approve 	= $approveStatus;
					$model->save();
					$task_list_checklist_id = $model->id;
				}
				
				$checkTemps = Task_list_checklist_temp_rejected_files::where(
				[
					'inspector_id'=> auth()->user()->id,
					'task_id'=> $task_id,
					'task_list_checklist_id'=>$current_question_id,
					'subcategory_id'=>$subcategory_id
				])->get();
				
				if ($checkTemps->isNotEmpty()) {
					foreach ($checkTemps as $tempFile) {
						$filename = $tempFile->file;

						$sourcePath = public_path('uploads/temp-reject-files/' . $filename);
						$destinationPath = public_path('uploads/reject-files/' . $filename);

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
						->where('task_list_subcategory_id', $subcategory_id)
						->where('task_list_checklist_id', $current_question_id)
						->where('subchecklist_id', $subChecklistId)
						->first();
						$hasid = $checkTastSubChecklistExists ? $checkTastSubChecklistExists->id : null;
						if($hasid)
						{
							$model = Task_list_subchecklists::find($hasid);
							
							$model->rejected_region = $text['approve_status'] == 0 ? $text['text'] : null;
							$model->approve = $text['approve_status'];
							$model->save();
						}
						else
						{
							$model = new Task_list_subchecklists();
							$model->task_list_id = $task_id ?? null;
							$model->task_list_subcategory_id = $subcategory_id ?? null;
							$model->task_list_checklist_id = $current_question_id ?? null;
							$model->subchecklist_id = $subChecklistId ?? null;
							$model->rejected_region = $text['text'] ?? null;
							$model->approve = $text['approve_status'];
							$model->save();
							$task_list_subchecklist_id = $model->id;
						}
					}
				}
			}
		}
		//-------
		$subChklistArr = [];
		if($nextQuestionExists)
		{
			$nextQuestion = Checklist::with('get_subchecklist','get_category','get_subcategory')->where('category_id', $category_id)
			->where('category_id', $category_id)
			->where('subcategory_id', $subcategory_id)
			->where('status', '!=', 2)
			->where('id', '>', $current_question_id)
			->orderBy('id', 'asc')
			->first();
			//echo "<pre>";print_r($nextQuestion);die;
			$nextId = $nextQuestion->id;
			$name = $nextQuestion->name;
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
				
				$subcategoryname = $nextQuestion->get_subcategory->name;
			}
			
			// fetch data from task_list_checklist
			$iffetch  = Task_list_checklists::where('task_list_id', $task_id)->where('task_list_subcategory_id', $subcategory_id)->where('checklist_id', $nextId)->first();
			$next_rejected_region = $iffetch ? $iffetch->rejected_region : null;
			$next_approve = $iffetch ? $iffetch->approve : '';
			
			// fetch files 
			$task_list_checklist_id = $iffetch ? $iffetch->id : null;
			$existingFiles = [];
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
							->where('task_list_subcategory_id', $subcategory_id)
							->where('task_list_checklist_id', $nextId)
							->get();
			if($ifsubfetch->isNotEmpty())
			{
				foreach($ifsubfetch as $subchecklistval)
				{
					$fetchsubChklistArr[] = [
						'subchecklist_id' => $subchecklistval->subchecklist_id,
						'rejected_region' => $subchecklistval->rejected_region,
						'approve' => $subchecklistval->approve
					];
				}
			}
		}
		return response()->json
		(
			[
				'task_id'=>$task_id,
				'currentid'=> $nextId ?? null,
				'name' => $name ?? null,
				'subchecklist' => $subChklistArr,
				'subcategoryname' => $subcategoryname,
				'next_rejected_region'=> $next_rejected_region ?? '',
				'next_approve'=>$next_approve,
				'existingNextFiles'=>$existingFiles,
				'fetchsubChklistArr'=>$fetchsubChklistArr
			]
		);
	}
	public function checklist_previous_question(Request $request)
	{
		$task_id = $request->post('task_id');
		$current_question_id = $request->post('current_question_id');
		$category_id = $request->post('category_id');
		$subcategory_id = $request->post('subcategory_id');
		$nextQuestionExists = Checklist::where('category_id', $category_id)
		->where('subcategory_id', $subcategory_id)
		->where('status', '!=', 2)
		->where('id', '<', $current_question_id)
		->orderBy('id', 'desc')
		->exists();
		
		$nextId = '';
		$name  = '';
		$subchecklist = '';
		$subcategoryname = '';
		
		if($nextQuestionExists)
		{
			$nextQuestion = Checklist::with('get_subchecklist','get_category','get_subcategory')->where('category_id', $category_id)
			->where('category_id', $category_id)
			->where('subcategory_id', $subcategory_id)
			->where('status', '!=', 2)
			->where('id', '<', $current_question_id)
			->orderBy('id', 'desc')
			->first();
			//echo "<pre>";print_r($nextQuestion);die;
			$nextId = $nextQuestion->id;
			$name = $nextQuestion->name;
			$subChklistArr = [];
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
				
				$subcategoryname = $nextQuestion->get_subcategory->name;
			}
			
			// fetch data from task_list_checklist
			$iffetch  = Task_list_checklists::where('task_list_id', $task_id)->where('task_list_subcategory_id', $subcategory_id)->where('checklist_id', $nextId)->first();
			$next_rejected_region = $iffetch ? $iffetch->rejected_region : null;
			$next_approve = $iffetch ? $iffetch->approve : '';
			
			// fetch files 
			$task_list_checklist_id = $iffetch ? $iffetch->id : null;
			$existingFiles = [];
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
		}
		return response()->json(
			[
				'task_id'=>$task_id,
				'currentid'=> $nextId ?? null,
				'name' => $name ?? null,
				'subchecklist' => $subChklistArr,
				'subcategoryname' => $subcategoryname,
				'next_rejected_region'=> $next_rejected_region ?? '',
				'next_approve'=>$next_approve,
				'existingPreviousFiles'=>$existingFiles
			]
		);
	}
	public function single_reject_files(Request $request)
	{
		$current_checklist_id = $request->post('current_checklist_id');
		$subcategory_id = $request->post('subcategory_id');
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
			$tempmodel->subcategory_id = $subcategory_id ?? null;
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

	
}
