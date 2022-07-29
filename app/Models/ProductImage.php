<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductImage extends Model
{
    protected $table = 'product_images';
    protected $primaryKey = 'product_image_id';
    protected $fillable = ['product_id', 'product_image'];
    protected $hidden = ['deleted_at', 'created_at', 'updated_at'];
}
