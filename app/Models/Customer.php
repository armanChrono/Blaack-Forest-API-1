<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Customer extends Authenticatable
{
    use HasApiTokens;
    use SoftDeletes;
    protected $dates = ['deleted_at'];
    protected $table = 'customers';
    protected $primaryKey = 'customer_id';
    protected $fillable = ['customer_name', 'customer_mobile', 'customer_email', 'customer_password', 'customer_otp', 'default_address_id', 'customer_gender', 'customer_discount_id'];
    protected $hidden = ['customer_password', 'active_status','deleted_at', 'created_at', 'updated_at'];


    public function customerDiscont(){
        return $this->hasOne('App\Models\CustomerDiscont', 'customer_dis_id', 'customer_discount_id');
    }
}
