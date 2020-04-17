<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    protected $fillable = [
        'name', 'signature', 'address', 'field', 'workforce', 'ceo', 'contact'
    ];

    public $table = "companies";

    public $timestamps = false;

    public function departments(){
        return $this->hasMany('App\Departments', 'company_id');
    }
}
