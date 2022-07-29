<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Flavour;

class product_flavour extends Model
{
    use SoftDeletes;
    protected $dates = ['deleted_at'];
    protected $table = 'product_flavour';
    protected $primaryKey = 'product_flavour_id';
    protected $fillable = ['flavour_id','product_id'];
    protected $hidden = ['deleted_at', 'created_at', 'updated_at'];

    public function flavour(){
        return $this->hasMany('App\Models\Flavour','flavour_id','flavour_id');
    }
}
