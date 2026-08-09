<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubscriptionPlan extends Model
{
    protected $fillable = [
        'slug', 'name', 'price_monthly', 'price_yearly',
        'max_invoices_per_month', 'max_customers', 'max_products', 'max_users',
        'excel_export', 'email_send', 'stock_management', 'custom_templates',
        'max_templates', 'api_access', 'is_active',
    ];

    protected $casts = [
        'excel_export' => 'boolean', 'email_send' => 'boolean',
        'stock_management' => 'boolean', 'custom_templates' => 'boolean',
        'api_access' => 'boolean', 'is_active' => 'boolean',
    ];

    // -1 يعني غير محدود
    public function isUnlimited(string $field): bool
    {
        return $this->$field === -1;
    }

    public function canDo(string $feature): bool
    {
        return (bool) $this->$feature;
    }
}
