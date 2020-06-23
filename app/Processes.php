<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use phpDocumentor\Reflection\Element;

class Processes extends Model
{
    protected $fillable = [
        'code' ,'name', 'description', 'xml', 'type', 'collabration', 'deadline', 'update_at','admin_id', 'document', 'is_delete'
    ];

    public $table = "processes";

    public function employees(){
        return $this->belongsToMany('\App\Employees','processes_employees', 'process_id', 'employee_id');
    }

    public function elementNotes()
    {
        return $this->hasManyThrough('\App\ElementNotes', '\App\Elements','process_id', 'element_id');
    }

    public function elementComments(){
        return $this->hasManyThrough('\App\ElementComments', '\App\Elements', 'process_id', 'element_id');
    }

    public function elements(){
        return $this->hasMany('App\Elements', 'process_id', 'id');
    }

    public function roles(){
        return $this->belongsToMany('\App\Roles','processes_roles', 'process_id', 'role_id');
    }

    public function departments(){
        return $this->belongsToMany('\App\Departments','processes_departments', 'process_id', 'department_id');
    }

    public function templates(){
        return $this->hasMany('App\ProcessesTemplates', 'process_id', 'id');
    }
}
