<?php

namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use App\Models\Category;
use App\Models\Manage_location_category;
use App\Models\Countries;
use App\Models\States;
use App\Models\Cities;
use Lang;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
	{
		$has_search  = 0;
		//print_r($request->all());
		if($request->all() && count($request->all()) > 0)
		{
			$has_search  = 1;
		}
		$data['has_search'] = $has_search;
		
		$data['location_id'] = $request->src_location_id;
		
		$dataArr = Category::whereHas('locationCategories', function ($q) use ($id) {
			$q->where('location_id', $request->src_location_id);
		});
		//$dataArr = Category::query();
		
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
		$data['category'] = $dataArr->get();
		return view('admin.location.category',$data);
	}
	
	
	
	public function save_category(Request $request)
	{
		
		//echo "<pre>";print_r($request->all());die;
		
		
		$existingStage = Category::where('name', $request->post('name'))->where('location_id', $request->post('location_id'))->where('status', '!=', 2)
        ->when($request->post('id'), function ($query) use ($request) {
            $query->where('id', '!=', $request->post('id'));
        })
        ->first();
		
		if ($existingStage) {
			return response()->json([
				'success' => false,
				'message' => 'category name already exists.'
			]);
		}
		
		
		if($request->post('id')>0)
		{
			$model= Category::find($request->post('id'));
			
			$model->name =	$request->post('name');
			$model->created_at	=	date('Y-m-d');
			$model->save();
			$id = $request->post('id');
		}
		else{
			$model=new Category();
			$model->location_id		=	$request->post('location_id');
			$model->name		=	$request->post('name');
			$model->status		=	1;
			$model->created_at	=	date('Y-m-d');
			$model->save();
			
			$id = $model->id;
			
			$mngCatmodel = new Manage_location_category();
			$mngCatmodel->location_id = $request->post('location_id');
			$mngCatmodel->category_id = $id;
			$mngCatmodel->save();
		}
		
		$fileName = '';
		if($request->hasFile('category_image')) {
			$destinationPath = public_path('uploads/category/');
			if (!file_exists($destinationPath)) {
				mkdir($destinationPath, 0777, true);
			}
			$file = $request->file('category_image');
			$fileName = time() . '_' . $file->getClientOriginalName();
			$file->move($destinationPath, $fileName);
			
			$updtmodel= Category::find($id);
			$updtmodel->image = $fileName;
			$updtmodel->save();
		}
		
		return response()->json([
			'success' => true
		]);
	}
	public function edit_category(Request $request)
	{
		$category = Category::where('id', $request->id)->first();
		$data = array();
		$data['id']  = $category->id ;
		$data['location_id']  = $category->location_id ;
		$data['name']  = $category->name ;
		$data['category_image']  = $category->image ;
		$data['app_url']  = url('uploads/category') ;
		$data['edit']  =  Lang::get('edit_category');
		return $data;
	}
	public function delete_category(Request $request)
	{
		$name = Category::where('id', $request->id)->first()->name;
		echo json_encode($name);
	}
	public function delete_list(Request $request)
	{
		//$check = check_record_use($request->id, 'product_code');
		//if($check){
			$del = Category::where('id', $request->id)->update(['status'=>2]);
			
			$data['result'] ='success';
		//}else{
			//$data['result'] ='error';
		//}
		echo json_encode($data);
	}
	
	public function update_status(Request $request)
	{
		$status = Category::where('id', $request->id)->first()->status;
		$change_status = $status == 1 ? 0 : 1;
		$update = Category::where('id', $request->id)->update(['status'=> $change_status]);
		
		$data['result'] = $change_status;
		echo json_encode($data);
	}
	public function manage_location_wise_category($id='')
	{
		$has_search  = 0;
		
		$data['has_search'] = $has_search;
		$data['location_id'] = $id;
		
		$dataArr = Category::query();
		/*$data['category'] = Category::whereHas('locationCategories', function ($q) use ($id) {
			$q->where('location_id', $id);
		})
		->where('status', '!=', 2)
		->orderBy('name', 'ASC')
		->get();*/
		
		$dataArr->where('location_id', $id);
		$dataArr->where('status', '!=', 2);
		$dataArr->orderBy('name', 'ASC'); 
		$data['category'] = $dataArr->get();
		return view('admin.location.category',$data);
	}
	 
}

