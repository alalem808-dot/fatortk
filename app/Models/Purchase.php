<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Purchase extends Model
{
    protected $fillable = [
        'tenant_id', 'supplier_id', 'warehouse_id', 'reference',
        'supplier_name', 'supplier_phone', 'purchase_date', 'status',
        'subtotal', 'tax_amount', 'total', 'paid_amount', 'returned_amount',
        'payment_status', 'currency', 'exchange_rate', 'notes', 'created_by',
    ];

    protected $casts = [
        'purchase_date'   => 'date',
        'returned_amount' => 'float',
    ];

    public function tenant()    { return $this->belongsTo(Tenant::class); }
    public function supplier()  { return $this->belongsTo(Supplier::class); }
    public function warehouse() { return $this->belongsTo(Warehouse::class); }
    public function items()     { return $this->hasMany(PurchaseItem::class); }
    public function returns()   { return $this->hasMany(PurchaseReturn::class); }
    public function payments()  { return $this->hasMany(SupplierPayment::class); }
    public function creator()   { return $this->belongsTo(User::class, 'created_by'); }

    public function getRemainingAmountAttribute(): float
    {
        return max(0, (float) $this->total - (float) $this->paid_amount);
    }

    /**
     * صافي قيمة أمر الشراء بعد خصم المرتجعات
     * total الأصلي لا يُعدَّل أبداً — الصافي يُحسب دائماً من هنا
     */
    public function getNetTotalAttribute(): float
    {
        return max(0, (float) $this->total - (float) $this->returned_amount);
    }

    public function getStatusLabelAttribute(): string
    {
        return ['pending' => 'معلق', 'received' => 'مستلم', 'cancelled' => 'ملغي'][$this->status] ?? $this->status;
    }

    public function getStatusColorAttribute(): string
    {
        return ['pending' => 'warning', 'received' => 'success', 'cancelled' => 'danger'][$this->status] ?? 'secondary';
    }

    public function getPaymentStatusLabelAttribute(): string
    {
        return ['unpaid' => 'غير مدفوع', 'partial' => 'مدفوع جزئياً', 'paid' => 'مدفوع'][$this->payment_status] ?? $this->payment_status;
    }

    public function getPaymentStatusColorAttribute(): string
    {
        return ['unpaid' => 'danger', 'partial' => 'warning', 'paid' => 'success'][$this->payment_status] ?? 'secondary';
    }
}
