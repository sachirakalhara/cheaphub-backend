<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Tracks when the abandoned-cart reminder email was last sent for a
     * cart. NULL = never sent. If the cart's updated_at moves past this
     * timestamp (customer came back and changed the cart), the cart
     * becomes eligible for another reminder.
     */
    public function up(): void
    {
        Schema::table('carts', function (Blueprint $table) {
            $table->timestamp('abandoned_email_sent_at')->nullable()->after('coupon_code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('carts', function (Blueprint $table) {
            $table->dropColumn('abandoned_email_sent_at');
        });
    }
};
