<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LatestArrival extends Model
{
    use SoftDeletes;
    protected $dates = ['deleted_at'];
    protected $table = 'latest_arrivals';
    protected $primaryKey = 'id';
    protected $fillable = ['latest_arrival_name','latest_arrival_image','active_status'];
    protected $hidden = ['created_at', 'updated_at', 'deleted_at'];
}
