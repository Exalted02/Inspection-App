<?php
  
namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Manage_location;
use App\Models\Task_lists;

class ManagementController extends Controller
{
    public function index()
    {
		$data = [];
		
		$locations = Manage_location::where('company_id', auth()->user()->company_name)->get();
		$data['locations'] = $locations;
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
	public function management_location_task_details($id='')
	{
		$data = [];
		$checklist_id = 5;
		$subchecklist_id = 3;
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
}
