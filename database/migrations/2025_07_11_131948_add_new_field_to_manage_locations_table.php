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
        Schema::table('manage_locations', function (Blueprint $table) {
            $table->string('unit_floor')->after('image')->nullable();
            $table->string('street')->after('unit_floor')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('manage_locations', function (Blueprint $table) {
            //
        });
    }
};
