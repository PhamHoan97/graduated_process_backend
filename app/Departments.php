<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Departments extends Model
{
    protected $fillable = [
        'name', 'description', 'role','signature','company_id'
    ];

    public $table = "departments";

    public $timestamps = false;

    public function employees(){
        return $this->hasMany('App\Employees', 'department_id');
    }

    public function roles(){
        return $this->hasMany('App\Roles', 'department_id');
    }
}
