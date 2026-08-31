<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Customer extends Model
{
    protected $fillable = [
        'tenant_id', 'name', 'email', 'phone', 'whatsapp_number',
        'address', 'city', 'country', 'tax_number', 'notes',
    ];

    public function tenant() { return $this->belongsTo(Tenant::class); }
    public function invoices() { return $this->hasMany(Invoice::class); }

    public function getTotalDueAttribute(): float
    {
        return $this->invoices()
            ->whereIn('status', ['sent', 'overdue', 'partially_paid'])
            ->selectRaw('SUM(total_amount - paid_amount) as due')
            ->value('due') ?? 0.0;
    }
}
