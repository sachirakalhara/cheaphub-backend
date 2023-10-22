<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class MailQueue extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public $details = [];

    public function __construct($details)
    {
        $this->details = $details;

        // dd($details['user']->first_name);
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->from(env("FROM_EMAIL"), env("APP_NAME"))
            ->subject($this->details['subject'])
            ->view('emails.'.$this->details['template']);
    }
}
