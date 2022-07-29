<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DriverOrder extends Model
{
    use HasFactory;

    protected $table = 'driver_orders';
    protected $primaryKey = 'driver_order_id ';
    protected $fillable = ['location_details_id', 'order_id', 'driver_id','driver_acceptance','accepted_at','picked_up','picked_up_at','shop_id'];
    // protected $hidden = ['created_at', 'updated_at'];


    public function location() {
        return $this->hasOne('App\Models\LocationDetails','location_details_id','location_details_id');
    }

    public function driver() {
        return $this->hasOne('App\Models\Driver','driver_id','driver_id');
    }

    public function shop() {
        return $this->hasOne('App\Models\ShopDetails','shop_details_id', 'shop_id');
    }
    public function order() {
        return $this->hasOne('App\Models\Order','order_id','order_id');
    }
    public function orderedAddress() {
        return $this->hasOne('App\Models\OrderAddress','order_id','order_id');
    }


}
