<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Email_settings extends Model
{
    use HasFactory;
    protected $table = 'email_settings';
	protected $fillable = [
        'email_from_address', 'emails_from_name', 'smtp_host', 'smtp_user',
        'smtp_password', 'smtp_port', 'smtp_security', 'smtp_authentication_domain', 'php_mail_smtp', 
    ];
}
