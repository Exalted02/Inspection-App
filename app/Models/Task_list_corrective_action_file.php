<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Task_list_corrective_action_file extends Model
{
    use HasFactory;
	protected $fillable = [
        'task_list_corrective_actions_id', 
        'file',
        'status',
	];
}
