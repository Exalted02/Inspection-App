<?php

namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use App\Models\Manage_company;
use App\Models\Manage_location;
use App\Models\Countries;
use App\Models\States;
use App\Models\Cities;
use App\Models\Category;
use App\Models\Manage_location_category;
use Lang;

class MasterController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function manage_company(Request $request)
	{
		$has_search  = 0;
		if($request->all() && count($request->all()) > 0)
		{
			$has_search  = 1;
		}
		$data['has_search'] = $has_search;
		//$data['manage_company'] = Manage_company::where('status','!=', 2)->get();
		
		$dataArr = Manage_company::query();
		if($request->search_name)
		{
			$dataArr->where('company_name', 'like', '%' . $request->search_name . '%');
		}
		
		if($request->date_range_phone && $request->date_range_phone != 'MM/DD/YYYY - MM/DD/YYYY') {
			// Explode the date range into start and end dates
			$dates = explode(' - ', $request->date_range_phone);

			// Convert the start date and end date to Y-m-d format
			$start_date = \Carbon\Carbon::createFromFormat('m/d/Y', $dates[0])->startOfDay()->format('Y-m-d');
			$end_date = \Carbon\Carbon::createFromFormat('m/d/Y', $dates[1])->endOfDay()->format('Y-m-d');
			//$contactArr->whereBetween('address_since', [$start_date, $end_date]);
			$dataArr->whereDate('created_at', '>=', $start_date)
            ->whereDate('created_at', '<=', $end_date);
		}
		
		if($request->search_sort_by)
		{
			if($request->search_sort_by == 'ASC' || $request->search_sort_by == 'DESC')
			{
				$dataArr->orderBy('company_name', $request->search_sort_by);
			}
			
			if($request->search_sort_by == 'updated_at')
			{
				$dataArr->orderBy('updated_at', 'DESC');
			}
			
			if($request->search_sort_by == 'created_at')
			{
				$dataArr->orderBy('created_at', 'DESC');
			}
		}
		
		if($request->has('search_status') && $request->search_status !== '' && isset($request->search_status))
		{
			$dataArr->where('status', $request->search_status);
		} else {
			$dataArr->where('status', '!=', 2);
		}
		
		$dataArr->orderBy('company_name', 'ASC'); 
		$data['manage_company'] = $dataArr->get();
		return view('admin.master.manage-company',$data);
	}
	
	public function delete_manage_company_list(Request $request)
	{
		//$check = check_record_use($request->id, 'product_code');
		//if($check){
			$del = Manage_company::where('id', $request->id)->update(['status'=>2]);
			
			$data['result'] ='success';
		//}else{
			//$data['result'] ='error';
		//}
		echo json_encode($data);
	}
	
	public function save_manage_company(Request $request)
	{
		
		//echo "<pre>";print_r($request->all());die;
		
		
		$existingStage = Manage_company::where('company_name', $request->post('company_name'))->where('status', '!=', 2)
        ->when($request->post('id'), function ($query) use ($request) {
            $query->where('id', '!=', $request->post('id'));
        })
        ->first();
		
		if ($existingStage) {
			return response()->json([
				'success' => false,
				'message' => 'company name already exists.'
			]);
		}
		
		
		if($request->post('id')>0)
		{
			$model= Manage_company::find($request->post('id'));
			
			$model->company_name =	$request->post('company_name');
			$model->created_at	=	date('Y-m-d');
			$model->save();
		}
		else{
			$model=new Manage_company();
			$model->company_name		=	$request->post('company_name');
			$model->status		=	1;
			$model->created_at	=	date('Y-m-d');
			$model->save();
		}
		
		return response()->json([
			'success' => true
		]);
	}
	public function edit_company(Request $request)
	{
		$company = Manage_company::where('id', $request->id)->first();
		$data = array();
		$data['id']  = $company->id ;
		$data['company_name']  = $company->company_name ;
		return $data;
	}
	public function delete_company(Request $request)
	{
		$name = Manage_company::where('id', $request->id)->first()->company_name;
		echo json_encode($name);
	}
	
	public function update_status(Request $request)
	{
		$status = Manage_company::where('id', $request->id)->first()->status;
		$change_status = $status == 1 ? 0 : 1;
		$update = Manage_company::where('id', $request->id)->update(['status'=> $change_status]);
		
		$data['result'] = $change_status;
		echo json_encode($data);
	}
	
	public function manage_location(Request $request)
	{
		$has_search  = 0;
		$data['company_location_id'] = '';
		if($request->all() && count($request->all()) > 0)
		{
			$has_search  = 1;
		}
		$data['has_search'] = $has_search;
		
		$dataArr = Manage_location::with('get_country','get_state','get_city');
		if($request->search_name)
		{
			$dataArr->where('location_name', 'like', '%' . $request->search_name . '%');
		}
		
		if($request->src_country_id)
		{
			$dataArr->where('country_id', $request->src_country_id);
		}
		if($request->src_state_id)
		{
			$dataArr->where('state_id', $request->src_state_id);
		}
		if($request->src_city_id)
		{
			$dataArr->where('city_id', $request->src_city_id);
		}
		
		if($request->date_range_phone && $request->date_range_phone != 'MM/DD/YYYY - MM/DD/YYYY') {
			// Explode the date range into start and end dates
			$dates = explode(' - ', $request->date_range_phone);

			// Convert the start date and end date to Y-m-d format
			$start_date = \Carbon\Carbon::createFromFormat('m/d/Y', $dates[0])->startOfDay()->format('Y-m-d');
			$end_date = \Carbon\Carbon::createFromFormat('m/d/Y', $dates[1])->endOfDay()->format('Y-m-d');
			//$contactArr->whereBetween('address_since', [$start_date, $end_date]);
			$dataArr->whereDate('created_at', '>=', $start_date)
            ->whereDate('created_at', '<=', $end_date);
		}
		
		if($request->search_sort_by)
		{
			if($request->search_sort_by == 'ASC' || $request->search_sort_by == 'DESC')
			{
				$dataArr->orderBy('location_name', $request->search_sort_by);
			}
			
			if($request->search_sort_by == 'updated_at')
			{
				$dataArr->orderBy('updated_at', 'DESC');
			}
			
			if($request->search_sort_by == 'created_at')
			{
				$dataArr->orderBy('created_at', 'DESC');
			}
		}
		
		if($request->has('search_status') && $request->search_status !== '' && isset($request->search_status))
		{
			$dataArr->where('status', $request->search_status);
		} else {
			$dataArr->where('status', '!=', 2);
		}
		
		$dataArr->orderBy('location_name', 'ASC'); 
		$data['manage_location'] = $dataArr->get();
		$data['countries'] = Countries::all();
		
		$data['src_state'] = States::where('country_id', $request->src_country_id)->get();
		$data['src_state_id'] = $request->src_state_id;
		
		$data['src_cities'] = Cities::where(['state_id'=>$request->src_state_id])->get();
		$data['src_city_id'] = $request->src_city_id;
		
		$data['categories'] = Category::where('status','!=',2)->get();
		
		return view('admin.master.manage-location',$data);
	
	}
	
	public function save_location(Request $request)
	{
		
		$existingName = Manage_location::where('location_name', $request->post('location_name'))->where('status', '!=', 2)
        ->when($request->post('id'), function ($query) use ($request) {
            $query->where('id', '!=', $request->post('id'));
        })
        ->first();
		
		if ($existingName) {
			return response()->json([
				'success' => false,
				'message' => 'location name already exists.'
			]);
		}
		

		$categoryData = $request->input('category');
		
		//echo "<pre>";print_r($request->post('category')[]);die;
		$image = '';
		if($request->post('id')>0)
		{
			$model= Manage_location::find($request->post('id'));
			$model->company_id 		=	$request->post('company_id');
			$model->location_name 	=	$request->post('location_name');
			$model->address 		=	$request->post('address');
			$model->zipcode 		=	$request->post('zipcode');
			$model->country_id 		=	$request->post('country_id');
			$model->state_id 		=	$request->post('state_id');
			$model->city_id 		=	$request->post('city_id');
			$model->categories 		=	$request->post('categories');
			$model->updated_at		=	date('Y-m-d');
			$model->save();
			$id = $request->post('id');
			
			if(!empty($categoryData))
			{
				Manage_location_category::where('location_id', $request->post('id'))->delete();
				foreach($categoryData as $category)
				{
					$mngCatmodel = new Manage_location_category();
					$mngCatmodel->location_id = $id;
					$mngCatmodel->category_id = $category;
					$mngCatmodel->save();
				}
			}
		}
		else{
			$model=new Manage_location();
			$model->company_id 		=	$request->post('company_id');
			$model->location_name 	=	$request->post('location_name');
			$model->address 		=	$request->post('address');
			$model->zipcode 		=	$request->post('zipcode');
			$model->country_id 		=	$request->post('country_id');
			$model->state_id 		=	$request->post('state_id');
			$model->city_id 		=	$request->post('city_id');
			$model->categories 		=	$request->post('categories');
			$model->status			=	1;
			$model->created_at		=	date('Y-m-d');
			$model->save();
			$id = $model->id;
			
			if(!empty($categoryData))
			{
				foreach($categoryData as $category)
				{
					$mngCatmodel = new Manage_location_category();
					$mngCatmodel->location_id = $id;
					$mngCatmodel->category_id = $category;
					$mngCatmodel->save();
				}
			}
		}
		
		$fileName = '';
		if($request->hasFile('location_image')) {
			$destinationPath = public_path('uploads/location/');
			if (!file_exists($destinationPath)) {
				mkdir($destinationPath, 0777, true);
			}
			$file = $request->file('location_image');
			$fileName = time() . '_' . $file->getClientOriginalName();
			$file->move($destinationPath, $fileName);
			
			$updtmodel= Manage_location::find($id);
			$updtmodel->image = $fileName;
			$updtmodel->save();
			
			// unlink image
			if(!empty($request->post('hid_image')))
			{
				$path = public_path('uploads/location/' . $request->post('hid_image'));
				if (file_exists($path)) {
					unlink($path);
				}
			}
		}
		
		return response()->json([
			'success' => true
		]);
	}
	
	public function location_update_status(Request $request)
	{
		$status = Manage_location::where('id', $request->id)->first()->status;
		$change_status = $status == 1 ? 0 : 1;
		$update = Manage_location::where('id', $request->id)->update(['status'=> $change_status]);
		
		$data['result'] = $change_status;
		echo json_encode($data);
	}
	public function edit_location(Request $request)
	{
		$location = Manage_location::where('id', $request->id)->first();
		$data = array();
		$data['id']  = $location->id ;
		$data['company_id']  = $location->company_id ;
		$data['location_name']  = $location->location_name;
		$data['address']  = $location->address;
		$data['zipcode']  = $location->zipcode;
		$data['country_id']  = $location->country_id;
		$data['state_id']  = $location->state_id;
		$data['city_id']  = $location->city_id;
		$data['location_image']  = $location->image;
		$data['app_url']  = url('uploads/location');
		$data['edit']  =  Lang::get('edit_location');
		
		$catArry = array();
		$location_category = Manage_location_category::where('location_id', $request->id)->get();
		foreach($location_category as $val)
		{
			$catArry[] = $val->category_id;
		}
		$data['categary_data']  = $catArry;
		
		return $data;
	}
	public function delete_location(Request $request)
	{
		$name = Manage_location::where('id', $request->id)->first()->location_name;
		echo json_encode($name);
	}
	public function delete_location_list(Request $request)
	{
		//$check = check_record_use($request->id, 'product_code');
		//if($check){
			$del = Manage_location::where('id', $request->id)->update(['status'=>2]);
			
			$data['result'] ='success';
		//}else{
			//$data['result'] ='error';
		//}
		echo json_encode($data);
	}
	public function manage_company_location(Request $request ,$id='')
	{

		$data = [];
		$data['company_id'] = $id;
		$has_search  = 0;
		$data['has_search'] = $has_search;
		$dataArr = Manage_location::with('get_country','get_state','get_city');
		
		
		$dataArr->orderBy('location_name', 'ASC'); 
		$data['manage_location'] = $dataArr->get();
		$data['countries'] = Countries::all();
		$data['categories'] = Category::where('status','!=',2)->get();
		
		return view('admin.master.manage-location',$data);
	}
    
}

