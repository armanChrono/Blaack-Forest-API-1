<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TimeZone extends Model
{
    use HasFactory;
    protected $table = 'time_zone';
    protected $primarykey='id';
    protected $fillable = ['s_time','e_time'];
}
