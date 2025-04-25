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
        'location_details', 
        'status', 
    ];
}
