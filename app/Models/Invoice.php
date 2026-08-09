<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    protected $fillable = [
        'tenant_id', 'invoice_number', 'customer_id', 'template_id',
        'invoice_date', 'due_date', 'status', 'subtotal', 'tax_amount',
        'discount_amount', 'discount_type', 'total_amount', 'paid_amount',
        'currency', 'language', 'notes', 'terms_conditions', 'created_by',
    ];

    protected $casts = ['invoice_date' => 'date', 'due_date' => 'date'];

    public function tenant() { return $this->belongsTo(Tenant::class); }
    public function customer() { return $this->belongsTo(Customer::class); }
    public function items() { return $this->hasMany(InvoiceItem::class); }
    public function payments() { return $this->hasMany(Payment::class); }
    public function template() { return $this->belongsTo(InvoiceTemplate::class); }
    public function creator() { return $this->belongsTo(User::class, 'created_by'); }

    public function getRemainingAmountAttribute(): float
    {
        return $this->total_amount - $this->paid_amount;
    }

    public function calculateTotals(): void
    {
        $subtotal = $this->items->sum(fn($item) => $item->quantity * $item->unit_price);
        $taxAmount = $this->items->sum(fn($item) => ($item->quantity * $item->unit_price) * ($item->tax_rate / 100));

        $discount = $this->discount_type === 'percent'
            ? ($subtotal * $this->discount_amount / 100)
            : $this->discount_amount;

        $this->subtotal = $subtotal;
        $this->tax_amount = $taxAmount;
        $this->total_amount = $subtotal + $taxAmount - $discount;
        $this->save();
    }

    public static function generateNumber(int $tenantId): string
    {
        $last = static::where('tenant_id', $tenantId)->latest()->first();
        $number = $last ? (intval(substr($last->invoice_number, -4)) + 1) : 1;
        return 'INV-' . date('Y') . '-' . str_pad($number, 4, '0', STR_PAD_LEFT);
    }
}
