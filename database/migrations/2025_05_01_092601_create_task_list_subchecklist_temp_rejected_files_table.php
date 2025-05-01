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
        Schema::create('task_list_subchecklist_temp_rejected_files', function (Blueprint $table) {
            $table->id();
			$table->integer('inspector_id')->nullable();
			$table->integer('task_list_id')->nullable();
			$table->integer('task_list_checklist_id')->nullable();
			$table->integer('subchecklist_id')->nullable();
			$table->string('file')->nullable()->index();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('task_list_subchecklist_temp_rejected_files');
    }
};
