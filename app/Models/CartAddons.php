<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CartAddons extends Model
{
    protected $table = 'cart_addon';
    protected $primaryKey = 'id';
    protected $fillable = ['customer_id','addon_id', 'product_name', 'image', 'price', 'quantity', 'total', 'hsn', 'tax_id', 'date', 'time'];
    protected $hidden = ['created_at', 'updated_at'];

    public function cartAddons() {
        return $this->hasOne('App\Models\Addons','addon_id','addon_id');
    }
    public function tax() {
        return $this->hasOne('App\Models\Tax','tax_id','tax_id');
    }

}
