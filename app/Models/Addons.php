<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class Addons extends Model
{
    use SoftDeletes;
    protected $dates = ['deleted_at'];
    protected $table = 'addons';
    protected $primaryKey = 'addon_id';
    protected $fillable = ['product_name', 'price', 'image','hsn', 'tax_id', 'region_id'];
    protected $hidden = ['created_at', 'updated_at'];


  
}
