<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Companies extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id', 'name', 'signature', 'ceo', 'workforce', 'field', 'address', 'contact', 'registration_id'
    ];

    public $table = "companies";

    public $timestamps = false;
}