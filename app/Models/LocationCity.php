<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LocationCity extends Model
{
    protected $table = 'location_cities';
    public $timestamps = false;

    public function state() {
        return $this->hasOne('App\Models\LocationState','id','state_id');
    }
}
