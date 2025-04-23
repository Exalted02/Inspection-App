<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Inspector extends Model
{
    use HasFactory;
	protected $table = 'inspectors';
	protected $fillable = [
        'name', 
        'email', 
        'password', 
        'company_name', 
        'avatar', 
        'background_image', 
        'status', 
    ];
	public function get_company()
	{
		return $this->belongsTo(Manage_company::class, 'company_name');
	}
}
