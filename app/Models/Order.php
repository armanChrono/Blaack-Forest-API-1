<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Order extends Model
{
    protected $table = 'orders';
    protected $primaryKey = 'order_id';
    protected $fillable = ['customer_id', 'billing_name','region_id','billing_mobile_number', 'order_sub_total', 'deliver_fee', 'cgst_tax', 'cgst_value', 'sgst_tax', 'sgst_value', 'promo_code', 'promo_code_value', 'order_overall_totall', 'payment_mode', 'order_status', 'order_submitted_at', 'order_processed_at', 'order_shipped_at', 'order_delivered_at', 'razorpay_payment_id', 'order_tracking_id','push_to_dispatch','push_to_dispatched_at','contact_mobile','expected_delivery','slot', 'delivery_mode', 'shop_id', 'hold', 'order_cancelled_at', 'hold_reason', 'cancel_reason', 'email', 'cancelled_by', 'hold_by', 'paid', 'paid_at','invoice_no'];
     protected $hidden = ['created_at', 'updated_at'];
    //protected $appends = ['expected_delivery'];


    public function getCreatedAtAttribute($date)
    {
        return Carbon::parse($date)->format('Y-M-d g:i A');
    }

    public function getUpdatedAtAttribute($date)
    {
        return Carbon::parse($date)->format('Y-M-d g:i A');
    }

    // public function getExpectedDeliveryAttribute()
    // {
    //     return Carbon::parse($this->created_at)->addDays(10)->format('d-M-Y');
    // }

    public function orderedAddress() {
        return $this->hasOne('App\Models\OrderAddress','order_id','order_id');
    }
    public function orderedAddons() {
        return $this->hasMany('App\Models\OrderAddons','order_id','order_id');
    }

    public function  dispatchOrders(){
        return $this->hasOne('App\Models\DispatchOrders','order_id','order_id');
    }

    public function  shops(){
        return $this->hasOne('App\Models\ShopDetails','shop_details_id','shop_id');

    }

    public function orderedProducts() {
        return $this->hasMany('App\Models\OrderProducts','order_id','order_id');
    }

    public function  region() {
        return $this->hasOne('App\Models\Region','region_id','region_id');
    }

    public function  orderGstMerge() {
        return $this->hasOne('App\Models\OrderGstMerge','order_id','order_id');
    }

    public function customerDetails() {
        return $this->belongsTo('App\Models\Customer','customer_id','customer_id');
    }
}
