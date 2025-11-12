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
        Schema::create('dashboard_notifications', function (Blueprint $table) {
            $table->id();
			$table->integer('user_id')->nullable();
			$table->integer('total_action_plan')->nullable()->comment('IA, LO');
			$table->integer('read_action_plan')->nullable()->comment('IA, LO');
			$table->integer('total_inspection_closure')->nullable()->comment('IA');
			$table->date('inspection_closure_date')->nullable()->comment('IA');
			$table->integer('pending_closure')->nullable()->comment('LOS');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dashboard_notifications');
    }
};
