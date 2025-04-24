<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Task_list_categories extends Model
{
    use HasFactory;
	protected $table = 'task_list_categories';
	protected $fillable = [
        'task_list_id', 
        'category_id', 
        'location_details', 
        'status', 
    ];
}
