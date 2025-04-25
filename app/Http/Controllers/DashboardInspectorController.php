<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Manage_location;
use App\Models\Manage_location_category;
use App\Models\Category;
use App\Models\Task_lists;
use App\Models\Task_list_categories;

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
	public function checklist($lid='',$catid='')
    {
		
		$data = [];
		$data['categoryData'] = Category::with('get_subcategory')->where('id', $catid)->get();
		$data['location_id'] = $lid;
		return view('inspector.checklist', $data);
    }
	public function send_location_details(Request $request)
	{
		$location_id = $request->post('location_id');
		$category_id = $request->post('category_id');
		$details = $request->post('details');
		//echo $location_id.' '.$category_id.' '.$details; die;
		$model = new Task_lists();
		$model->inspector_id = auth()->user()->id;
		$model->location_id = $location_id;
		$model->save();
		$task_list_id  = $model->id;
		
		$modelCat = new Task_list_categories();
		$modelCat->task_list_id = $task_list_id ?? null;
		$modelCat->category_id = $category_id ?? null;
		$modelCat->location_details = $details ?? null;
		$modelCat->status = 1;
		$modelCat->save();
		return response()->json(['status' => 'success', 'message' => 'Data saved successfully.']);

	}
}
