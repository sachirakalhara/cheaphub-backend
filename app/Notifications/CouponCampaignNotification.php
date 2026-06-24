<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CouponCampaignNotification extends Notification
{
    use Queueable;

    protected $coupon;
    protected $isReminder;

    public function __construct($coupon, bool $isReminder = false)
    {
        $this->coupon = $coupon;
        $this->isReminder = $isReminder;
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $storeUrl = rtrim(config('app.client_url'), '/') . '/store';
        $subject = $this->coupon->campaign_subject ?: 'Exclusive Coupon Just for You!';

        if ($this->isReminder) {
            $subject = '⏰ Reminder: ' . $subject;
        }

        $discountText = $this->coupon->discount_percentage . '% off';
        if ($this->coupon->max_discount_amount > 0) {
            $discountText .= ' (up to $' . number_format($this->coupon->max_discount_amount, 2) . ')';
        }

        $name = $notifiable->fname ?: $notifiable->display_name ?: 'there';

        $mail = (new MailMessage)
            ->subject($subject)
            ->greeting('Hi ' . $name . ',');

        if ($this->isReminder) {
            $mail->line('Your exclusive coupon expires in 2 days — don\'t miss out!');
        } else {
            $mail->line('We have an exclusive deal just for you!');
        }

        $mail->line('Use coupon code **' . $this->coupon->coupon_code . '** to get **' . $discountText . '** on your next order.');

        if ($this->coupon->scheduled_end) {
            $mail->line('**Valid until:** ' . \Carbon\Carbon::parse($this->coupon->scheduled_end)->format('d M Y, h:i A'));
        } elseif ($this->coupon->expiry_date) {
            $mail->line('**Valid until:** ' . $this->coupon->getRawOriginal('expiry_date'));
        }

        $mail->action('Shop Now', $storeUrl)
             ->line('Happy shopping!');

        return $mail;
    }
}
