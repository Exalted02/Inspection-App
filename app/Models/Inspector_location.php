<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Inspector_location extends Model
{
    use HasFactory;
	protected $table = 'inspector_locations';
	protected $fillable = [
        'inspector_id', 
        'location_id', 
    ];
}
