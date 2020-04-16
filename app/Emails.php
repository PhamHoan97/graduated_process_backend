<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Emails extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id', 'type', 'to', 'content', 'time', 'system_id'
    ];

    public $table = "emails";
}
