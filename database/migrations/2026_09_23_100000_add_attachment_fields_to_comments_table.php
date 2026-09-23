<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Optional image attachment on a ticket chat message. All nullable, so
     * every existing text-only message is unaffected.
     */
    public function up(): void
    {
        Schema::table('comments', function (Blueprint $table) {
            $table->string('attachment_url')->nullable()->after('message');
            $table->string('attachment_type')->nullable()->after('attachment_url');
            $table->unsignedInteger('attachment_size')->nullable()->after('attachment_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('comments', function (Blueprint $table) {
            $table->dropColumn(['attachment_url', 'attachment_type', 'attachment_size']);
        });
    }
};
