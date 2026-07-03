<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AbandonedCartNotification extends Notification
{
    use Queueable;

    protected $cart;

    public function __construct($cart)
    {
        $this->cart = $cart;
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $storeUrl = rtrim(config('app.client_url'), '/') . '/store';
        $name = $notifiable->fname ?: $notifiable->display_name ?: 'there';

        $mail = (new MailMessage)
            ->subject('You left something in your cart 🛒')
            ->greeting('Hi ' . $name . ',')
            ->line('You have items waiting in your cart at CheapHub:');

        foreach ($this->cart->cartItems as $item) {
            $productName = null;
            if ($item->bulkProduct) {
                $productName = $item->bulkProduct->name;
            } elseif ($item->package) {
                $productName = optional(optional($item->package->subscription)->contributionProduct)->name
                    ?: optional($item->package->subscription)->name
                    ?: $item->package->name;
            }

            if ($productName) {
                $mail->line('• **' . $productName . '** × ' . $item->quantity);
            }
        }

        $mail->line('Complete your order now — your items are reserved in your cart and ready for instant delivery.')
            ->action('Complete Your Order', $storeUrl)
            ->line('If you had any trouble checking out, just reply to this email — we\'re happy to help!');

        return $mail;
    }
}
