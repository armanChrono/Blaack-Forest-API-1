<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Disclaimer extends Model
{
    use SoftDeletes;
    protected $dates = ['deleted_at'];
    protected $table = 'disclaimer';
    protected $primaryKey = 'disclaimer_id';
    protected $fillable = ['disclaimer'];
    protected $hidden = ['deleted_at', 'created_at', 'updated_at'];
}
