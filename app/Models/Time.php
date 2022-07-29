<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Time extends Model
{
    use HasFactory;
    protected $table = 'time';
    protected $primarykey='id';
    protected $fillable = ['date1', '9_date','12_date','3_date','6_date','time'];
}
