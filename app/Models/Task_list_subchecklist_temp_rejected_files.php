<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Task_list_subchecklist_temp_rejected_files extends Model
{
    use HasFactory;
	protected $table = 'task_list_subchecklist_temp_rejected_files';
	protected $fillable = [
        'inspector_id', 
        'task_list_id', 
        'task_list_checklist_id', 
		'subchecklist_id', 
        'file', 
    ];
}
