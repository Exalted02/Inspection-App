<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Users_location extends Model
{
    use HasFactory;
	protected $table = 'users_locations';
	protected $fillable = [
		'company_id', 
        'user_id', 
        'user_type', 
        'location_id', 
        'notification_status', 
        'primary_owner', 
    ];
}
