<?php
  
namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Manage_location;

class ManagementController extends Controller
{
    public function index()
    {
		$data = [];
		
		$locations = Manage_location::where('company_id', auth()->user()->company_name)->get();
		$data['locations'] = $locations;
        return view('management.management-dashboard', $data);
    }
    public function management_location()
    {
		$data = [];
		
        return view('management.management-location', $data);
    }
}
