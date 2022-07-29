<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Rating extends Model
{
    use SoftDeletes;
    protected $dates = ['deleted_at'];
    protected $table = 'rating';
    protected $primaryKey = 'rating_id';
    protected $fillable = ['product_id', 'customer_id', 'rating', 'rating_starts'];
    protected $hidden = ['deleted_at', 'created_at', 'updated_at'];
}
