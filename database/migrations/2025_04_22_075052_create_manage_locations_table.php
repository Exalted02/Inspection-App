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
        Schema::create('manage_locations', function (Blueprint $table) {
            $table->id();
			$table->string('location_name')->nullable();
			$table->string('image')->nullable();
			$table->text('address')->nullable();
			$table->string('zipcode')->nullable();
			$table->integer('country_id')->nullable();
			$table->integer('state_id')->nullable();
			$table->integer('city_id')->nullable();
			$table->text('categories')->nullable();
			$table->tinyInteger('status')->default(1)->comment('0=inactive, 1= active');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('manage_locations');
    }
};
