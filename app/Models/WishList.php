<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WishList extends Model
{
    protected $table = 'wishlists';
    protected $primaryKey = 'wishlist_id';
    protected $fillable = ['customer_id', 'product_id', 'date', 'time'];
    protected $hidden = ['created_at', 'updated_at'];

    public function products() {
        return $this->hasOne('App\Models\Product','id','product_id');
    }
}
