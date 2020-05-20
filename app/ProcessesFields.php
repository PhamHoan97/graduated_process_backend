<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ProcessesFields extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id', 'name', 'xml', 'description', 'field_id',
    ];

    public $table = "processes_fields";
}
