<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ElementNotes extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id', 'element_id', 'admin_id', 'content', 'update_at'
    ];

    public $table = "element_notes";
}
