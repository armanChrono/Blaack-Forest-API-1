<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TermsAndCondition extends Model
{
    use SoftDeletes;
    protected $dates = ['deleted_at'];
    protected $table = 'terms_and_condition';
    protected $primaryKey = 'terms_id';
    protected $fillable = ['terms_and_condition'];
    protected $hidden = ['deleted_at', 'created_at', 'updated_at'];
}
