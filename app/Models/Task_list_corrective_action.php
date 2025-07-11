<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Task_list_corrective_action extends Model
{
    use HasFactory;
	protected $fillable = [
        'task_list_id', 
        'category_id', 
        'checklist_id', 
        'subchecklist_id', 
        'lo_id', 
        'lo_corrective_action_plan', 
        'lo_corrective_action_plan_second_check', 
        'lo_completed_by', 
        'lo_direct_approve', 
        'inspector_id', 
        'inspector_action', 
        'inspector_action_date', 
        'los_id', 
        'los_action', 
        'los_action_date', 
        'approved_status', 
		'rejected_status',
		'rejected_repeated',
    ];
	
	public function get_inspector()
	{
		return $this->belongsTo(User::class, 'inspector_id');
	}
	
	public function get_lo()
	{
		return $this->belongsTo(User::class, 'lo_id');
	}
	public function get_los()
	{
		return $this->belongsTo(User::class, 'los_id');
	}
}
