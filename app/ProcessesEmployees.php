<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ProcessesEmployees extends Model
{
    protected $fillable = [
        'id', 'process_id', 'employee_id'
    ];

    public $table = "processes_employees";
}
