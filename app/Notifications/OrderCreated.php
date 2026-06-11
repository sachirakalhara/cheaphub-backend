<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OrderCreated extends Notification
{
    use Queueable;

    protected $order;

    public function __construct($order)
    {
        $this->order = $order;
    }

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $url = rtrim(config('app.client_url'), '/') . '/order-details/' . $this->order->id;

        return (new MailMessage)
            ->subject('Payment Confirmed - Order #' . $this->order->order_id)
            ->greeting('Hi ' . $notifiable->display_name . ',')
            ->line('Your payment for order **#' . $this->order->order_id . '** has been confirmed.')
            ->line('**Amount Paid:** $' . number_format($this->order->amount_paid, 2))
            ->action('View Order', $url)
            ->line('Your order details and serials are available on your dashboard.');
    }

    public function toArray(object $notifiable): array
    {
        $url = rtrim(config('app.client_url'), '/') . '/order-details/' . $this->order->id;

        return [
            'title' => 'Payment Confirmed',
            'message' => 'Order #' . $this->order->order_id . ' has been paid.',
            'order_id' => $this->order->id,
            'url' => $url,
            'icon' => 'shopping-cart',
        ];
    }
}
