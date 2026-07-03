<?php

namespace App\Services;

use App\Models\Coupon\Coupon;
use App\Models\User\User;
use App\Notifications\CouponCampaignNotification;
use Illuminate\Support\Facades\Log;

class CouponCampaignService
{
    /**
     * Send a coupon campaign email to its configured audience.
     *
     * Single source of truth for the recipient query — previously this
     * logic was duplicated verbatim in ProcessCouponSchedules and
     * CouponRepository; both now delegate here.
     *
     * Audiences:
     *  - (default / 'all_customers'): every active non-admin user
     *  - 'purchased_customers': at least one paid/completed order
     *  - 'inactive_customers' (win-back): purchased before, but no
     *    paid/completed order within the last campaign_inactive_days days
     */
    public static function send(Coupon $coupon, bool $isReminder): void
    {
        $query = User::whereDoesntHave('roles', function ($q) {
            $q->where('name', 'super_admin');
        })->where('active', 1);

        if ($coupon->campaign_audience === 'purchased_customers') {
            $query->whereHas('order', function ($q) {
                $q->whereIn('payment_status', ['paid', 'completed']);
            });
        } elseif ($coupon->campaign_audience === 'inactive_customers') {
            $days = (int) ($coupon->campaign_inactive_days ?: 60);

            $query->whereHas('orders', function ($q) {
                $q->whereIn('payment_status', ['paid', 'completed']);
            })->whereDoesntHave('orders', function ($q) use ($days) {
                $q->whereIn('payment_status', ['paid', 'completed'])
                    ->where('created_at', '>=', now()->subDays($days));
            });
        }

        $query->chunk(50, function ($customers) use ($coupon, $isReminder) {
            foreach ($customers as $customer) {
                try {
                    $customer->notify(new CouponCampaignNotification($coupon, $isReminder));
                } catch (\Exception $e) {
                    Log::error("Failed to send coupon email to user #{$customer->id} for coupon #{$coupon->id}: {$e->getMessage()}");
                }
            }
        });
    }
}
