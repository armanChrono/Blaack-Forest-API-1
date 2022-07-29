<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Promo extends Model
{
    use SoftDeletes;
    protected $dates = ['deleted_at'];
    protected $table = 'promos';
    protected $primaryKey = 'id';
    protected $fillable = ['promo_code', 'min_value', 'discount', 'active_status'];
    protected $hidden = ['deleted_at', 'created_at', 'updated_at'];
}
