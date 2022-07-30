<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ViewCard extends Model
{
    use HasFactory;
    protected $dates = ['deleted_at'];
    protected $table = 'cards';
    protected $primaryKey = 'id';
    protected $fillable = ['card_name','card_link','card_image','active_status'];
    protected $hidden = ['created_at', 'updated_at', 'deleted_at'];
}
