<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\log;


class ProductDiscont extends Model
{
    use SoftDeletes;
    protected $dates = ['deleted_at'];
    protected $table = 'product_discount';
    protected $primaryKey = 'product_discount_id';
    protected $fillable = ['product','discount_id', 'active_status'];
    protected $hidden = ['deleted_at', 'created_at', 'updated_at'];

    public function discount(){
        return $this->hasMany('App\Models\discount', 'discount_id', 'discount_id')->select('discount_name','discount_id','discount_percentage');
    }


    public function getProductAttribute($value)
    {
        $region = [];
        if( strpos($value, ',') !== false ) {
            // return explode(',', $value);
            foreach(explode(',', $value) as $regionId) {
                $product = Product::find($regionId);
                if($product){
                    array_push($region, ['id' => (int)$regionId, 'itemName' => $product->product_name]);
                }
            }
        } elseif ($value) {
            array_push($region, ['id' => (int)$value, 'itemName' => Product::find($value)->product_name]);
        }
        return $region;
    }
}
