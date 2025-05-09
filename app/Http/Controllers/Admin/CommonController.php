<?php

namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;

use Illuminate\Http\RedirectResponse;
use App\Models\Areatrade_contact;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;

use App\Models\Countries;
use App\Models\States;
use App\Models\Cities;
use App\Models\Category;
use App\Models\Subcategory;
use App\Models\Manage_location;
use App\Models\User;
use Lang;

class CommonController extends Controller
{
	public function change_multi_status(Request $request)
	{
		$modelClass = $request->model;
		$cat_ids = explode(',',$request->id);
		$updated = $modelClass::whereIn('id',$cat_ids)
				->update(['status'=>$request->status]);
        if($updated){
			$request->session()->flash('message','Status has been updated successfully.');
			return response()->json(['success'=>'Status has been updated successfully.']);
		}else{
			return response()->json(['success'=>'Status not updated.']);
		}
	}
	public function get_state_by_country(Request $request)
	{
		$country_id = $request->country_id;
		$state_list = States::query()->where('country_id', $country_id)->orderBy('name')->get();
		$html = '';
		if($request->page && $request->page == 'search'){
			$html .='<option value>'.Lang::get('state').'</option>';
		}else{
			$html .='<option value>'.Lang::get('please_select').'</option>';
		}
		foreach($state_list as $val)
		{
		$html .='<option value='.$val->id.'>'.$val->name.'</option>';
		}
		echo json_encode($html);
	}
	
	public function delete_multi_data(Request $request)
	{
		$modelClass = $request->model;
		
		
		
		
		$cat_ids = explode(',',$request->id);
		$module = '';
		$updated = $modelClass::whereIn('id',$cat_ids)
				->update(['status'=>2]);	
		if ($modelClass === 'App\Models\Manage_company') {
			Manage_location::whereIn('company_id',$cat_ids)
				->update(['status'=>2]);
			User::whereIn('company_name',$cat_ids)
				->update(['status'=>2]);
		}
		
		if($module != ''){
			if(!check_module_option_permission($module, 'delete')){
				return response()->json([
					'success' => false,
					'message' => 'No Permission!',
				], 403);
			}			
		}
		
		if($updated){
			$request->session()->flash('message','Data deleted successfully.');
			return response()->json(['success'=>'Data deleted successfully.']);
		}else{
			return response()->json(['success'=>'Data not deleted.']);
		}
	}
	
	public function get_city_by_state(Request $request)
	{
		$state_id = $request->state_id;
		$city_list = Cities::query()->where('state_id', $state_id)->orderBy('name')->get();
		$html = '';
		if($request->page && $request->page == 'search'){
			$html ='<option value>'.Lang::get('city').'</option>';
		}else{
			$html .='<option value>'.Lang::get('please_select').'</option>';
		}
		foreach($city_list as $val)
		{
		$html .='<option value='.$val->id.'>'.$val->name.'</option>';
		}
		echo json_encode($html);
	}
	public function get_category_by_subcategory(Request $request)
	{
		$category = $request->category_id;
		$subcategory_list = Subcategory::query()->where('category_id', $category)->orderBy('name')->get();
		$html = '';
		$html .='<option value>'.Lang::get('sub_category').'</option>';
		foreach($subcategory_list as $val)
		{
		$html .='<option value='.$val->id.'>'.$val->name.'</option>';
		}
		echo json_encode($html);
	}
}
