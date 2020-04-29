<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Accounts extends Model
{
    protected $fillable = [
        'username', 'password', 'initial_password', 'auth_token', 'provider', 'token','employee_id'
    ];
    public $table = "accounts";
}
