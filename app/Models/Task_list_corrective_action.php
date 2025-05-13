<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Task_list_corrective_action extends Model
{
    use HasFactory;
	protected $fillable = [
        'task_list_id', 
        'checklist_id', 
        'subchecklist_id', 
        'lo_id', 
        'lo_corrective_action_plan', 
        'lo_completed_by', 
        'lo_direct_approve', 
        'inspector_id', 
        'inspector_action', 
        'inspector_action_date', 
        'los_id', 
        'los_action', 
        'los_action_date', 
    ];
}
