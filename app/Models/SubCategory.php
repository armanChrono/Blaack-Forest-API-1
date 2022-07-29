<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SubCategory extends Model
{
    use SoftDeletes;
    protected $dates = ['deleted_at'];
    protected $table = 'sub_categories';
    protected $primaryKey = 'id';
    protected $fillable = ['category_id', 'sub_category_name', 'sub_category_slug', 'sub_category_description', 'sub_category_image', 'sub_category_tags', 'active_status', 'hsn', 'tax_id'];
    protected $hidden = ['deleted_at', 'created_at', 'updated_at'];

    public function setSubCategoryNameAttribute($value)
    {
        $this->attributes['sub_category_name'] = $value;
        $this->attributes['sub_category_slug'] = str_slug($value);
    }

    public function tags() {
        return $this->hasMany('App\Models\SubCategoryTag','sub_category_id', 'id')->select('sub_category_id','sub_category_tags');
    }

    public function category() {
        return $this->hasOne('App\Models\Category','id','category_id')->select('id', 'category_name');
    }

    public function products() {
        return $this->hasMany('App\Models\Product','sub_category_id', 'id')->where('active_status', 1);
    }
    public function filter($q) {
        if ($request->has('price_from')) {
            $model->where('price', '>', $request->get('price_from'));
        }
        if ($request->has('color')) {
            $model->where('color', '>', $request->get('color'));
        }

        return $model;
    }
}
