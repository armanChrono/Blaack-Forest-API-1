<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Foundation\Auth\User as Authenticatable;


class ShopDetails extends Model
{
    use HasApiTokens, SoftDeletes;
    protected $dates = ['deleted_at'];
    protected $table = 'shop_details';
    protected $primaryKey = 'shop_details_id';
    protected $fillable = ['region_id', 'shop_code', 'shop_name', 'address', 'active_status', 'pincode', 'mobile_no','password'];
    protected $hidden = ['password', 'deleted_at', 'created_at', 'updated_at'];

    public function AauthAcessToken(){
        return $this->hasMany('\App\OauthAccessToken');
    }
    public function region() {
        return $this->hasMany('App\Models\Region','region_id','region_id');
    }
}
