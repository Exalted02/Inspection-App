<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subcategory extends Model
{
    use HasFactory;
	protected $table = 'subcategories';
	protected $fillable = [
        'category_id', 
        'name', 
        'status', 
    ];
	
	public function get_category()
	{
		return $this->belongsTo(Category::class, 'category_id');
	}
}
