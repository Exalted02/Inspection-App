<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Users_location extends Model
{
    use HasFactory;
	protected $table = 'users_locations';
	protected $fillable = [
        'user_id', 
        'location_id', 
    ];
}
