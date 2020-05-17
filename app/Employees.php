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

    public function processesEmployees(){
        return $this->belongsToMany('\App\Processes','processes_employees', 'employee_id', 'process_id');
    }

    public function role(){
        return $this->belongsTo('App\Roles', 'role_id', 'id');
    }

    public function processesRoles()
    {
        return $this->belongsToMany('\App\Processes', 'processes_roles', 'role_id', 'process_id');
    }

    /**
     *
     * Scope a query to only include users of a given type.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  mixed  $nameSearch
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeName($query, $nameSearch)
    {
        if (!empty($nameSearch)) {
            $query->where('employees.name', 'LIKE', "%{$nameSearch}%");
        }

        return $query;
    }


    /**
     * Scope a query to only include users of a given type.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  mixed  $searchEmail
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeEmail($query, $searchEmail)
    {
        if (!empty($searchEmail)) {
            $query->where('employees.email','LIKE', "%{$searchEmail}%");
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
            $query->where('employees.department_id', $idDepartment);
        }
        return $query;

    }
}
