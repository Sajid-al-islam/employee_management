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
        Schema::create('employee_details', function (Blueprint $table) {
            $table->id();
            $table->uuid('employee_id');
            $table->foreign('employee_id')->references('id')->on('employees');
            $table->string('designation');
            $table->float('salary');
            $table->string('address');
            $table->date('joined_date');
            $table->timestamps();

            $table->index('joined_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_details');
    }
};
