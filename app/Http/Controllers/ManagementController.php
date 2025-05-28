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
}
