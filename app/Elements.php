<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Elements extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id', 'element', 'type', 'process_id'
    ];

    public $table = "elements";
}
