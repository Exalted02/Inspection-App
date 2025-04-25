<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Task_list_checklists extends Model
{
    use HasFactory;
	protected $table = 'task_list_checklists';
	protected $fillable = [
        'task_list_id', 
        'task_list_subcategory_id', 
        'checklist_id', 
        'rejected_region', 
        'approve', 
    ];
}
