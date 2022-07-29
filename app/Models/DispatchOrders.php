<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class DispatchOrders extends Model
{
    use HasFactory;
    protected $table = 'dispatch_orders';
    protected $primaryKey = 'dispatch_order_id';
    protected $fillable = ['location_details_id', 'order_id', 'dispatch_order_status', 'cake_done_image', 'online_team_accept', 'scheduled_date'];
    protected $hidden = ['created_at', 'updated_at'];


    public function location() {
        return $this->hasOne('App\Models\LocationDetails','location_details_id','location_details_id');
    }


    public function order() {
        return $this->hasOne('App\Models\Order','order_id','order_id');
    }

}
