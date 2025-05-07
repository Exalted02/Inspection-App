<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Task_list_subcategories extends Model
{
    use HasFactory;
	protected $table = 'task_list_subcategories';
	protected $fillable = [
        'task_list_id', 
        'task_list_category_id', 
        'subcategory_id', 
        'total_task', 
        'completed_task', 
        'is_submit', 
    ];
}
