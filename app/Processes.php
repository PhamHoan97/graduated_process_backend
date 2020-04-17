<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Processes extends Model
{
    protected $fillable = [
        'name', 'description', 'image', 'svg', 'bpmn', 'xml', 'update_at','employee_id'
    ];

    public $table = "processes";

    public $timestamps = false;

}
