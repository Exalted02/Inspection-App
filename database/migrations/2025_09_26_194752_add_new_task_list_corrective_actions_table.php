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
            $table->tinyInteger('tab_no')->after('repeated_observation')->comment('1=needed,2=action,3=plan,4=completed');
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
