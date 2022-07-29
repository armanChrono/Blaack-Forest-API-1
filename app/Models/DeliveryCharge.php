<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeliveryCharge extends Model
{
    protected $table = 'delivery_charge';
    protected $primaryKey = 'delivery_charge_id';
    protected $fillable = ['distance_from', 'distance_to', 'rate', 'region_id'];
    protected $hidden = ['created_at', 'updated_at'];
    
       public function location() {
        return $this->hasOne('App\Models\LocationDetails','region_id','region_id');
    }
}
