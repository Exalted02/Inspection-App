<?php
//common functions here
use App\Models\Lead_products;
use App\Models\Lead_outsources;
use App\Models\Outsource_service_information;
use App\Models\Inventory;
use App\Models\Cms;
use App\Models\EmailManagement;
use App\Models\Email_settings;
use App\Models\Referral;
use App\Models\Resource;
use App\Models\Outsources;
use App\Models\Product_code;
use App\Models\Rolepermission;
use App\Models\Leads;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Config;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use Illuminate\Support\Facades\Lang;
use App\Models\Product_group;
use App\Models\Product_name_master;
use App\Models\Product_unit;

use App\Models\Cast;
use App\Models\Religion;
use App\Models\Marital_status;
use App\Models\Gender;
use App\Models\Resources;

use App\Models\Countries;
use App\Models\Cities;
use App\Models\States;
use App\Models\Source;
use App\Models\Followup_remarks;

// use File;
	
//Get referral model Data
    function get_referral_model(){
		$data = Referral::where('status', 1)->orderBy('created_at', 'DESC')->get();
        return $data;
    }	
//Get outsources model Data
    function get_outsources_model(){
		$data = Outsources::where('status', 1)->orderBy('created_at', 'DESC')->get();
        return $data;
    }	
//Get product_code model Data
    function get_product_code_model(){
		$data = Product_code::where('status', 1)->orderBy('created_at', 'DESC')->orderBy('code_name', 'ASC')->get();
        return $data;
    }		
//Get resource model Data
    function get_resource_model(){
		$data = Resource::where('status', 1)->orderBy('created_at', 'DESC')->get();
        return $data;
    }	
//Get source model Data
    function get_source_model(){
		$data = Source::where('status', 1)->orderBy('created_at', 'DESC')->get();
        return $data;
    }	
//Get Followup_remarks model Data
    function get_followup_remarks_model(){
		$data = Followup_remarks::where('status', 1)->orderBy('created_at', 'DESC')->get();
        return $data;
    }
//Get Countries model Data
    function get_all_countries(){
		$data = Countries::get();
        return $data;
    }
//Get States model Data
    function get_state_by_country($id){
		$data = States::where('country_id', $id)->get();
        return $data;
    }
//Get Cities model Data
    function get_city_by_state($id){
		$data = Cities::where('state_id', $id)->get();
        return $data;
    }
//Change date format
    function change_date_format($date, $fromFormat, $toFormat){
		$data = Carbon::createFromFormat($fromFormat, $date)->format($toFormat);
        return $data;
    }
//get website settings
    function web_settings(){
		$data = array(
			'logo' => url('images/logo.png'),
			'screen_name' => config('app.name', 'Laravel'),
			'linkedin' => 'https://linkedin.com',
			'linkedin_image' => url('images/linkedin.png'),
			'instagram' => 'https://instagram.com',
			'instagram_image' => url('images/instagram.png'),
			'year' => date('Y'),
		);
        return $data;
    }
//get email template
	function get_email($id){
		$arr = EmailManagement::where('id',$id)
				->first();
		return $arr;
	}
//Email Configuration
	function set_email_configuration(){
		$settings = Email_settings::find(1);
		if($settings->smtp_security == 1){
			$encryption = 'tls';
		}else if($settings->smtp_security == 2){
			$encryption = 'ssl';
		}else{
			$encryption = '';
		}
		$config = array(
			'driver'     =>     'smtp',
			'host'       =>     isset($settings->smtp_host) ? $settings->smtp_host : '',
			'port'       =>     isset($settings->smpt_port) ? $settings->smpt_port : '',
			'username'   =>     isset($settings->smtp_user) ? $settings->smtp_user : '',
			'password'   =>     isset($settings->smtp_password) ? $settings->smtp_password : '',
			'encryption' =>     $encryption,
			'from'       =>     array('address' => isset($settings->email_from_address) ? $settings->email_from_address : '', 'name' => isset($settings->emails_from_name) ? $settings->emails_from_name : ''),
		);
		Config::set('mail', $config);	
    }	
//send email template
	function send_email_bck($data){
		// toEmails = Receiver Email, bccEmails = Bcc Receiver, ccEmails = Cc Receiver, files = For attatchment files.
		$data['body'] = str_replace(array("[SCREEN_NAME]", "[YEAR]"), array(config('app.site.name'),date('Y')), $data['body']);
		
        Mail::send('email.sendmail', $data, function($message)use($data) {
            $message->to($data["toEmails"]);
			if(isset($data['bccEmails']) && count($data['bccEmails']) > 0){
				$message->bcc($data["bccEmails"]);
			}
			if(isset($data['ccEmails']) && count($data['ccEmails']) > 0){
				$message->cc($data["ccEmails"]);
			}
            $message->subject($data["subject"]);
			if(isset($data['files']) && count($data['files']) > 0){
				foreach ($data['files'] as $file){
					$message->attach($file);
				}
            }
        });
	}
	function send_email($data){
		// toEmails = Receiver Email, bccEmails = Bcc Receiver, ccEmails = Cc Receiver, files = For attatchment files.
		$data['body'] = str_replace(array("[SCREEN_NAME]", "[YEAR]"), array(config('app.name', 'Laravel'),date('Y')), $data['body']);
		set_email_configuration();
        Mail::send('email-setting.sendmail', $data, function($message)use($data) {
            $message->to($data["toEmails"]);
			if(isset($data['bccEmails']) && count($data['bccEmails']) > 0){
				$message->bcc($data["bccEmails"]);
			}
			if(isset($data['ccEmails']) && count($data['ccEmails']) > 0){
				$message->cc($data["ccEmails"]);
			}
            $message->subject($data["subject"]);
			if(isset($data['files']) && count($data['files']) > 0){
				foreach ($data['files'] as $file){
					$message->attach($file);
				}
            }
        });
	}
//uc all text
    function uc_all($data){
        return strtoupper($data);
    }
// this function fetch dynamic data of cms page
    function getCms($id){
        $cms_data = Cms::where('id', $id)->first();
        return $cms_data;
    }
//this function making slug 
    function slug_create($text, string $divider = '-'){
        // replace non letter or digits by divider
        $text = preg_replace('~[^\pL\d]+~u', $divider, $text);
        // transliterate
        $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
        // remove unwanted characters
        $text = preg_replace('~[^-\w]+~', '', $text);
        // trim
        $text = trim($text, $divider);
        // remove duplicate divider
        $text = preg_replace('~-+~', $divider, $text);
        // lowercase
        $text = strtolower($text);
        if (empty($text)) {
            return 'n-a';
        }
        return $text;
    } 
    function link_create($text, string $divider = '-'){
        // replace non letter or digits by divider
        $text = preg_replace('~[^\pL\d]+~u', $divider, $text);
        // transliterate
        $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
        // remove unwanted characters
        $text = preg_replace('~[^-\w]+~', '', $text);
        // trim
        $text = trim($text, $divider);
        // remove duplicate divider
        $text = preg_replace('~-+~', $divider, $text);
        // lowercase
        $text = strtolower($text);
        if (empty($text)) {
            return '';
        }
        return $text;
    }
//Image upload
	function uploadResizeImage($details_path='', $dest_path='', $dest_thumb_path='', $width='', $height='', $takeimage){
		
		/*if($oldimg!='')
		{
		  $image_path = $dest_path.$oldimg;
		  //echo $image_path; die;
		  if(File::isDirectory($image_path)){
			  unlink($image_path);
		  }
		  $image_thum_path = $dest_thumb_path.$oldimg;
		  if(File::isDirectory($image_thum_path)){
			  unlink($image_thum_path);
		  }
		}*/
		
		$paths = [
			'image_path' => $dest_path,
			'thumbnail_path' => $dest_thumb_path
		];
		
		foreach($paths as $key => $path) {
			if(!File::isDirectory($path)){
				File::makeDirectory($path, 0777, true, true);
			}
		}
		
		$imageName = time().'-'.generateImageName($takeimage->getClientOriginalName());
		
		// create image manager with desired driver
		$manager = new ImageManager(new Driver());
		
		$original_img = $img = $manager->read($takeimage);
		$img->save($dest_path.$imageName);
		// $img = $img->resize($width,$height);
		$img->resize($width, $height, function ($constraint) {
			// $constraint->aspectRatio();
		});
		$img->save($dest_thumb_path.$imageName);
		if($details_path != ''){
			if(!File::isDirectory($details_path)){
				File::makeDirectory($details_path, 0777, true, true);
			}
			$details_img = $original_img;
			$details_img->resize(750, 420, function ($constraint) {
				// $constraint->aspectRatio();
			});
			$details_img->save($details_path.$imageName);
		}
		
		return $imageName;
	}
//Replace special character with iconv (like - schönenlandm => schonenlandm)
	function replace_special_characters_iconv($string) {
		// Convert special characters to ASCII
		return iconv('UTF-8', 'ASCII//TRANSLIT', $string);
	}
	
	function generate_unique_id($model, $column, $type, $prefix = ''){
		// Get the last record
		if($type == 4){
			$lastRecord = $model::orderBy('id', 'desc')->first();
		}else{
			$lastRecord = $model::orderBy('id', 'desc')->where('move_to', $type)->first();
		}
		
		// Check if there is any previous record
		if (!$lastRecord) {
			// If no record exists, start with L00001
			$newId = $prefix . str_pad(1, 3, '0', STR_PAD_LEFT);
		} else {
			// Extract numeric part from the last ID (e.g., L00001 -> 00001)
			$lastIdNumber = intval(str_replace($prefix, '', $lastRecord->$column));

			// Increment the numeric part by 1
			$newIdNumber = $lastIdNumber + 1;

			// Create new ID with padded zeros (e.g., 1 -> 00001)
			$newId = $prefix . str_pad($newIdNumber, 3, '0', STR_PAD_LEFT);
		}

		return $newId;  // Return the new unique ID (e.g., L00002)
	} 
	//Get product_group model Data
    function get_Product_group_model(){
		$data = Product_group::where('status', 1)->orderBy('created_at', 'DESC')->get();
        return $data;
    }
	//Get Product_name_master model Data
    function get_Product_name_model(){
		$data = Product_name_master::where('status', 1)->orderBy('created_at', 'DESC')->get();
        return $data;
    }
	// Get the product unit
	function get_Product_unit_model(){
		$data = Product_unit::where('status', 1)->orderBy('created_at', 'DESC')->get();
        return $data;
    }
	// Get gender
	function get_gender_model(){
		$data = Gender::where('status', 1)->get();
        return $data;
    }
	// Get marital status
	function get_marital_status_model(){
		$data = Marital_status::where('status', 1)->get();
        return $data;
    }
	// Get Religion
	function get_religion_model(){
		$data = Religion::where('status', 1)->get();
        return $data;
    }
	// Get Cast
	function get_cast_model(){
		$data = Cast::where('status', 1)->get();
        return $data;
    } 
	// Get Resources
	function get_resource_potal_model(){
		$data = Resources::where('status', 1)->get();
        return $data;
    }
	// Check record is use in child table or not
	/*function check_record_use($id, $type){
		if($type == 'product_code'){
			$results = Lead_products::where('product_code', $id)
			->whereHas('lead', function ($query) {
				$query->where('status', '!=', 2);
			})->first();
			if($results){ return false; }
			
			$results = Lead_outsources::where('outsource_product_code', $id)
			->whereHas('lead', function ($query) {
				$query->where('status', '!=', 2);
			})->first();
			if($results){ return false; }
			
			$results = Outsource_service_information::where('product_code', $id)
			->whereHas('outsource', function ($query) {
				$query->where('status', '!=', 2);
			})->first();
			if($results){ return false; }
			
			$results = Inventory::where('product_code', $id)->where('status', '!=', 2)->first();
			if($results){ return false; }
		}
		if($type == 'product_group'){
			$results = Lead_products::where('product_group', $id)
			->whereHas('lead', function ($query) {
				$query->where('status', '!=', 2);
			})->first();
			if($results){ return false; }
			
			$results = Lead_outsources::where('outsource_product_group', $id)
			->whereHas('lead', function ($query) {
				$query->where('status', '!=', 2);
			})->first();
			if($results){ return false; }
			
			$results = Outsource_service_information::where('product_group', $id)
			->whereHas('outsource', function ($query) {
				$query->where('status', '!=', 2);
			})->first();
			if($results){ return false; }
			
			$results = Inventory::where('product_group', $id)->where('status', '!=', 2)->first();
			if($results){ return false; }
		}
		
		return true;
	}*/
	function get_used_value($type)
	{
		// Define the models and their fields for each type
		$checks = [
			'product_code' => [
				[Lead_products::class, 'product_code', 'lead'],
				[Lead_outsources::class, 'outsource_product_code', 'lead'],
				[Outsource_service_information::class, 'product_code', 'outsource'],
				[Inventory::class, 'product_code', null],
			],
			'product_group' => [
				[Lead_products::class, 'product_group', 'lead'],
				[Lead_outsources::class, 'outsource_product_group', 'lead'],
				[Outsource_service_information::class, 'product_group', 'outsource'],
				[Inventory::class, 'product_group', null],
			],
			'product_name' => [
				[Lead_products::class, 'product_name', 'lead'],
				[Lead_outsources::class, 'outsource_product_name', 'lead'],
				[Outsource_service_information::class, 'product_name', 'outsource'],
				[Inventory::class, 'product_name', null],
			],
			'stage' => [
				[Leads::class, 'stage_id', null],
			],
			'resource' => [
				[Lead_products::class, 'resource_id', 'lead'],
			],
			'outsource' => [
				[Lead_outsources::class, 'outsources_id', 'lead'],
			],
			'referral_category' => [
				[Leads::class, 'referral_category', null],
			],
		];

		// Check if the type is valid
		$data = [];
		if (!isset($checks[$type])) {
			return $data;
		}

		// Loop through the checks for the given type
		foreach ($checks[$type] as [$model, $field, $relation]) {
			$arr = [];
			$query = $model::query();

			// Add relationship condition if defined
			if ($relation) {
				$query->whereHas($relation, function ($query) {
					$query->where('status', '!=', 2);
				});
			} else {
				$query->where('status', '!=', 2);
			}
			$query->whereNotNull($field);
			// If a record is found, return false
			if ($query->exists()) {
				// return false;
				$arr = $query->pluck($field)->toArray();
				$data = array_merge($data, $arr);
			}
		}

		return $data;
	}
	
	// Get All module list Total = 11
	function get_all_module(){
		$module_list = array(
			'lead'=>array('module_id'=>1, 'module_name'=>Lang::get('lead')),
			'prospect'=>array('module_id'=>2, 'module_name'=>Lang::get('prospect')),
			'quotations'=>array('module_id'=>3, 'module_name'=>Lang::get('quotation')),
			// 'proforma_invoice'=>array('module_id'=>4, 'module_name'=>Lang::get('proforma_invoice')),
			// 'customers'=>array('module_id'=>5, 'module_name'=>Lang::get('customer')),
			'referral'=>array('module_id'=>7, 'module_name'=>Lang::get('referral')),
			'outsource'=>array('module_id'=>8, 'module_name'=>Lang::get('outsource')),
			'resource'=>array('module_id'=>11, 'module_name'=>Lang::get('resource')),
			'inventory'=>array('module_id'=>6, 'module_name'=>Lang::get('inventory')),
			// 'event'=>array('module_id'=>9, 'module_name'=>Lang::get('event')),
			// 'dashboard'=>array('module_id'=>10, 'module_name'=>Lang::get('dashboard')),
		);
        return $module_list;
    }
	// Get All module option list
	function get_all_module_options(){
		$module_list_option = array(
			'lead'=>array('add' => 1, 'edit' => 1, 'view' => 1, 'export' => 1, 'import' => 1, 'transfer_to' => 1, 'move_to' => 1, 'delete' => 1, 'status' => 1),
			'prospect'=>array('add' => 1, 'edit' => 1, 'view' => 1, 'export' => 1, 'import' => 1, 'transfer_to' => 1, 'move_to' => 1, 'delete' => 1, 'status' => 1),
			'quotations'=>array('add' => 1, 'edit' => 1, 'view' => 1, 'export' => 1, 'import' => 1, 'transfer_to' => 1, 'move_to' => 1, 'delete' => 1, 'status' => 1),
			'proforma_invoice'=>array('add' => 1, 'edit' => 1, 'view' => 1, 'export' => 1, 'import' => 1, 'transfer_to' => 1, 'move_to' => 1, 'delete' => 1, 'status' => 1),
			'customers'=>array('add' => 1, 'edit' => 1, 'view' => 1, 'export' => 1, 'import' => 1, 'transfer_to' => 1, 'move_to' => 1, 'delete' => 1, 'status' => 1),
			'inventory'=>array('add' => 1, 'edit' => 1, 'view' => 1, 'export' => 1, 'import' => 1, 'transfer_to' => 0, 'move_to' => 0, 'delete' => 1, 'status' => 1),
			'referral'=>array('add' => 1, 'edit' => 1, 'view' => 1, 'export' => 1, 'import' => 1, 'transfer_to' => 0, 'move_to' => 0, 'delete' => 1, 'status' => 1),
			'outsource'=>array('add' => 1, 'edit' => 1, 'view' => 1, 'export' => 1, 'import' => 1, 'transfer_to' => 0, 'move_to' => 0, 'delete' => 1, 'status' => 1),
			'event'=>array('add' => 1, 'edit' => 1, 'view' => 1, 'export' => 1, 'import' => 1, 'transfer_to' => 0, 'move_to' => 0, 'delete' => 1, 'status' => 1),
			'dashboard'=>array('add' => 1, 'edit' => 1, 'view' => 1, 'export' => 1, 'import' => 1, 'transfer_to' => 0, 'move_to' => 0, 'delete' => 1, 'status' => 1),
			'resource'=>array('add' => 1, 'edit' => 1, 'view' => 1, 'export' => 1, 'import' => 1, 'transfer_to' => 0, 'move_to' => 0, 'delete' => 1, 'status' => 1),
		);
        return $module_list_option;
    }
	// Get module option permission
	function check_module_option_permission($module_id, $field){
		if(auth()->user()->user_type == 0){
			return true;
		}else{
			$chk_permission = Rolepermission::where('resource_id', auth()->user()->id)->where('module_id', $module_id)->first();
			// dd($chk_permission);
			if($chk_permission){
				if($chk_permission->$field == 1){
					return true;
				}else{
					return false;
				}
			}else{
				return false;
			}
		}
    }
	// Get module permission
	function check_module_permission($module_id){
		if(auth()->user()->user_type == 0){
			return true;
		}else{
			$chk_permission = Rolepermission::where('resource_id', auth()->user()->id)
								->where('module_id', $module_id)
								->where(function ($query) {
									$query->where('add', 1)
										  ->orWhere('edit', 1)
										  ->orWhere('view', 1)
										  ->orWhere('export', 1)
										  ->orWhere('import', 1)
										  ->orWhere('transfer_to', 1)
										  ->orWhere('move_to', 1)
										  ->orWhere('delete', 1)
										  ->orWhere('status', 1);
								})
								->exists();
			if($chk_permission){
				return true;
			}else{
				return false;
			}
		}
    }
	function get_customer_email($id=''){
		$arr = EmailManagement::where('id',$id)
				->first();
		return $arr;
	}
	function generateImageName($string) {
	   $string = str_replace(' ', '-', $string); // Replaces all spaces with hyphens.

	   return preg_replace('/[^A-Za-z0-9\-.]/', '', $string); // Removes special chars.
	}

?>