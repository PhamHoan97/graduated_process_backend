<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Isos extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id', 'name', 'year', 'content', 'name_download', 'download', 'created_at', 'updated_at'
    ];

    public $table = "isos";
}
