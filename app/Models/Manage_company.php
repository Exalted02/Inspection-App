<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Manage_company extends Model
{
    use HasFactory;
	protected $table = 'manage_companies';
	protected $fillable = [
        'company_name', 
		'status', 
    ];
}
