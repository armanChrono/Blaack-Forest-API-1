<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderProducts extends Model
{
    protected $table = 'order_products';
    protected $primaryKey = 'order_product_id';
    protected $fillable = ['order_id', 'product_id', 'product_price', 'product_discount_price', 'product_quantity', 'product_total', 'product_size_id', 'flavour_id', 'variation_id', 'message_on_cake','egg_or_eggless'];
    protected $hidden = ['created_at', 'updated_at'];

    public function productDetails() {
        return $this->hasMany('App\Models\Product','id','product_id');
    }

    public function size() {
        return $this->hasOne('App\Models\Size','id','product_size_id');
    }

    public function flavour(){
        return $this->hasOne('App\Models\Flavour','flavour_id','flavour_id');
    }

    public function variation(){
        return $this->hasOne('App\Models\product_variation','variation_id','variation_id');
    }
}
