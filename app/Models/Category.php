<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;
	protected $table = 'categories';
	protected $fillable = [
        'location_id', 
        'name', 
        'image', 
        'status', 
    ];
	public function get_subcategory()
	{
		return $this->hasMany(Subcategory::class, 'category_id', 'id');
	}
	
	public function locationCategories()
	{
		return $this->hasMany(Manage_location_category::class, 'category_id');
	}
}
