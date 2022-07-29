<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Sanctum\HasApiTokens;

class Dispatch extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    protected $dates = ['deleted_at'];
    protected $table = 'dispatch_team';
    protected $primaryKey = 'dispatch_team_id';
    protected $fillable = ['dispatch_name', 'password', 'active_status', 'location_details_id'];
    protected $hidden = ['password', 'deleted_at', 'created_at', 'updated_at'];
   
    //logout
    public function AauthAcessToken(){
        return $this->hasMany('\App\OauthAccessToken');
    }

    public function locationDetails(){
        return $this->hasMany('App\Models\LocationDetails','location_details_id','location_details_id');
    }
}
