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
        Schema::create('task_list_subcategories', function (Blueprint $table) {
            $table->id();
			$table->integer('task_list_id');
			$table->integer('task_list_category_id');
			$table->integer('subcategory_id');
			$table->integer('total_task');
			$table->integer('completed_task');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('task_list_subcategories');
    }
};
