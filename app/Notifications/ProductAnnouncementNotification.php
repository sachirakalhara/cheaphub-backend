<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ProductAnnouncementNotification extends Notification
{
    use Queueable;

    protected $products;
    protected $announcementType;
    protected $subject;

    public function __construct(array $products, string $announcementType, string $subject)
    {
        $this->products = $products;
        $this->announcementType = $announcementType;
        $this->subject = $subject;
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $name = $notifiable->fname ?: $notifiable->display_name ?: 'there';
        $isMultiple = count($this->products) > 1;

        if ($this->announcementType === 'new_product') {
            $headline = $isMultiple ? 'New Products Just Landed!' : 'New Product Just Landed!';
            $subheading = "We've added something new to our store. Check it out before it sells out.";
        } else {
            $headline = $isMultiple ? "They're Back in Stock!" : 'Back in Stock!';
            $subheading = 'Good news — a product you might love is available again.';
        }

        $mail = (new MailMessage)
            ->subject($this->subject)
            ->greeting('Hi ' . $name . ',')
            ->line('**' . $headline . '**')
            ->line($subheading);

        foreach ($this->products as $product) {
            $priceText = isset($product['price']) && $product['price'] > 0
                ? ' — $' . number_format($product['price'], 2)
                : '';
            $mail->line('[' . e($product['name']) . '](' . $product['url'] . ')' . $priceText);
        }

        $storeUrl = rtrim(config('app.client_url'), '/') . '/store';
        $mail->action('Browse Store', $storeUrl)
             ->line('Happy shopping!');

        return $mail;
    }
}
