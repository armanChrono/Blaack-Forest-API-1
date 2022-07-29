<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PrivacyPolicy extends Model
{
    use SoftDeletes;
    protected $dates = ['deleted_at'];
    protected $table = 'privacy_policy';
    protected $primaryKey = 'privacy_policy_id';
    protected $fillable = ['privacy_policy'];
    protected $hidden = ['deleted_at', 'created_at', 'updated_at'];
}
