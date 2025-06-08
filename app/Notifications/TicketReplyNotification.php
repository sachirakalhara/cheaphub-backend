<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TicketReplyNotification extends Notification
{
    use Queueable;

    protected $ticket;
    protected $userType;

    public function __construct($ticket, $userType)
    {
        $this->ticket = $ticket;
        $this->userType = $userType;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $url = "https://cheaphub.io/tickets/chat-box/" . $this->ticket->ticket_number;

        if ($this->userType === 'admin') {
            $url = "https://admin.cheaphub.io/tickets/chat-box/" . $this->ticket->ticket_number;
        }

        return (new MailMessage)
            ->subject('New Reply on Ticket #' . $this->ticket->ticket_number)
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line('There is a new reply to ticket #' . $this->ticket->ticket_number . '.')
            ->action('View Ticket', $url)
            ->line('Thank you for staying with CheapHub!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $url = "https://cheaphub.io/tickets/chat-box/" . $this->ticket->ticket_number;

        if ($this->userType === 'admin') {
            $url = "https://admin.cheaphub.io/tickets/chat-box/" . $this->ticket->ticket_number;
        }

        return [
            'title' => 'New Ticket Reply',
            'ticket_id' => $this->ticket->id,
            'url' => $url,
            'icon' => 'reply',
        ];
    }
}
