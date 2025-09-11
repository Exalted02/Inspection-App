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
        Schema::table('users_locations', function (Blueprint $table) {
            $table->tinyInteger('notification_status')->default(0)->after('location_id')->comment('0=location added by admin, 1=location transfer by other lo');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users_locations', function (Blueprint $table) {
            //
        });
    }
};
