<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class Enquiry extends Model
{
    protected $table = 'enquiry';
    protected $primaryKey = 'id';
    protected $fillable = ['name', 'mobile', 'occation','order_date','order_time','location', 'status','image', 'weight'];
    protected $hidden = ['created_at', 'updated_at'];
}
