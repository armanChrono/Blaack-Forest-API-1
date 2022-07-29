<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Weight extends Model
{
    use SoftDeletes;
    protected $dates = ['deleted_at'];
    protected $table = 'weights';
    protected $primaryKey = 'weight_id';
    protected $fillable = ['weight_name', 'unit_id', 'allow_cod', 'active_status'];
    protected $hidden = ['deleted_at', 'created_at', 'updated_at'];

    public function unit() {
        return $this->hasOne('App\Models\Unit','unit_id','unit_id');
    }

    public function variation() {
        return $this->belongsTo('App\Models\product_variation');
    }

}
