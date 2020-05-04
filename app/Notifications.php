<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Notifications extends Model
{
    protected $fillable = [
        'name','description','status','update_at','template_id','system_id'
    ];

    public $table = "notifications";

    public $timestamps = false;
}
