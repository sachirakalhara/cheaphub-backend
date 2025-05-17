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
        Schema::create('product_replacement_serials', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_replacement_id');
            $table->foreign('product_replacement_id')->references('id')->on('product_replacements');
            $table->longText('serial');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_replacement_serials');
    }
};