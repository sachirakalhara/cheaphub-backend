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
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('contribution_product_id');
            $table->text('name');
            $table->text('serial');
            $table->integer('available_serial_count');
            $table->integer('refresh_count');
            $table->double('gateway_fee');
            $table->foreign('contribution_product_id')->references('id')->on('contribution_products');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subscriptions');
    }
};
