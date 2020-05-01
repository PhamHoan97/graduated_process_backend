<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserEmails extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id', 'type', 'to', 'content', 'time', 'admin_id'
    ];

    public $table = "user_emails";
}
