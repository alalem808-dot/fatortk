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
        'currencies_enabled', 'last_login_at', 'notes',
    ];

    protected $casts = [
        'subscription_expires_at' => 'datetime',
        'currencies_enabled'      => 'boolean',
        'last_login_at'           => 'datetime',
    ];

    public function users(): HasMany { return $this->hasMany(User::class); }
    public function customers(): HasMany { return $this->hasMany(Customer::class); }
    public function products(): HasMany { return $this->hasMany(Product::class); }
    public function invoices(): HasMany { return $this->hasMany(Invoice::class); }
    public function settings(): HasMany { return $this->hasMany(Setting::class); }
    public function templates(): HasMany { return $this->hasMany(InvoiceTemplate::class); }
    public function paymentMethods(): HasMany { return $this->hasMany(PaymentMethod::class); }
    public function subscriptionPayments(): HasMany { return $this->hasMany(SubscriptionPayment::class); }
    public function supportTickets(): HasMany { return $this->hasMany(SupportTicket::class); }

    public function getSetting(string $key, mixed $default = null): mixed
    {
        // تحميل كل الإعدادات مرة واحدة وتخزينها في الذاكرة لتجنب N+1
        if (!$this->relationLoaded('settings')) {
            $this->load('settings');
        }
        $setting = $this->settings->firstWhere('key', $key);
        return $setting ? $setting->value : $default;
    }
}
