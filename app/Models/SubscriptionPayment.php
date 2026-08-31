<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SubscriptionPayment extends Model
{
    protected $fillable = [
        'tenant_id', 'plan_slug', 'plan_name', 'amount_usd',
        'period', 'paid_at', 'expires_at', 'notes',
    ];

    protected $casts = [
        'paid_at'    => 'date',
        'expires_at' => 'date',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
}
