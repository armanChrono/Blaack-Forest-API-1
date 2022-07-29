<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $table = 'settings';
    protected $primaryKey = 'id';
    protected $fillable = ['delivery_charge', 'cgst_tax', 'sgst_tax'];
    protected $hidden = ['created_at', 'updated_at'];
}
