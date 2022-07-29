<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Cart;
use App\Models\Flavour;
use App\Models\product_variation;

class Cart extends Model
{
    protected $table = 'carts';
    protected $primaryKey = 'cart_id';
    protected $fillable = ['customer_id', 'product_id', 'product_price', 'product_quantity', 'product_total', 'product_size_id', 'product_discount_price', 'flavour_id', 'addon_ids', 'message_on_cake', 'variation_ids', 'eggless', 'date', 'time'];
    protected $hidden = ['created_at', 'updated_at'];
    protected $appends = ['offer'];


    public function products() {
        return $this->hasOne('App\Models\Product','id','product_id');
    }
    public function cartAddons() {
        return $this->hasOne('App\Models\CartAddons','addon_id','addon_ids');
    }
    public function addons() {
        return $this->hasOne('App\Models\Addons','addon_id','addon_ids');
    }

    public function size() {
        return $this->hasOne('App\Models\Size','id','product_size_id');
    }


    public function flavour() {
        return $this->hasOne('App\Models\Flavour','flavour_id','flavour_id')->select('flavour_id','flavour_name');
    }

    public function variations() {
        return $this->hasOne('App\Models\product_variation','variation_id','variation_ids');
    }

    public function getOfferAttribute() {
        return round(100 - (($this->product_discount_price / $this->product_price) * 100));
    }



}
