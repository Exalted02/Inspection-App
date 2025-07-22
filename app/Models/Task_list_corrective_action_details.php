<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Task_list_corrective_action_details extends Model
{
    use HasFactory;
	protected $fillable = [
        'task_list_corrective_action_id', 
		'order',
        'lo_corrective_action_plan_final_checks', 
        'ia_los_rejected_reason', 
		'inspector_action_date',
		'los_action_date', 
        'approved_status', 
		'rejected_status',
		'rejected_repeated',
    ];
}
