<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Departments extends Model
{
    protected $fillable = [
        'name', 'description', 'role', 'company_id'
    ];

    public $table = "departments";

    public $timestamps = false;

    public function employees(){
        return $this->hasMany('App\Employees', 'department_id');
    }
}
