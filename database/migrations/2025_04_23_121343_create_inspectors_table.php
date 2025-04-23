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
        Schema::create('inspectors', function (Blueprint $table) {
            $table->id();
			$table->string('name')->nullable();
			$table->string('email')->nullable();
			$table->string('password')->nullable();
			$table->integer('company_name')->nullable();
			$table->string('avatar')->nullable();
			$table->string('background_image')->nullable();
			$table->tinyInteger('status') ->nullable()->comment('0=inactive , 1=active');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inspectors');
    }
};
