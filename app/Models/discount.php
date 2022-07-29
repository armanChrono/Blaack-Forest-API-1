<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class discount extends Model
{
    use SoftDeletes;
    protected $dates = ['deleted_at'];
    protected $table = 'discount';
    protected $primaryKey = 'discount_id';
    protected $fillable = ['discount_name','discount_percentage', 'active_status','fromDate', 'toDate'];
    protected $hidden = ['deleted_at', 'created_at', 'updated_at'];
}
