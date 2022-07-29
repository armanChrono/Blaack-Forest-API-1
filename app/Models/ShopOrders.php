<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShopOrders extends Model
{
    use HasFactory;
    protected $table = 'shop_orders';
    protected $primaryKey = 'shop_order_id';
    protected $fillable = ['shop_id', 'order_id', 'region_id', 'shop_acceptance','accepted_at', 'picked_up','picked_up_at', 'driver_id'];
    // protected $hidden = ['created_at', 'updated_at'];

    public function location() {
        return $this->hasOne('App\Models\ShopDetails','shop_details_id','shop_details_id');
    }
    public function shop() {
        return $this->hasOne('App\Models\ShopDetails','shop_details_id','shop_id');
    }
    public function driver() {
        return $this->hasOne('App\Models\Driver','driver_id','driver_id');
    }
    public function region() {
        return $this->hasOne('App\Models\Region','region_id','region_id ');
    }

    public function order() {
        return $this->hasOne('App\Models\Order','order_id','order_id');
    }
    public function driverOrder() {
        return $this->hasOne('App\Models\DriverOrder','order_id','order_id');
    }

}
