<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subchecklist extends Model
{
    use HasFactory;
	protected $table = 'subchecklists';
	protected $fillable = [
        'checklist_id', 
        'name', 
        'status', 
    ];
	
	public function get_checklist()
	{
		return $this->belongsTo(Checklist::class, 'checklist_id');
	}
}
