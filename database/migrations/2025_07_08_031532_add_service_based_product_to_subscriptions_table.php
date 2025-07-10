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
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->string('service_type')->default('serial_based');//service_based
            $table->longText('serial')->nullable()->change();
            $table->integer('available_serial_count')->default(0)->change();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->dropColumn('service_type');
            $table->longText('serial')->nullable(false)->change();
            $table->integer('available_serial_count')->change();
        });
    }
};
