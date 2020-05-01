<?php

namespace App\Mail;

use App\Accounts;
use App\Employees;
use App\Admins;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
class SendEmployeeAccount extends Mailable
{
    use Queueable, SerializesModels;

    public $account;

    public $admin;

    public $employee;

    /**
     * Create a new message instance.
     *
     * @param Accounts $account
     * @param Admins $admin
     * @param Employees $employee
     */
    public function __construct(Accounts $account, Admins $admin, Employees $employee)
    {
        $this->account = $account;
        $this->admin = $admin;
        $this->employee = $employee;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('emails.send_employee_account')->with([
            'username' => $this->account->username,
            'password' => $this->account->initial_password,
            'sender' => $this->admin->username,
            'recipient' => $this->employee->name,
        ]);
    }
}
