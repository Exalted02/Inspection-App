<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class ChangePasswordController extends Controller
{
    public function index()
    {
      $data[] = '';
      return view('change-password.index', $data);
    }
	public function save_data(Request $request)
    {
        $request->validate([
            'old_password' => 'required',
            'new_password' => 'required|min:8|confirmed',
        ]);

        $user = Auth::user();

        if (!Hash::check($request->input('old_password'), $user->password)) {
            return response()->json([
                'errors' => [
                    'old_password' => ['The old password does not match our records.']
                ]
            ], 422);
        }

        $user->password = Hash::make($request->input('new_password'));
        $user->save();
    }
}
