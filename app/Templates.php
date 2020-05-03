<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Templates extends Model
{
    protected $fillable = [
        'name','content','type_id'
    ];

    public $table = "templates";

    public $timestamps = false;
}
