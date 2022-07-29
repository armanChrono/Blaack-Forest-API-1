<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Paymentdetails extends Model
{
    protected $table = 'payment_details';
    protected $primaryKey = 'payment_details_id';
    protected $fillable = ['payment_details_id', 'customer_name', 'customer_mobile', 'customer_email', 'amount', 'payment_status', 'date'];
    protected $visible = ['payment_details_id', 'customer_name', 'customer_mobile', 'customer_email', 'amount', 'payment_status', 'date'];
}
