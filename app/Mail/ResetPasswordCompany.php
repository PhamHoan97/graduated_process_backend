<?php

namespace App\Mail;

use App\Admins;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class ResetPasswordCompany extends Mailable
{
    use Queueable, SerializesModels;
    public $admin;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(Admins $admin)
    {
        $this->admin = $admin;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('emails.forget_password_company')->with([
            'link' => 'https://processmanagement1102.herokuapp.com/company/form/reset/password/'. $this->admin->id,
        ]);
    }
}
