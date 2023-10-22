<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;


class MailQueueWithAttachment extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */

    public $details = [];
    public $attachment = [];

    public function __construct($details, $attachment)
    {
        $this->details = $details;
        $this->attachment = $attachment;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->from(env("FROM_EMAIL"), env("APP_NAME"))
            ->view('emails.'.$this->details['template'])
            ->subject($this->details['subject'])
            ->attach(public_path('storage/'. $this->attachment['doc_path']), [
                'as' => $this->attachment['doc_name'],
                'mime' => 'application/pdf',
            ]);

    }
}
