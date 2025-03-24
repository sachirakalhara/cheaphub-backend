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
        Schema::create('coupons', function (Blueprint $table) {
            $table->id();
            $table->string('product_type'); //'bulk', 'subscription', 'both'
            $table->decimal('discount_percentage', 5, 2); // Discount percentage
            $table->decimal('max_discount_amount', 10, 2); // Max discount allowed
            $table->string('coupon_code')->unique(); 
            $table->date('expiry_date'); 
            $table->timestamps();        
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('coupons');
    }
};
