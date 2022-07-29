<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CustomerAddress extends Model
{
    protected $dates = ['deleted_at'];
    protected $table = 'customer_address';
    protected $primaryKey = 'customer_address_id';
    protected $fillable = ['customer_id', 'billing_name', 'billing_mobile', 'address_pincode', 'doorNo', 'street', 'area', 'address_locality_town', 'address_city_district', 'address_state', 'address_type'];
    protected $hidden = ['deleted_at', 'created_at', 'updated_at'];
}
