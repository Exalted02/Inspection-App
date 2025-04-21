<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Email_settings;

class EmailSettingsController extends Controller
{
    public function index()
    {
      $data[] = '';
      $data['email'] = Email_settings::where('id', 1)->first();
      return view('admin.email-setting.index', $data);
    }
    public function save_data(Request $request)
    {
		// dd($request->all());
      $request->validate([
          'admin_email' => 'required|email',
          'email_from_address' => 'required|email',
          'emails_from_name' => 'required|string',
          'smtp_host' => 'required|string',
          'smtp_user' => 'required|string',
          'smtp_password' => 'required|string',
          'smpt_port' => 'required',
          'smtp_security' => 'nullable|in:0,1,2',
          // 'smtp_authentication_domain' => 'nullable|string',
      ]);


      $emailSettings =  Email_settings::find(1);
      $emailSettings->admin_email = $request->admin_email;
      $emailSettings->email_from_address = $request->email_from_address;
      $emailSettings->emails_from_name = $request->emails_from_name;
      $emailSettings->smtp_host = $request->smtp_host;
      $emailSettings->smtp_user = $request->smtp_user;
      $emailSettings->smtp_password = $request->smtp_password;
      $emailSettings->smpt_port = $request->smpt_port;
      $emailSettings->smtp_security = $request->smtp_security;
      // $emailSettings->smtp_authentication_domain = $request->smtp_authentication_domain;
      // $emailSettings->php_mail_smtp = $request->php_mail_smtp;
      $emailSettings->save();

      // return redirect('user.email-settings')->with('success', 'Email settings saved successfully!');
    }
    
}
