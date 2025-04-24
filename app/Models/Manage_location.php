<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Manage_location extends Model
{
    use HasFactory;
	protected $table = 'manage_locations';
	protected $fillable = [
        'location_name', 
        'image', 
        'address', 
        'zipcode', 
        'country_id', 
        'state_id', 
        'city_id', 
        'categories',
		'status', 
    ];
	
	public function get_country()
	{
		return $this->belongsTo(Countries::class, 'country_id');
	}
	public function get_state()
	{
		return $this->belongsTo(States::class, 'state_id');
	}
	public function get_city()
	{
		return $this->belongsTo(Cities::class, 'city_id');
	}
	
	public function category_by_location()
	{
		return $this->hasMany(Manage_location_category::class, 'location_id', 'id');
	}
}
