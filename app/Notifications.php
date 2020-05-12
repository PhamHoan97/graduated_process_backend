<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Notifications extends Model
{
    protected $fillable = [
        'name','description','file','status','update_at','form_id'
    ];

    public $table = "notifications";

    public $timestamps = false;
}
