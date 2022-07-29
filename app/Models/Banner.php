<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Banner extends Model
{
    use SoftDeletes;
    protected $dates = ['deleted_at'];
    protected $table = 'banners';
    protected $primaryKey = 'id';
    protected $fillable = ['banner_name','banner_image','active_status'];
    protected $hidden = ['created_at', 'updated_at', 'deleted_at'];
}
