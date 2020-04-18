<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Roles extends Model
{
    protected $fillable = [
        'name'
    ];

    public $table = "roles";

    public $timestamps = false;

    public function employees(){
        return $this->hasMany('App\Employees', 'role_id');
    }
}
