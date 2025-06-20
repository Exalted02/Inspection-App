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
        'category_id', 
        'task_list_checklist_id', 
        'subchecklist_id', 
        'rejected_region', 
        'approve',
    ];
	
	public function get_subchecklist_files()
	{
		return $this->hasMany(Task_list_subchecklist_rejected_files::class, 'task_list_subchecklist_id');
	}
}
