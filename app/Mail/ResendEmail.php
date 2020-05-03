<?php

namespace App\Mail;

use App\Emails;
use App\Systems;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class ResendEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $email;

    public $system;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(Emails $email, Systems $system)
    {
        $this->email = $email;
        $this->system = $system;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        if($this->email->type === 'Send Account'){
            $content = json_decode($this->email->content);
            return $this->view('emails.send_admin_account')->with([
                'username' => $content->username,
                'password' => $content->password,
                'sender' => $this->system->username,
                'recipient' => $content->recipientName,
            ]);
        }else if($this->email->type === 'Reject'){
            return $this->view('emails.reject')->with([
                'reason' => $this->email->reason,
                'sender' => $this->system->username,
                'recipient' => "Buddy",
            ]);
        }
    }
}
