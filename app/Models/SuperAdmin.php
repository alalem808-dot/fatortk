<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class SuperAdmin extends Authenticatable
{
    use Notifiable;

    protected $fillable = ['name', 'username', 'email', 'password', 'last_login'];
    protected $hidden   = ['password', 'remember_token'];
    protected $casts    = ['last_login' => 'datetime'];
}
