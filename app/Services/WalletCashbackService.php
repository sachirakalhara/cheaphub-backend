<?php

namespace App\Services;

use App\Models\Payment\Order;
use App\Models\Payment\Wallet;
use Illuminate\Support\Facades\Log;

class WalletCashbackService
{
    /**
     * Credit a percentage of a paid product order back to the customer's
     * wallet. Controlled by CASHBACK_PERCENT (config app.cashback_percent);
     * 0 disables the feature entirely.
     *
     * Idempotent: orders.cashback_amount is set exactly once — webhook
     * retries and duplicate callbacks can never double-credit. Callers must
     * only pass PRODUCT purchases (never wallet top-ups). Failures are
     * logged but never thrown, so a cashback problem can't break payment
     * confirmation.
     */
    public static function creditForOrder(Order $order): void
    {
        try {
            $percent = (float) config('app.cashback_percent', 0);
            if ($percent <= 0) {
                return;
            }

            if ($order->payment_status !== 'paid' || $order->cashback_amount !== null) {
                return;
            }

            $base = (float) ($order->amount_paid ?: $order->amount);
            $cashback = round($base * $percent / 100, 2);
            if ($cashback <= 0) {
                return;
            }

            $wallet = Wallet::firstOrCreate(
                ['user_id' => $order->user_id],
                ['balance' => 0, 'currency' => $order->currency ?? 'USD']
            );
            $wallet->increment('balance', $cashback);

            $order->update(['cashback_amount' => $cashback]);
        } catch (\Exception $e) {
            Log::error("Cashback credit failed for order #{$order->id}: {$e->getMessage()}");
        }
    }
}
