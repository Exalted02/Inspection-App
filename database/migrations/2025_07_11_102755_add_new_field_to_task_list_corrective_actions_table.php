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
             $table->tinyInteger('rejected_repeated')->default(0)->after('rejected_status')->comment('if any one rejected then 1');
             $table->tinyInteger('repeated_observation')->default(0)->after('rejected_repeated')->comment('no of times ins or los rejected');
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
