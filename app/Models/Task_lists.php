<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Task_lists extends Model
{
    use HasFactory;
	protected $table = 'task_lists';
	protected $fillable = [
        'inspector_id', 
        'location_id', 
        'category_id', 
        'lo_id', 
        'los_id', 
        'management_id', 
        'task_title', 
        'image', 
        'location_details', 
        'status', 
    ];
	
	public function get_user()
	{
		return $this->belongsTo(User::class, 'inspector_id');
	}
	
	public function location_wise_category()
	{
		return $this->belongsTo(Task_location_categories::class, 'task_list_id');
	}
}
