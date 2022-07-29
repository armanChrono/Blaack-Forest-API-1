<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AcceptanceLog extends Model
{
    use HasFactory;
    protected $table = 'acceptance_log';
    protected $primaryKey = 'log_id';
    protected $fillable = ['dispatch_order_id', 'status'];
    protected $hidden = ['created_at', 'updated_at'];
}
