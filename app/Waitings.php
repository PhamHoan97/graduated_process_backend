<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Waitings extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id', 'name', 'signature', 'ceo', 'workforce', 'field', 'address', 'contact', 'approve'
    ];

    public $table = "waitings";

    public $timestamps = false;
}
