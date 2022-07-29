<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Pincode extends Model
{
    use SoftDeletes;
    protected $dates = ['deleted_at'];
    protected $table = 'pincode';
    protected $primaryKey = 'pincode_id';
    protected $fillable = ['region_id', 'pincode', 'rate', 'active_status'];
    protected $hidden = ['deleted_at', 'created_at', 'updated_at'];

    public function region() {
        return $this->hasMany('App\Models\Region','region_id','region_id');
    }
}
