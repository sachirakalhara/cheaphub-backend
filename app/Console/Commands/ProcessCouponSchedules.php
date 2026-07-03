<?php

namespace App\Console\Commands;

use App\Models\Coupon\Coupon;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProcessCouponSchedules extends Command
{
    protected $signature = 'app:process-coupon-schedules';
    protected $description = 'Activate/deactivate scheduled coupons and send campaign emails';

    public function handle()
    {
        $now = Carbon::now();

        $this->activateScheduledCoupons($now);
        $this->deactivateExpiredCoupons($now);
        $this->sendReminderEmails($now);
    }

    private function activateScheduledCoupons(Carbon $now): void
    {
        $coupons = Coupon::where('scheduled_start', '<=', $now)
            ->where('is_active', false)
            ->whereNotNull('scheduled_start')
            ->get();

        foreach ($coupons as $coupon) {
            DB::transaction(function () use ($coupon) {
                $coupon->update(['is_active' => true]);
                Log::info("Coupon #{$coupon->id} ({$coupon->coupon_code}) activated by schedule.");
            });

            if ($coupon->campaign_email_enabled && !$coupon->campaign_activation_sent) {
                $this->sendCampaignEmails($coupon, false);
                $coupon->update(['campaign_activation_sent' => true]);
                Log::info("Activation email campaign sent for coupon #{$coupon->id} ({$coupon->coupon_code}).");
            }
        }

        if ($coupons->count() > 0) {
            $this->info("Activated {$coupons->count()} coupon(s).");
        }
    }

    private function deactivateExpiredCoupons(Carbon $now): void
    {
        $count = Coupon::where('scheduled_end', '<=', $now)
            ->where('is_active', true)
            ->whereNotNull('scheduled_end')
            ->update(['is_active' => false]);

        if ($count > 0) {
            Log::info("Deactivated {$count} coupon(s) by schedule.");
            $this->info("Deactivated {$count} coupon(s).");
        }
    }

    private function sendReminderEmails(Carbon $now): void
    {
        $reminderStart = $now->copy()->addDays(2)->startOfMinute();
        $reminderEnd = $reminderStart->copy()->addMinutes(5);

        $coupons = Coupon::where('is_active', true)
            ->where('campaign_email_enabled', true)
            ->where('campaign_reminder_sent', false)
            ->whereNotNull('scheduled_end')
            ->whereBetween('scheduled_end', [$reminderStart, $reminderEnd])
            ->get();

        foreach ($coupons as $coupon) {
            $this->sendCampaignEmails($coupon, true);
            $coupon->update(['campaign_reminder_sent' => true]);
            Log::info("Reminder email campaign sent for coupon #{$coupon->id} ({$coupon->coupon_code}).");
        }

        if ($coupons->count() > 0) {
            $this->info("Sent reminder emails for {$coupons->count()} coupon(s).");
        }
    }

    private function sendCampaignEmails(Coupon $coupon, bool $isReminder): void
    {
        \App\Services\CouponCampaignService::send($coupon, $isReminder);
    }
}
