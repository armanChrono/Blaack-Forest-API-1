<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Stock extends Model
{
    protected $table = 'stocks';
    protected $primaryKey = 'id';
    protected $fillable = ['product_id', 'size_id', 'stock_quantity'];
    protected $hidden = ['created_at', 'updated_at'];

    public function size() {
        return $this->hasMany('App\Models\Size','id','size_id')->select('id','size_name');
    }
}
