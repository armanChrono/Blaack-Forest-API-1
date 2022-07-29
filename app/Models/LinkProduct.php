<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LinkProduct extends Model
{
    protected $table = 'link_products';
    protected $primaryKey = 'id';
    protected $fillable = ['link_id', 'product_id', 'color_id', 'active_status'];
    protected $hidden = ['created_at', 'updated_at'];

    public function products() {
        return $this->hasOne('App\Models\Product','id', 'product_id')->select('sub_category_id', 'id', 'product_name', 'product_slug', 'product_discount_price' ,'product_price', 'product_sizes', 'product_tags');
    }

    public function productSlug() {
        return $this->hasOne('App\Models\Product','id', 'product_id')->select('id', 'product_name', 'product_slug');
    }

    public function color() {
        return $this->hasOne('App\Models\Color','id','color_id')->select('id', 'color_name', 'color_code');
    }
}
