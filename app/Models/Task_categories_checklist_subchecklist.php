<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Task_categories_checklist_subchecklist extends Model
{
    use HasFactory;
	protected $fillable = [
        'task_list_id', 
        'category_id', 
        'checklist_id', 
        'subchecklist_id', 
	];
}
