<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Tax extends Model
{
    use SoftDeletes;
    protected $dates = ['deleted_at'];
    protected $table = 'tax';
    protected $primaryKey = 'tax_id';
    protected $fillable = ['tax_percentage', 'active_status'];
    protected $hidden = ['deleted_at', 'created_at', 'updated_at'];
}
