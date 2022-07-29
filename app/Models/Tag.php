<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Tag extends Model
{
    use SoftDeletes;
    protected $dates = ['deleted_at'];
    protected $table = 'tags';
    protected $primaryKey = 'id';
    protected $fillable = ['tag_name', 'active_status'];
    protected $hidden = ['deleted_at', 'created_at', 'updated_at'];
}
