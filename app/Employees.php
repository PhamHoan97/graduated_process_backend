<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
class Employees extends Model
{
    protected $fillable = [
        'name', 'address', 'phone', 'birth', 'avatar', 'role', 'department_id'
    ];

    public $table = "employees";

    public $timestamps = false;

    public function processes(){
        return $this->hasMany('App\Processes', 'employee_id');
    }
}
