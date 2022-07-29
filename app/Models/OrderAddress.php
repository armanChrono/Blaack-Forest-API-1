<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderAddress extends Model
{
    protected $table = 'order_address';
    protected $primaryKey = 'order_address_id';
    protected $fillable = ['order_id', 'billing_name', 'billing_mobile','address_pincode', 'doorNo', 'street', 'area', 'address_locality_town', 'address_city_district', 'address_state', 'address_type'];
    protected $hidden = ['created_at', 'updated_at'];
}
