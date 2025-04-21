<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EmailManagement;
use Illuminate\Http\Request;

class EmailManagementController extends Controller
{
    public function index()
    {
		$data = EmailManagement::query()->where('status', '!=', '2')->get();
		$result['data']=$data;
        return view('admin.email-management.index',$result);
    }
    public function email_management_edit(Request $request, $id='')
    {
		$data = EmailManagement::where('id' ,$id)->first();
		$result['data']=$data;
        return view('admin.email-management.edit',$result);
    }
	public function manage_email_management_process(Request $request)
    {
		$request->validate([
			'message_subject' => 'required',
			'message' => 'required',
		],[],[
			'message_subject' => 'Email Subject',
			'message' => 'Email Message',
		]);
		if($request->post('id')>0){
			$arr = EmailManagement::updateOrCreate([
				'id'   => $request->post('id'),
			],[
				'message'     		=> $request->post('message'),
				'message_subject' 	=> $request->post('message_subject'),
			]);
			$msg="Data has been updated successfully.";
		}else{
			$model=new EmailManagement();
			$model->updated_at=null;
			$model->message=$request->post('message');
			$model->message_subject=$request->post('message_subject');
			$model->status=$request->post('status');
			$model->save();
			$msg="New data has been added successfully.";
		}
		// $request->session()->flash('message',$msg);
        // return redirect('admin/email-management');
	}
}
