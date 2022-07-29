<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Size;
use App\Models\Tax;
use App\Models\Region;
use App\Models\Flavour;
use App\Models\Weight;
use App\Models\Unit;
use Illuminate\Support\Facades\log;

class Product extends Model
{
    use SoftDeletes;
    protected $dates = ['deleted_at'];
    protected $table = 'products';
    protected $primaryKey = 'id';
    protected $fillable = ['sub_category_id','category_id','region','flavour', 'product_name', 'product_price', 'product_discount_price', 'product_description', 'product_tags', 'product_sizes', 'product_slug','unit_id','hsn', 'tax_id','new_product','best_selling','suggested','active_status','short_description','product_discount', 'category_discount', 'customer_discount','variation_ids','weight_ids','COD_egg','COD_eggless', 'base_eggless_price'];
    protected $hidden = ['deleted_at', 'created_at', 'updated_at'];
    protected $appends = ['offer', 'region_id'];

    public function setProductNameAttribute($value)
    {
        $this->attributes['product_name'] = $value;
        $this->attributes['product_slug'] = str_slug($value);
    }

    public function subCategory() {
        return $this->hasOne('App\Models\SubCategory','id','sub_category_id');
    }

    public function category() {
        return $this->hasOne('App\Models\Category','id','category_id');
    }


    public function unit() {
        return $this->hasOne('App\Models\Unit','unit_id','unit_id');
    }

    public function variation() {
        return $this->hasMany('App\Models\product_variation','product_id','id');
    }

    public function tax() {
        return $this->hasOne('App\Models\Tax','tax_id','tax_id');
    }

    public function images() {
        return $this->hasMany('App\Models\ProductImage','product_id','id')->select('product_id','product_image', 'product_image_id');
    }


    public function oneImage() {
        return $this->hasOne('App\Models\ProductImage','product_id','id')->select('product_id','product_image');
    }

    public function firstImage() {
        return $this->hasMany('App\Models\ProductImage','product_id','id')->select('product_id','product_image')->oldest();
    }

    public function tags() {
        return $this->hasMany('App\Models\ProductTag','product_id', 'id')->select('product_tag_id', 'product_id','product_tags');
    }

    public function sizes() {
        return $this->hasMany('App\Models\ProductTag','product_id', 'id')->select('product_tag_id', 'product_id','product_tags');
    }

    public function linked() {
        return $this->hasOne('App\Models\LinkProduct','product_id', 'id');
    }

    public function scopeSearch($query, $search) {
        return $query->where('product_name', 'LIKE', '%'. $search . '%')->orWhere('product_description', 'LIKE', '%'. $search . '%');
    }

    public function productDiscounts(){
        return $this->hasOne('App\Models\ProductDiscont','product_discount_id', 'product_discount');
    }

    public function getProductTagsAttribute($value)
    {
        $tags = [];
        if( strpos($value, ',') !== false ) {
            foreach(explode(',', $value) as $tagId) {
                array_push($tags, ['id' => (int)$tagId, 'itemName' => Tag::select('tag_name')->find($tagId)->tag_name]);
            }
        } elseif ($value) {
            array_push($tags, ['id' => (int)$value, 'itemName' => Tag::select('tag_name')->find($value)->tag_name]);
        }
        return $tags;
    }

    public function getProductSizesAttribute($value)
    {
        $size = [];
        if( strpos($value, ',') !== false ) {
            // return explode(',', $value);
            foreach(explode(',', $value) as $sizeId) {
                array_push($size, ['id' => (int)$sizeId, 'itemName' => Size::select('size_name')->find($sizeId)->size_name]);
            }
        } elseif ($value) {
            array_push($size, ['id' => (int)$value, 'itemName' => Size::select('size_name')->find($value)->size_name]);
        }
        return $size;
    }
    public function getRegionIdAttribute(){
        $array = [];
        $str = null;
        foreach ($this->region as $values) {
            array_push($array, $values['id']);
            $str = implode(",",$array);
        }
        return $str;
    }

     public function getRegionAttribute($value)
    {
        $region = [];
        if( strpos($value, ',') !== false ) {
             foreach(explode(',', $value) as $regionId) {
                $regionList = Region::with('country', 'state', 'City')->find($regionId);
                 if($regionList){
                    array_push($region, ['id' => (int)$regionId, 'itemName' => $regionList]);
                 }
            }
        } elseif ($value) {
            array_push($region, ['id' => (int)$value, 'itemName' => Region::with('country', 'state', 'City')->find($value)]);
        }
        return $region;
    }

    public function getFlavourAttribute($value)
    {
        $flavour = [];
        if( strpos($value, ',') !== false ) {
            // return explode(',', $value);
            foreach(explode(',', $value) as $flavourId) {
                array_push($flavour, ['id' => (int)$flavourId, 'itemName' => Flavour::find($flavourId)->flavour_name]);
            }
        } elseif ($value) {
            array_push($flavour, ['id' => (int)$value, 'itemName' => Flavour::find($value)->flavour_name]);
        }
        return $flavour;
    }

    public function getWeightIdsAttribute($value)
    {
        $weightIds = [];
        if( strpos($value, ',') !== false ) {
            // return explode(',', $value);
            foreach(explode(',', $value) as $weightId) {
                array_push($weightIds, ['id' => (int)$weightId, 'itemName' => Weight::find($weightId)->weight_name]);
            }
        } elseif ($value) {
            array_push($weightIds, ['id' => (int)$value, 'itemName' => Weight::find($value)->weight_name]);
        }
        return $weightIds;
    }

    public function getOfferAttribute() {
        if($this->product_price > 0){
            if($this->product_price - $this->product_discount_price != 0) {
                return round(100 - (($this->product_discount_price / $this->product_price) * 100));
            } else {
                return 0;
            }
        }else{
            return 0;
        }

    }
}
