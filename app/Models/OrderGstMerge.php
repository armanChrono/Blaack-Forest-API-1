<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderGstMerge extends Model
{
    use HasFactory;
    protected $table = 'order_gst_merge';
    protected $primaryKey = 'id';
    protected $fillable = ['order_id', 'gst_0','gst_5','gst_12', 'gst_18', 'gst_28', 'shipping_gst_18'];
    protected $hidden = ['created_at', 'updated_at'];

}
