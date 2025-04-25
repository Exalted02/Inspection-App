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
        Schema::table('task_lists', function (Blueprint $table) {
            $table->integer('category_id')->nullable()->after('location_id');
			$table->string('location_details')->nullable()->after('management_id')->index();
			$table->tinyInteger('status')->default(1)->comment('0=not states 1=Check area based on Inspection checklist  2=Approve checks 3=Set corrective actions 4=check for final corrective outcome  5=Approve action plan')->after('location_details');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('task_lists', function (Blueprint $table) {
            //
        });
    }
};
