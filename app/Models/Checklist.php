<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Checklist extends Model
{
    use HasFactory;
	protected $table = 'checklists';
	protected $fillable = [
        'category_id', 
        'subcategory_id', 
        'name', 
        'status', 
    ];
	
	public function get_category()
	{
		return $this->belongsTo(Category::class, 'category_id');
	}
	
	public function get_subcategory()
	{
		return $this->belongsTo(Subcategory::class, 'subcategory_id');
	}
	public function get_subchecklist()
	{
		return $this->hasMany(Subchecklist::class, 'checklist_id' ,'id');
	}
}
