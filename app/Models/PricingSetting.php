<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PricingSetting extends Model
{
    protected $fillable = [
        'currency',
        'plan_name',
        'monthly_price',
        'yearly_price',
        'features',
        'max_invoices',
        'max_customers',
        'max_products',
        'max_users',
        'is_active',
    ];

    protected $casts = [
        'features' => 'array',
        'is_active' => 'boolean',
    ];
}
