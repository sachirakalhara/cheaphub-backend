<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('coupons', function (Blueprint $table) {
            $table->boolean('is_active')->default(true)->after('expiry_date');
            $table->dateTime('scheduled_start')->nullable()->after('is_active');
            $table->dateTime('scheduled_end')->nullable()->after('scheduled_start');
            $table->boolean('campaign_email_enabled')->default(false)->after('scheduled_end');
            $table->string('campaign_audience')->nullable()->after('campaign_email_enabled');
            $table->string('campaign_subject')->nullable()->after('campaign_audience');
            $table->boolean('campaign_activation_sent')->default(false)->after('campaign_subject');
            $table->boolean('campaign_reminder_sent')->default(false)->after('campaign_activation_sent');
        });
    }

    public function down(): void
    {
        Schema::table('coupons', function (Blueprint $table) {
            $table->dropColumn([
                'is_active',
                'scheduled_start',
                'scheduled_end',
                'campaign_email_enabled',
                'campaign_audience',
                'campaign_subject',
                'campaign_activation_sent',
                'campaign_reminder_sent',
            ]);
        });
    }
};
