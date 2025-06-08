<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TicketNotification extends Notification
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
            ->subject('New Ticket Created - #' . $this->ticket->ticket_number)
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line('A new ticket has been created with ID #' . $this->ticket->ticket_number . '.')
            ->action('View Ticket', $url)
            ->line('Thank you for using our support system!');
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
            'title' => 'Ticket Created',
            'message' => 'Ticket #' . $this->ticket->id . ' has been created.',
            'ticket_id' => $this->ticket->id,
            'url' => $url,
            'icon' => 'ticket',
        ];
    }
}
