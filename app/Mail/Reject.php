<?php

namespace App\Mail;

use App\Systems;
use App\Waitings;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class Reject extends Mailable
{
    use Queueable, SerializesModels;

    public $reason;

    public $system;

    public $registration;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($reason, Waitings $registration, Systems $system)
    {
        $this->reason = $reason;
        $this->system = $system;
        $this->registration = $registration;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('emails.reject')->with([
            'reason' => $this->reason,
            'sender' => $this->system->username,
            'recipient' => $this->registration->ceo,
        ]);
    }
}
