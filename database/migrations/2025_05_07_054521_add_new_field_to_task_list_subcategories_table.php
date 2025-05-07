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
        Schema::table('task_list_subcategories', function (Blueprint $table) {
            $table->tinyInteger('is_submit')->default(1)->comment('0=no 1=yes')->after('completed_task');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('task_list_subcategories', function (Blueprint $table) {
            //
        });
    }
};
