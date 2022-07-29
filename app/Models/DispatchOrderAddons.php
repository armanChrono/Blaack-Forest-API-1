<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DispatchOrderAddons extends Model
{
    protected $table = 'dispatch_addon_orders';
    protected $primaryKey = 'dispatch_addon_orders_id';
    protected $fillable = ['order_id', 'addon_id', 'customer_id', 'product_name', 'image', 'price', 'quantity', 'total', 'hsn','tax_id'];
    protected $hidden = ['created_at', 'updated_at'];
}
