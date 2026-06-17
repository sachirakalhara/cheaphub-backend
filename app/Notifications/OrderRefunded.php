<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OrderRefunded extends Notification
{
    use Queueable;

    protected $order;
    protected $refundType;
    protected $refundAmount;

    public function __construct($order, $refundType, $refundAmount)
    {
        $this->order = $order;
        $this->refundType = $refundType;
        $this->refundAmount = $refundAmount;
    }

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $url = rtrim(config('app.client_url'), '/') . '/order-details/' . $this->order->id;
        $amount = '$' . number_format($this->refundAmount, 2);

        $mail = (new MailMessage)
            ->subject('Refund Processed - Order #' . $this->order->order_id)
            ->greeting('Hi ' . $notifiable->display_name . ',')
            ->line('Your order **#' . $this->order->order_id . '** has been refunded.')
            ->line('**Refund Amount:** ' . $amount);

        if ($this->refundType === 'wallet') {
            $mail->line('The amount has been credited to your CheapHub wallet balance and is ready to use right away.');
        } else {
            $mail->line('The refund is being processed back to your original payment method. Depending on your provider, it may take a few business days to appear.');
        }

        return $mail
            ->action('View Order', $url)
            ->line('If you have any questions, please reach out to our support team.');
    }

    public function toArray(object $notifiable): array
    {
        $url = rtrim(config('app.client_url'), '/') . '/order-details/' . $this->order->id;

        return [
            'title' => 'Order Refunded',
            'message' => 'Order #' . $this->order->order_id . ' has been refunded.',
            'order_id' => $this->order->id,
            'refund_type' => $this->refundType,
            'refund_amount' => $this->refundAmount,
            'url' => $url,
            'icon' => 'refresh-cw',
        ];
    }
}
