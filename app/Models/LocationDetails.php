<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LocationDetails extends Model
{
    use SoftDeletes;
    protected $dates = ['deleted_at'];
    protected $table = 'location_details';
    protected $primaryKey = 'location_details_id';
    protected $fillable = ['region_id', 'location_code', 'location_name', 'address', 'active_status', 'pincode', 'mobile_no'];
    protected $hidden = ['deleted_at', 'created_at', 'updated_at'];

    public function region() {
        return $this->hasMany('App\Models\Region','region_id','region_id');
    }
    
}
