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
            $table->tinyInteger('approved_status')->default(0)->after('los_action_date')->comment('1=approved by IA, 2=approved by LOS');
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
