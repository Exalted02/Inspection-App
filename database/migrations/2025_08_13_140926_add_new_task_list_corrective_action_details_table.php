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
        Schema::table('task_list_corrective_action_details', function (Blueprint $table) {
			
			$table->tinyInteger('lo_direct_approve')->after('ia_los_rejected_reason')->comment('0=no,1=approved');
			$table->dateTime('lo_completed_by')->nullable()->after('lo_direct_approve');
		});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('task_list_corrective_action_details', function (Blueprint $table) {
            //
        });
    }
};
