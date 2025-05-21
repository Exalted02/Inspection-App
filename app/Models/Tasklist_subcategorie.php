<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tasklist_subcategorie extends Model
{
    use HasFactory;
	protected $fillable = [
        'task_list_id', 
        'subcategory_id', 
    ];
}
