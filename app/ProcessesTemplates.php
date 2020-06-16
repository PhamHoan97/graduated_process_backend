<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ProcessesTemplates extends Model
{
    protected $fillable = [
        'id', 'name', 'link', 'process_id',
    ];

    public $table = "processes_templates";
}
