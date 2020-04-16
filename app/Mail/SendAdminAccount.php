<?php

namespace App\Mail;

use App\Admins;
use App\Companies;
use App\Systems;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendAdminAccount extends Mailable
{
    use Queueable, SerializesModels;

    public $admin;

    public $system;

    public $company;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(Admins $admin, Systems $system, Companies $company)
    {
        $this->admin = $admin;
        $this->system = $system;
        $this->company = $company;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('emails.send_admin_account')->with([
            'username' => $this->admin->username,
            'password' => $this->admin->password,
            'sender' => $this->system->username,
            'recipient' => $this->company->ceo,
        ]);
    }
}
