<?php

namespace App\Console\Commands;

use App\Models\Cart\Cart;
use App\Notifications\AbandonedCartNotification;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SendAbandonedCartEmails extends Command
{
    protected $signature = 'app:send-abandoned-cart-emails {--hours= : Override the ABANDONED_CART_HOURS env value}';

    protected $description = 'Email customers whose cart has sat untouched longer than the configured number of hours';

    public function handle()
    {
        $hours = $this->option('hours') ?? config('app.abandoned_cart_hours', 24);
        $hours = (int) $hours;
        $cutoff = Carbon::now()->subHours($hours);

        $this->info("Sending abandoned-cart emails for carts idle since {$cutoff} ({$hours}h)...");

        $sent = 0;

        Cart::has('cartItems')
            ->where('updated_at', '<=', $cutoff)
            ->where(function ($q) {
                // Never emailed, OR the cart changed after the last email
                // (customer came back) — eligible for another reminder.
                $q->whereNull('abandoned_email_sent_at')
                    ->orWhereColumn('abandoned_email_sent_at', '<', 'updated_at');
            })
            ->whereHas('user', function ($q) {
                $q->where('active', 1)
                    ->whereDoesntHave('roles', function ($r) {
                        $r->where('name', 'super_admin');
                    });
            })
            ->with(['user', 'cartItems.bulkProduct', 'cartItems.package.subscription.contributionProduct'])
            ->chunkById(50, function ($carts) use (&$sent) {
                foreach ($carts as $cart) {
                    try {
                        $cart->user->notify(new AbandonedCartNotification($cart));
                        // Don't bump updated_at when recording the send, or the
                        // cart would immediately look "changed" and re-arm itself.
                        $cart->timestamps = false;
                        $cart->abandoned_email_sent_at = now();
                        $cart->save();
                        $cart->timestamps = true;
                        $sent++;
                    } catch (\Exception $e) {
                        Log::error("Failed to send abandoned-cart email for cart #{$cart->id} (user #{$cart->user_id}): {$e->getMessage()}");
                    }
                }
            });

        if ($sent > 0) {
            $this->warn("{$sent} abandoned-cart email(s) sent.");
        } else {
            $this->info('No abandoned carts found.');
        }
    }
}
