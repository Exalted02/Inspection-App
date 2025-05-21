<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Task_location_categories extends Model
{
    use HasFactory;
	protected $table = 'task_location_categories';
	protected $fillable = [
        'task_list_id', 
        'category_id', 
    ];
}
