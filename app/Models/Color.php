<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Color extends Model
{
    use SoftDeletes;
    protected $dates = ['deleted_at'];
    protected $table = 'colors';
    protected $primaryKey = 'id';
    protected $fillable = ['color_name', 'color_code', 'active_status'];
    protected $hidden = ['deleted_at', 'created_at', 'updated_at'];
}
