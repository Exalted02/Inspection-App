<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;

class DashboardController extends Controller
{
    public function location_details($id='')
    {
		$data = [];
		
        return view('inspector.location-details', $data);
    }
    public function checklist()
    {
		$data = [];
		
        return view('inspector.checklist', $data);
    }
}
