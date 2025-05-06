<?php

namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use App\Models\Category;
use App\Models\Subcategory;
use App\Models\Checklist;
use Lang;

class ChecklistController extends Controller
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
		$data['category_id'] = $request->src_category;
		$data['subcategory_id'] = $request->src_subcategory;
		
		$dataArr = Checklist::with('get_category','get_subcategory');
		
		$dataArr->where('category_id', $request->src_category);
		
		$dataArr->where('subcategory_id', $request->src_subcategory);
		
		
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
		$data['checklists'] = $dataArr->get();
		$data['categories'] = Category::where('status','!=',2)->get();
		
		$data['src_subcategories'] = Subcategory::where('category_id', $request->src_category)->get();
		$data['src_subcategory'] = $request->src_subcategory;
		
		return view('admin.location.checklist',$data);
	}
	public function save_checklist(Request $request)
	{
		
		//echo "<pre>";print_r($request->all());die;
		$existingStage = Checklist::where('name', $request->post('name'))->where('category_id',$request->post('category'))->where('subcategory_id',$request->post('subcategory'))->where('status', '!=', 2)
        ->when($request->post('id'), function ($query) use ($request) {
            $query->where('id', '!=', $request->post('id'));
        })
        ->first();
		
		if ($existingStage) {
			return response()->json([
				'success' => false,
				'message' => 'checklist name already exists.'
			]);
		}
		
		if($request->post('id')>0)
		{
			$model= Checklist::find($request->post('id'));
			$model->category_id		=	$request->post('category');
			$model->subcategory_id	=	$request->post('subcategory');
			$model->name		=	$request->post('name');
			$model->updated_at	=	date('Y-m-d');
			$model->save();
			$id = $request->post('id');
		}
		else
		{
			$model=new Checklist();
			$model->category_id	=	$request->post('category');
			$model->subcategory_id	=	$request->post('subcategory');
			$model->name		=	$request->post('name');
			$model->status		=	1;
			$model->created_at	=	date('Y-m-d');
			$model->save();
			$id = $model->id;
		}
		
		return response()->json([
			'success' => true
		]);
	}
	public function edit_checklist(Request $request)
	{
		$Checklist = Checklist::where('id', $request->id)->first();
		$data = array();
		$data['id']  = $Checklist->id ;
		$data['category']  = $Checklist->category_id ;
		$data['subcategory']  = $Checklist->subcategory_id ;
		$data['name']  = $Checklist->name ;
		$data['edit']  =  Lang::get('edit_sub_category');
		return $data;
	}
	public function delete_Checklist(Request $request)
	{
		$name = Checklist::where('id', $request->id)->first()->name;
		echo json_encode($name);
	}
	public function delete_list(Request $request)
	{
		//$check = check_record_use($request->id, 'product_code');
		//if($check){
			$del = Checklist::where('id', $request->id)->update(['status'=>2]);
			
			$data['result'] ='success';
		//}else{
			//$data['result'] ='error';
		//}
		echo json_encode($data);
	}
	
	public function update_status(Request $request)
	{
		$status = Checklist::where('id', $request->id)->first()->status;
		$change_status = $status == 1 ? 0 : 1;
		$update = Checklist::where('id', $request->id)->update(['status'=> $change_status]);
		
		$data['result'] = $change_status;
		echo json_encode($data);
	}
	public function manage_location_wise_subcategory_checklist($catid='', $subcatid='')
	{
		$has_search  = 0;
		
		$data['has_search'] = $has_search;
		$data['category_id'] = $catid;
		$data['subcategory_id'] = $subcatid;
		
		$dataArr = Checklist::with('get_category','get_subcategory');
		
		$dataArr->where('category_id', $catid)->where('subcategory_id', $subcatid);
		
		$dataArr->where('status', '!=', 2);
		
		
		$dataArr->orderBy('name', 'ASC'); 
		$data['checklists'] = $dataArr->get();
		$data['categories'] = Category::where('status','!=',2)->get();
		
		//$data['src_subcategories'] = Subcategory::where('category_id', $request->src_category)->get();
		//$data['src_subcategory'] = $request->src_subcategory;
		
		return view('admin.location.checklist',$data);
	}
	 
}

