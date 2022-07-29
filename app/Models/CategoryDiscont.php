<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use App\Models\Category;


class CategoryDiscont extends Model
{
    use SoftDeletes;
    protected $dates = ['deleted_at'];
    protected $table = 'category_discount';
    protected $primaryKey = 'category_discount_id';
    protected $fillable = ['category','discount_id', 'active_status'];
    protected $hidden = ['deleted_at', 'created_at', 'updated_at'];

    public function discount(){
        return $this->hasMany('App\Models\discount', 'discount_id', 'discount_id')->select('discount_name','discount_id','discount_percentage');
    }

    public function getCategoryAttribute($value)
    {
        $region = [];
        if( strpos($value, ',') !== false ) {
            // return explode(',', $value);
            foreach(explode(',', $value) as $regionId) {
                array_push($region, ['id' => (int)$regionId, 'itemName' => Category::find($regionId)->category_name]);
            }
        } elseif ($value) {
            array_push($region, ['id' => (int)$value, 'itemName' => Category::find($value)->category_name]);
        }
        return $region;
    }
}
