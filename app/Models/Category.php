<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Category extends Model
{
    use SoftDeletes;
    protected $dates = ['deleted_at'];
    protected $table = 'categories';
    protected $primaryKey = 'id';
    protected $fillable = ['category_name', 'category_description', 'category_slug', 'active_status', 'category_image', 'banner_image','category_discount_id'];
    protected $hidden = ['created_at', 'updated_at', 'deleted_at'];

    public function subCategories() {
        return $this->hasMany('App\Models\SubCategory', 'category_id', 'id')->where('active_status', 1);
    }

    public function categoryDiscount(){
        return $this->hasOne('App\Models\CategoryDiscont', 'category_discount_id', 'category_discount_id');
    }

    public function subCategoriesFour() {
        return $this->hasMany('App\Models\SubCategory', 'category_id', 'id')->orderByRaw("IF(sub_category_slug = 'insta-cakes', 0,1)")->where('active_status', 1)
        ->orderBy('index_id', 'ASC');
    }
    public function subCategoriesLatest() {
        return $this->hasMany('App\Models\SubCategory', 'category_id', 'id')->select('sub_category_slug', 'sub_category_name');
    }
    public function products() {
        return $this->hasMany('App\Models\Product','category_id', 'id')->where('active_status', 1);
    }
    public function setCategoryNameAttribute($value)
    {
        $this->attributes['category_name'] = $value;
        $this->attributes['category_slug'] = str_slug($value);
    }
}
