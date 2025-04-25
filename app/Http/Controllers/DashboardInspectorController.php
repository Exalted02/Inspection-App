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
	public function checklist_question($cat_id='',$subcat_id='')
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
        return view('inspector.checklist-question', $data);
    }
	public function checklist_next_question(Request $request)
	{
		$current_question_id = $request->post('current_question_id');
		$category_id = $request->post('category_id');
		$subcategory_id = $request->post('subcategory_id');
		$nextQuestion = Checklist::with('get_subchecklist','get_category','get_subcategory')->where('category_id', $category_id)
		->where('subcategory_id', $subcategory_id)
		->where('status', '!=', 2)
		->where('id', '>', $current_question_id)
		->orderBy('id', 'asc')
		->first();
		echo $nextQuestion->name;
	}
}
