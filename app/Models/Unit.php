<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Unit extends Model
{
    use SoftDeletes;
    protected $dates = ['deleted_at'];
    protected $table = 'units';
    protected $primaryKey = 'unit_id';
    protected $fillable = ['unit_name', 'active_status'];
    protected $hidden = ['deleted_at', 'created_at', 'updated_at'];
}
