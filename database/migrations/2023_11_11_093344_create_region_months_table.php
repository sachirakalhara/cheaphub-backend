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
        Schema::create('region_months', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('month_id')->nullable();
            $table->unsignedBigInteger('region_id')->nullable();
            $table->foreign('month_id')->references('id')->on('months');
            $table->foreign('region_id')->references('id')->on('regions');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('region_months');
    }
};
