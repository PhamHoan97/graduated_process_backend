<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ProcessesRoles extends Model
{
    protected $fillable = [
        'id', 'process_id', 'role_id'
    ];

    public $table = "processes_roles";
}
