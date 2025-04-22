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
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
			$table->string('name')->nullable();
			$table->string('image')->nullable();
			$table->tinyInteger('status') ->nullable()->comment('1=Check area based on Inspection checklist, 1= Approve checks, 2=Set corrective actions, 3=check for final corrective outcome, 4=Approve action plan');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};
