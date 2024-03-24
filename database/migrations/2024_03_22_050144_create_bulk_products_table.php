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
        Schema::create('bulk_products', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('tag_id');
            $table->foreign('tag_id')->references('id')->on('tags');
            $table->string('name');
            $table->text('description')->nullable();
            $table->double('price');
            $table->double('gateway_fee');
            $table->text('image');
            $table->text('payment_method');
            $table->text('serial');
            $table->integer('serial_count');
            $table->integer('minimum_quantity');
            $table->integer('maximum_quantity')->nullable();
            $table->text('service_info')->nullable();
            $table->text('slug_url');
            $table->text('visibility')->nullable();
            $table->timestamps();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bulk_products');
    }
};
