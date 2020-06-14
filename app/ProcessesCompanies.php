<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ProcessesCompanies extends Model
{
    protected $fillable = [
        'id', 'process_id', 'company_id'
    ];

    public $table = "processes_companies";
}
