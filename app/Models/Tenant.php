<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Tenant extends Model
{
    protected $fillable = [
        'company_name', 'subdomain', 'email', 'phone', 'address',
        'logo', 'tax_number', 'status', 'subscription_plan', 'subscription_expires_at',
        'signer_name', 'signer_title', 'stamp_image', 'signature_image',
    ];

    protected $casts = ['subscription_expires_at' => 'datetime'];

    public function users(): HasMany { return $this->hasMany(User::class); }
    public function customers(): HasMany { return $this->hasMany(Customer::class); }
    public function products(): HasMany { return $this->hasMany(Product::class); }
    public function invoices(): HasMany { return $this->hasMany(Invoice::class); }
    public function settings(): HasMany { return $this->hasMany(Setting::class); }
    public function templates(): HasMany { return $this->hasMany(InvoiceTemplate::class); }

    public function getSetting(string $key, mixed $default = null): mixed
    {
        $setting = $this->settings()->where('key', $key)->first();
        return $setting ? $setting->value : $default;
    }
}
