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
        Schema::table('task_list_corrective_actions', function (Blueprint $table) {
            $table->text('ia_los_first_rejected_reason')->nullable()->after('lo_corrective_action_plan_second_check');
			$table->text('ia_los_second_rejected_reason')->nullable()->after('ia_los_first_rejected_reason');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('task_list_corrective_actions', function (Blueprint $table) {
            //
        });
    }
};
