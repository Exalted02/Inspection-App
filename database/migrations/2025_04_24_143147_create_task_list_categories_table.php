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
        Schema::create('task_list_categories', function (Blueprint $table) {
            $table->id();
			$table->integer('task_list_id')->nullable();
			$table->integer('category_id')->nullable();
			$table->string('location_details')->nullable();
			$table->tinyInteger('status')->default(1)->comment('0=not states 1=Check area based on Inspection checklist  2=Approve checks 3=Set corrective actions 4=check for final corrective outcome  5=Approve action plan');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('task_list_categories');
    }
};
