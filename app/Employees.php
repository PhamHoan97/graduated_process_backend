<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
class Employees extends Model
{
    protected $fillable = [
        'name', 'email','address', 'phone', 'birth', 'about_me', 'avatar', 'role', 'department_id'
    ];

    public $table = "employees";

    public $timestamps = false;

    public function processes(){
        return $this->belongsToMany('\App\Processes','processes_employees', 'employee_id', 'process_id');
    }

    public function role(){
        return $this->belongsTo('App\Roles', 'role_id', 'id');
    }
}
