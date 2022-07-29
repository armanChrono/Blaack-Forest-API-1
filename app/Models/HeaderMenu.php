<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class HeaderMenu extends Model
{
    use SoftDeletes;
    protected $dates = ['deleted_at'];
    protected $table = 'header_menu';
    protected $primaryKey = 'header_menu_id';
    protected $fillable = ['header_menu_name','header_menu_slug'];
    protected $visible = [];
}
