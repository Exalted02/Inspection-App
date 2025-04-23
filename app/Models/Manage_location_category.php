<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Manage_location_category extends Model
{
    use HasFactory;
	protected $table = 'manage_location_categories';
	protected $fillable = [
        'location_id', 
        'category_id', 
    ];
}
