<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ProcessesDepartments extends Model
{
    protected $fillable = [
        'id', 'process_id', 'department_id'
    ];

    public $table = "processes_departments";
}
