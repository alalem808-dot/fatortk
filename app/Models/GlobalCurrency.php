<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GlobalCurrency extends Model
{
    protected $fillable = ['code', 'name', 'symbol', 'is_active', 'sort_order'];
    protected $casts = ['is_active' => 'boolean'];
}
