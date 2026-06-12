<?php

namespace App\Services;

use App\Models\User\User;
use App\Notifications\LowStockNotification;
use Illuminate\Support\Facades\Log;

class StockNotificationService
{
    public static function checkAndNotify(string $productName, string $productType, int $newStock, int $oldStock, int $productId): void
    {
        $threshold = (int) env('LOW_STOCK_THRESHOLD', 5);

        if ($threshold <= 0) {
            return;
        }

        $shouldNotify = false;

        if ($oldStock > $threshold && $newStock <= $threshold) {
            $shouldNotify = true;
        }

        if ($oldStock > 0 && $newStock === 0) {
            $shouldNotify = true;
        }

        if (!$shouldNotify) {
            return;
        }

        try {
            $admins = User::role('super_admin')->get();

            foreach ($admins as $admin) {
                $admin->notify(new LowStockNotification($productName, $productType, $newStock, $productId));
            }

            Log::info("Low stock notification sent", [
                'product' => $productName,
                'type' => $productType,
                'remaining' => $newStock,
            ]);
        } catch (\Exception $e) {
            Log::error("Failed to send low stock notification: " . $e->getMessage());
        }
    }
}
