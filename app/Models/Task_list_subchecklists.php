<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Task_list_subchecklists extends Model
{
    use HasFactory;
	protected $table = 'task_list_subchecklists';
	protected $fillable = [
        'task_list_id', 
        'task_list_subcategory_id', 
        'task_list_checklist_id', 
        'subchecklist_id', 
        'rejected_region', 
        'approve',
    ];
}
