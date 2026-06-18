<?php

namespace App\Console\Commands;

use App\Models\Payment\Order;
use Carbon\Carbon;
use Illuminate\Console\Command;

class CancelPendingOrders extends Command
{
    protected $signature = 'app:cancel-pending-orders {--timeout= : Override the PENDING_ORDER_TIMEOUT_MINUTES env value}';

    protected $description = 'Cancel orders that have been in pending status longer than the configured timeout';

    public function handle()
    {
        $timeout = $this->option('timeout') ?? (int) config('app.pending_order_timeout_minutes', 60);
        $timeout = (int) $timeout;
        $cutoff = Carbon::now()->subMinutes($timeout);

        $this->info("Canceling pending orders older than {$timeout} minutes (before {$cutoff})...");

        $canceled = Order::where('payment_status', 'pending')
            ->where('created_at', '<=', $cutoff)
            ->update(['payment_status' => 'canceled']);

        if ($canceled > 0) {
            $this->warn("{$canceled} order(s) canceled.");
        } else {
            $this->info('No stale pending orders found.');
        }
    }
}
