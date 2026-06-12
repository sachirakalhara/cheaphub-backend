<?php

namespace App\Console\Commands;

use App\Models\Product\Bulk\BulkProduct;
use App\Models\Subscription\Subscription;
use App\Models\User\User;
use App\Notifications\LowStockNotification;
use Illuminate\Console\Command;

class CheckLowStock extends Command
{
    protected $signature = 'app:check-low-stock {--threshold= : Override the LOW_STOCK_THRESHOLD env value}';

    protected $description = 'Check all products for low stock and notify admins';

    public function handle()
    {
        $threshold = $this->option('threshold') ?? (int) config('app.low_stock_threshold', 5);
        $threshold = (int) $threshold;

        $this->info("Checking stock levels (threshold: {$threshold})...");

        $admins = User::role('super_admin')->get();

        if ($admins->isEmpty()) {
            $this->warn('No super_admin users found to notify.');
            return;
        }

        $lowStockItems = [];

        $bulkProducts = BulkProduct::where('bulk_type', 'serial_based')
            ->where('serial_count', '<=', $threshold)
            ->where('visibility', true)
            ->get();

        foreach ($bulkProducts as $product) {
            $lowStockItems[] = [
                'name' => $product->name,
                'type' => 'Bulk Product',
                'stock' => $product->serial_count,
                'id' => $product->id,
            ];
        }

        $subscriptions = Subscription::where('available_serial_count', '<=', $threshold)->get();

        foreach ($subscriptions as $subscription) {
            $lowStockItems[] = [
                'name' => $subscription->name,
                'type' => 'Subscription',
                'stock' => $subscription->available_serial_count,
                'id' => $subscription->id,
            ];
        }

        if (empty($lowStockItems)) {
            $this->info('All products have sufficient stock.');
            return;
        }

        $this->warn(count($lowStockItems) . ' product(s) with low stock:');

        foreach ($lowStockItems as $item) {
            $this->line("  - {$item['name']} ({$item['type']}): {$item['stock']} remaining");

            foreach ($admins as $admin) {
                $admin->notify(new LowStockNotification(
                    $item['name'],
                    $item['type'],
                    $item['stock'],
                    $item['id']
                ));
            }
        }

        $this->info('Notifications sent to ' . $admins->count() . ' admin(s).');
    }
}
