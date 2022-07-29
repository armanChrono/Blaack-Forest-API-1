<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Repositories\ResponseRepository;

class LocationState extends Model
{
    protected $table = 'location_states';
    public $timestamps = false;
}
