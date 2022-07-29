<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

use App\Models\Customer;
use App\Models\Category;

class CustomerDiscont extends Model
{
    use SoftDeletes;
    protected $dates = ['deleted_at'];
    protected $table = 'customer_discount';
    protected $primaryKey = 'customer_dis_id';
    protected $fillable = ['customer','category','discount_id', 'active_status'];
    protected $hidden = ['deleted_at', 'created_at', 'updated_at'];

    public function discount(){
        return $this->hasMany('App\Models\discount', 'discount_id', 'discount_id');
    }

    public function getCategoryAttribute($value)
    {
        $region = [];
        if( strpos($value, ',') !== false ) {
            // return explode(',', $value);
            foreach(explode(',', $value) as $regionId) {
                array_push($region, ['id' => (int)$regionId, 'itemName' => Category::find($regionId)->category_name]);
            }
        } elseif ($value) {
            array_push($region, ['id' => (int)$value, 'itemName' => Category::find($value)->category_name]);
        }
        return $region;
    }

    public function getCustomerAttribute($value){
        $customer = [];
        if( strpos($value, ',') !== false ) {
            // return explode(',', $value);
            foreach(explode(',', $value) as $customerId) {
                array_push($customer, ['id' => (int)$customerId, 'itemName' => Customer::find($customerId)]);
            }
        } elseif ($value) {
            // array_push($customer, ['id' => (int)$value, 'itemName' => Customer::find($value)->customer_name]);
            array_push($customer, ['id' => (int)$value, 'itemName' => Customer::find($value)]);

        }
        return $customer;
    }
}
