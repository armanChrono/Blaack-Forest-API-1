<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Voicenote extends Model
{
    public $timestamps = false;
    protected $table = 'voicenotes';
    protected $primaryKey = 'id';
    protected $fillable = ['voicenotes'];


  
}
