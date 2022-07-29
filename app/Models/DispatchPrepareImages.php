<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DispatchPrepareImages extends Model
{
    use HasFactory;
    protected $table = 'dispatch_prepare_image';
    protected $primaryKey = 'dispatch_prepare_image_id';
    protected $fillable = ['dispatch_order_id', 'approve_status', 'comments','admin_accepted_at', 'product_id', 'image'];
    protected $hidden = ['created_at', 'updated_at'];

    public function product() {
        return $this->hasOne('App\Models\Product','id','product_id');
    }

    public function dispatchOrder() {
        return $this->hasOne('App\Models\DispatchOrders','dispatch_order_id','dispatch_order_id');
    }
}
