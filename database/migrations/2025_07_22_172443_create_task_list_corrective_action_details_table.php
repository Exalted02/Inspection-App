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
        Schema::create('task_list_corrective_action_details', function (Blueprint $table) {
            $table->id();
			$table->integer('task_list_corrective_action_id')->nullable();
			$table->text('lo_corrective_action_plan_final_checks')->nullable();
			$table->text('ia_los_rejected_reason')->nullable();
			$table->dateTime('inspector_action_date')->nullable();
			$table->dateTime('los_action_date')->nullable();
			$table->tinyInteger('approved_status')->default(0)->comment('1=approved by IA, 2=approved by LOS');
			$table->tinyInteger('rejected_status')->default(0)->comment('1=rejected by IA, 2=rejected by LOS');
			$table->tinyInteger('rejected_repeated')->default(0)->comment('if any one rejected then 1');
			$table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('task_list_corrective_action_details');
    }
};
