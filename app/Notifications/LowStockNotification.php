<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class LowStockNotification extends Notification
{
    use Queueable;

    protected string $productName;
    protected string $productType;
    protected int $remainingStock;
    protected int $productId;

    public function __construct(string $productName, string $productType, int $remainingStock, int $productId)
    {
        $this->productName = $productName;
        $this->productType = $productType;
        $this->remainingStock = $remainingStock;
        $this->productId = $productId;
    }

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $subject = $this->remainingStock === 0
            ? "OUT OF STOCK - {$this->productName}"
            : "Low Stock Alert - {$this->productName}";

        $message = (new MailMessage)
            ->subject($subject)
            ->greeting('Hi ' . ($notifiable->display_name ?? 'Admin') . ',');

        if ($this->remainingStock === 0) {
            $message->line("**{$this->productName}** ({$this->productType}) is now **out of stock**.")
                ->line('Customers will not be able to purchase this product until stock is replenished.');
        } else {
            $message->line("**{$this->productName}** ({$this->productType}) is running low on stock.")
                ->line("**Remaining stock:** {$this->remainingStock} serial(s)");
        }

        return $message->line('Please restock this product as soon as possible.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => $this->remainingStock === 0 ? 'Out of Stock' : 'Low Stock Alert',
            'message' => "{$this->productName} ({$this->productType}) has {$this->remainingStock} serial(s) remaining.",
            'product_id' => $this->productId,
            'product_type' => $this->productType,
            'remaining_stock' => $this->remainingStock,
            'icon' => 'exclamation-triangle',
        ];
    }
}
