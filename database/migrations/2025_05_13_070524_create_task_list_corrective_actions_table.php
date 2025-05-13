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
        Schema::create('task_list_corrective_actions', function (Blueprint $table) {
            $table->id();
			$table->integer('task_list_id')->nullable();
			$table->integer('checklist_id')->nullable();
			$table->integer('subchecklist_id')->nullable();
			$table->integer('lo_id')->nullable();
			$table->text('lo_corrective_action_plan')->nullable();
			$table->dateTime('lo_completed_by')->nullable();
			$table->tinyInteger('lo_direct_approve')->default(0)->comment('0=No, 1=approve');
			$table->integer('inspector_id')->nullable();
			$table->tinyInteger('inspector_action')->default(0)->comment('0=pending, 1=agree ,2=reject');
			$table->dateTime('inspector_action_date')->nullable();
			$table->integer('los_id')->nullable();
			$table->tinyInteger('los_action')->default(0)->comment('0=pending,1=agree ,2=reject');
			$table->dateTime('los_action_date')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('task_list_corrective_actions');
    }
};
