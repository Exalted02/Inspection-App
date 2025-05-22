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
        'checklist_id', 
        'rejected_region', 
        'approve', 
    ];
	
	public function get_checklist_files()
	{
		return $this->hasMany(Task_list_checklist_rejected_files::class, 'task_list_checklist_id');
	}
}
