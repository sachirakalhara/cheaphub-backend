<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OrderItemDelivered extends Notification
{
    use Queueable;

    protected $order;
    protected $productName;
    protected $deliveryContent;

    public function __construct($order, $productName, $deliveryContent)
    {
        $this->order = $order;
        $this->productName = $productName;
        $this->deliveryContent = $deliveryContent;
    }

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $url = rtrim(config('app.client_url'), '/') . '/order-details/' . $this->order->id;

        return (new MailMessage)
            ->subject('Your Service is Ready - Order #' . $this->order->order_id)
            ->greeting('Hi ' . $notifiable->display_name . ',')
            ->line('Your service for **' . $this->productName . '** (Order #' . $this->order->order_id . ') has been delivered.')
            ->line('**Service Details:**')
            ->line($this->deliveryContent)
            ->action('View Order', $url)
            ->line('Thank you for shopping with CheapHub.');
    }

    public function toArray(object $notifiable): array
    {
        $url = rtrim(config('app.client_url'), '/') . '/order-details/' . $this->order->id;

        return [
            'title' => 'Service Delivered',
            'message' => 'Your service for ' . $this->productName . ' (Order #' . $this->order->order_id . ') is ready.',
            'order_id' => $this->order->id,
            'url' => $url,
            'icon' => 'package',
        ];
    }
}
