<?php

namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use App\Models\Category;
use App\Models\Inspector;
use App\Models\Inspector_location;
use App\Models\Manage_company;
use App\Models\Manage_location;
use Lang;
use Illuminate\Support\Facades\Hash;

class InspectorController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
	{
		$has_search  = 0;
		if($request->all() && count($request->all()) > 0)
		{
			$has_search  = 1;
		}
		$data['has_search'] = $has_search;
		
		$dataArr = Inspector::with('get_company');
		if($request->search_name)
		{
			$dataArr->where('name', 'like', '%' . $request->search_name . '%');
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
				$dataArr->orderBy('name', $request->search_sort_by);
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
		
		$dataArr->orderBy('name', 'ASC'); 
		$data['inspector'] = $dataArr->get();
		$data['companies'] = Manage_company::where('status','!=',2)->get();
		$data['locations'] = Manage_location::where('status','!=',2)->get();
		return view('admin.location.inspector',$data);
	}
	
	
	
	public function save_inspector(Request $request)
	{
		
		//echo "<pre>";print_r($request->all());die;
		
		
		$existingInsp = Inspector::where('name', $request->post('name'))->where('status', '!=', 2)
        ->when($request->post('id'), function ($query) use ($request) {
            $query->where('id', '!=', $request->post('id'));
        })
        ->first();
		
		if ($existingInsp) {
			return response()->json([
				'success' => false,
				'label' => 'name',
				'message' => 'Inspector name already exists.'
			]);
		}
		
		$duplemail = Inspector::where('email', $request->post('email'))->where('status', '!=', 2)
        ->when($request->post('id'), function ($query) use ($request) {
            $query->where('id', '!=', $request->post('id'));
        })
        ->first();
		
		if ($duplemail) {
			return response()->json([
				'success' => false,
				'label' => 'email',
				'message' => 'Email already exists.'
			]);
		}
		
		$locationData = $request->input('location');
		
		if($request->post('id')>0)
		{
			$model= Inspector::find($request->post('id'));
			$model->name =	$request->post('name');
			$model->email		=	$request->post('email');
			$model->password	=	Hash::make($request->input('password'));
			$model->company_name =	$request->post('company_name');
			$model->created_at	=	date('Y-m-d');
			$model->save();
			$id = $request->post('id');
			
			if(!empty($locationData))
			{
				Inspector_location::where('location_id', $request->post('id'))->delete();
				foreach($locationData as $location)
				{
					$mngCatmodel = new Inspector_location();
					$mngCatmodel->inspector_id = $id;
					$mngCatmodel->location_id = $location;
					$mngCatmodel->save();
				}
			}
		}
		else{
			$model=new Inspector();
			$model->name		=	$request->post('name');
			$model->email		=	$request->post('email');
			$model->password	=	Hash::make($request->input('password'));
			$model->company_name =	$request->post('company_name');
			$model->status		=	1;
			$model->created_at	=	date('Y-m-d');
			$model->save();
			$id = $model->id;
			
			if(!empty($locationData))
			{
				foreach($locationData as $location)
				{
					$mngCatmodel = new Inspector_location();
					$mngCatmodel->inspector_id = $id;
					$mngCatmodel->location_id = $location;
					$mngCatmodel->save();
				}
			}
		}
		
		$fileName = '';
		if($request->hasFile('avatar')) {
			$destinationPath = public_path('uploads/Inspector/');
			if (!file_exists($destinationPath)) {
				mkdir($destinationPath, 0777, true);
			}
			$file = $request->file('avatar');
			$fileName = time() . '_' . $file->getClientOriginalName();
			$file->move($destinationPath, $fileName);
			
			$updtmodel= Inspector::find($id);
			$updtmodel->avatar = $fileName;
			$updtmodel->save();
		}
		
		$backgroundImgfileName = '';
		if($request->hasFile('backgroung_image')) {
			$destinationPath = public_path('uploads/Inspector/');
			if (!file_exists($destinationPath)) {
				mkdir($destinationPath, 0777, true);
			}
			$file = $request->file('backgroung_image');
			$backgroundImgfileName = time() . '_' . $file->getClientOriginalName();
			$file->move($destinationPath, $backgroundImgfileName);
			
			$updtmodel= Inspector::find($id);
			$updtmodel->background_image = $backgroundImgfileName;
			$updtmodel->save();
		}
		
		return response()->json([
			'success' => true
		]);
	}
	public function edit_inspector(Request $request)
	{
		$inspector = Inspector::where('id', $request->id)->first();
		$data = array();
		$data['id']  = $inspector->id ;
		$data['name']  = $inspector->name ;
		$data['email']  = $inspector->email ;
		$data['password']  = $inspector->password ;
		$data['company_name']  = $inspector->company_name;
		
		$data['avatar']  = $inspector->avatar;
		$data['background_image']  = $inspector->background_image;
		$data['app_url']  = url('uploads/inspector') ;
		$data['edit']  =  Lang::get('edit_inspector');
		
		$inspLocArry = array();
		$inspector_location  = Inspector_location::where('inspector_id', $request->id)->get();
		foreach($inspector_location as $val)
		{
			$inspLocArry[] = $val->location_id;
		}
		$data['location_data']  = $inspLocArry;
		
		return $data;
	}
	public function delete_inspector(Request $request)
	{
		$name = Inspector::where('id', $request->id)->first()->name;
		echo json_encode($name);
	}
	public function delete_list(Request $request)
	{
		//$check = check_record_use($request->id, 'product_code');
		//if($check){
			$del = Inspector::where('id', $request->id)->update(['status'=>2]);
			
			$data['result'] ='success';
		//}else{
			//$data['result'] ='error';
		//}
		echo json_encode($data);
	}
	
	public function update_status(Request $request)
	{
		$status = Inspector::where('id', $request->id)->first()->status;
		$change_status = $status == 1 ? 0 : 1;
		$update = Inspector::where('id', $request->id)->update(['status'=> $change_status]);
		
		$data['result'] = $change_status;
		echo json_encode($data);
	}
	 
}

