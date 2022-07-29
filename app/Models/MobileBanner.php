<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MobileBanner extends Model
{
    use SoftDeletes;
    protected $dates = ['deleted_at'];
    protected $table = 'banners_mobile';
    protected $primaryKey = 'id';
    protected $fillable = ['mobile_banner_name','mobile_banner_image','active_status'];
    protected $hidden = ['created_at', 'updated_at', 'deleted_at'];
}
