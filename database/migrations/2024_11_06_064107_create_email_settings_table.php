<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('email_settings', function (Blueprint $table) {
            $table->id();
            $table->string('email_from_address');
			$table->string('emails_from_name');
			$table->string('smtp_host');
            $table->string('smtp_user');
            $table->string('smtp_password');
            $table->string('smpt_port');
            $table->string('smtp_authentication_domain');
            $table->tinyInteger('php_mail_smtp')->default(0)->comment('0=php_mail,1=smtp');
			$table->tinyInteger('smtp_security')->default(0)->comment('0=none,1=tlc,2=ssl,');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('email_settings');
    }
};
