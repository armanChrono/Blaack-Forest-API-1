<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Driver extends Model
{
    use SoftDeletes;
    protected $dates = ['deleted_at'];
    protected $table = 'driver';
    protected $primaryKey = 'driver_id';
    protected $fillable = ['driver_name', 'driver_mobile', 'location_details_id','city_id','password'];
    protected $hidden = ['password','deleted_at', 'created_at', 'updated_at'];

   public function city() {
        return $this->hasOne('App\Models\Cities','id', 'city_id');
    }
}
