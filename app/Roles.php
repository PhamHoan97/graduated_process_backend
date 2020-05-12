<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Roles extends Model
{
    protected $fillable = [
        'name','description','is_process','department_id'
    ];

    public $table = "roles";

    public $timestamps = false;

    public function employees(){
        return $this->hasMany('App\Employees', 'role_id');
    }

    /**
     * Scope a query to only include users of a given type.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  mixed  $nameSearch
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeName($query, $nameSearch)
    {
        if (!empty($nameSearch)) {
            $query->where('roles.name', 'LIKE', '%' . $nameSearch . '%');
        }

        return $query;
    }


    /**
     * Scope a query to only include users of a given type.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  mixed  $idDepartment
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeDepartment($query, $idDepartment)
    {
        if ((int)$idDepartment !== 0) {
            $query->where('roles.department_id', $idDepartment);
        }
        return $query;
    }

}
