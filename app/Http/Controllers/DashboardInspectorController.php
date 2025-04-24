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
		$id = auth()->user()->id;
		$data['userdata'] = User::with('get_user_location')->where('id', $id)->first();
        return view('inspector-dashboard', $data);
    }
}
