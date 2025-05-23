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
        Schema::create('removed_product_replacement_serials', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('removed_contribution_product_serial_id');
            $table->unsignedBigInteger('product_replacement_serial_id');

            // Add foreign keys with shorter constraint names
            $table->foreign('removed_contribution_product_serial_id', 'fk_removed_contribution_id')
                  ->references('id')
                  ->on('removed_contribution_product_serials');

            $table->foreign('product_replacement_serial_id', 'fk_product_replacement_id')
                  ->references('id')
                  ->on('product_replacement_serials');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('removed_product_replacement_serials');
    }
};
