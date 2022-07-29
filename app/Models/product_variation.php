<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Weight;

class product_variation extends Model
{
    use SoftDeletes;
    protected $dates = ['deleted_at'];
    protected $table = 'product_variation';
    protected $primaryKey = 'variation_id';
    protected $fillable = ['weight_id','product_id','egg', 'eggLess', 'egg_preparation', 'eggless_preparation', 'sku'];
    protected $hidden = ['deleted_at', 'created_at', 'updated_at'];

    public function weight(){
        return $this->hasMany('App\Models\Weight','weight_id','weight_id');
    }
}
