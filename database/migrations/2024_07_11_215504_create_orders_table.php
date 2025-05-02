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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->decimal('amount', 10, 2);
            $table->string('currency', 3);
            $table->boolean('is_wallet')->default(false);
            $table->string('description');
            $table->string('payment_status')->default('pending'); // pending, paid, failed
            $table->string('transaction_id')->nullable();
            $table->string('order_id')->unique();
            $table->decimal('amount_paid', 10, 2)->nullable();
            $table->decimal('discount', 10, 2)->nullable();
            $table->string('payment_method')->nullable();// e.g., 'credit_card', 'wallet'
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
