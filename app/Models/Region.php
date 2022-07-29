<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Region extends Model
{
    use SoftDeletes;
    protected $dates = ['deleted_at'];
    protected $table = 'region';
    protected $primaryKey = 'region_id';
    protected $fillable = ['country_id', 'state_id', 'city_id', 'address', 'active_status'];
    protected $hidden = ['deleted_at', 'created_at', 'updated_at'];

    public function country() {
        return $this->hasOne('App\Models\Countries','id','country_id')->select('id','name');
    }

    public function state() {
        return $this->hasOne('App\Models\States','id','state_id')->select('id','name');
    }

    public function City() {
     //return $this->hasOne('App\Models\Cities','id','city_id')->select('id','name');
    return $this->hasOne('App\Models\Cities','id','city_id')->select('id','name');
       
    


    }

    
}
