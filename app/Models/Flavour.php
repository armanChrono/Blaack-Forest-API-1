<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Flavour extends Model
{
    use SoftDeletes;
    protected $dates = ['deleted_at'];
    protected $table = 'flavour';
    protected $primaryKey = 'flavour_id';
    protected $fillable = ['flavour_name', 'active_status'];
    protected $hidden = ['deleted_at', 'created_at', 'updated_at'];
}
