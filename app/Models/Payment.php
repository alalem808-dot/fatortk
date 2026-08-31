<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Payment extends Model
{
    protected $fillable = [
        'tenant_id', 'invoice_id', 'payment_date', 'amount',
        'payment_method', 'reference_number', 'notes',
    ];

    protected $casts = ['payment_date' => 'date'];

    /**
     * DESIGN-04 Fix: Global Scope لضمان عزل بيانات المستأجرين
     * يمنع الاستعلامات المباشرة على Payment من تسريب بيانات tenant آخر
     */
    protected static function booted(): void
    {
        static::addGlobalScope('tenant', function (Builder $builder) {
            // نطبّق الـ scope فقط عند وجود مستخدم مسجّل دخوله
            if (auth()->check() && auth()->user()->tenant_id) {
                $builder->where('payments.tenant_id', auth()->user()->tenant_id);
            }
        });
    }

    public function invoice() { return $this->belongsTo(Invoice::class); }
    public function tenant()  { return $this->belongsTo(Tenant::class); }
}
