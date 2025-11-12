<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Dashboard_notification extends Model
{
    use HasFactory;
	protected $table = 'dashboard_notifications';
	protected $fillable = [
        'user_id', 
        'user_type', 
        'location_id', 
        'task_id', 
        'total_action_plan', 
        'read_action_plan', 
        'total_inspection_closure', 
        'inspection_closure_date', 
        'pending_closure', 
    ];
}
