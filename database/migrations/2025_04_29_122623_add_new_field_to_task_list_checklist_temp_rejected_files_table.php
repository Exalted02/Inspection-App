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
        Schema::table('task_list_checklist_temp_rejected_files', function (Blueprint $table) {
            $table->integer('inspector_id')->nullable()->after('id');
			$table->integer('task_id')->nullable()->after('inspector_id');
			$table->integer('subcategory_id')->nullable()->after('task_list_checklist_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('task_list_checklist_temp_rejected_files', function (Blueprint $table) {
            //
        });
    }
};
