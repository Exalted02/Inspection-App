<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;

class DashboardInspectorController extends Controller
{
    public function inspector_dashboard()
    {
		$data = [];
		
        return view('inspector.inspector-dashboard', $data);
    }
}
