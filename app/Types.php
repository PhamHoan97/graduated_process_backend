<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Types extends Model
{
    protected $fillable = [
        'name'
    ];

    public $table = "types";

    public $timestamps = false;
}
