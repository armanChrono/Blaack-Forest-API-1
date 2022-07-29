<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderAddons extends Model
{
    protected $table = 'order_addons';
    protected $primaryKey = 'order_addons_id';
    protected $fillable = ['order_id', 'addon_id', 'customer_id', 'product_name', 'image', 'price', 'quantity', 'total', 'hsn','tax_id'];
    protected $hidden = ['created_at', 'updated_at'];

    public function tax() {
        return $this->hasOne('App\Models\Tax','tax_id','tax_id');
    }
}
