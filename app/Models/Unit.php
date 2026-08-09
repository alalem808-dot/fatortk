<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Unit extends Model
{
    protected $fillable = ['tenant_id', 'name', 'symbol'];

    public function products()
    {
        return $this->hasMany(Product::class, 'unit', 'name');
    }
}
