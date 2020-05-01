<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ElementComments extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id', 'element_id', 'admin_id', 'employee_id', 'comment', 'update_at'
    ];

    public $table = "element_comments";
}
