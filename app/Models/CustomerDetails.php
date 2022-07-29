<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CustomerDetails extends Model
{
    use SoftDeletes;
    protected $dates = ['deleted_at'];
    protected $table = 'customer_details';
    protected $primaryKey = 'customer_details_id';
    protected $fillable = ['customer_name', 'customer_mobile_no', 'customer_email','address'];
    protected $hidden = ['deleted_at', 'created_at', 'updated_at'];
}
