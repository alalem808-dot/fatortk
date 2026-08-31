<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
class Invoice extends Model
{
    protected $fillable = [
        'tenant_id', 'invoice_number', 'customer_id', 'template_id',
        'warehouse_id',
        'invoice_date', 'due_date', 'status', 'subtotal', 'tax_amount',
        'discount_amount', 'discount_type', 'total_amount', 'paid_amount',
        'currency', 'exchange_rate', 'language', 'notes', 'terms_conditions',
        'created_by', 'public_token',
    ];

    protected $casts = [
        'invoice_date' => 'date',
        'due_date'     => 'date',
        'total_amount' => 'float',
        'paid_amount'  => 'float',
        'subtotal'     => 'float',
        'tax_amount'   => 'float',
    ];

    public function tenant() { return $this->belongsTo(Tenant::class); }
    public function customer() { return $this->belongsTo(Customer::class); }
    public function warehouse() { return $this->belongsTo(Warehouse::class); }
    public function items() { return $this->hasMany(InvoiceItem::class); }
    public function payments() { return $this->hasMany(Payment::class); }
    public function template() { return $this->belongsTo(InvoiceTemplate::class); }
    public function creator() { return $this->belongsTo(User::class, 'created_by'); }

    public function returns() { return $this->hasMany(InvoiceReturn::class); }

    public function getRemainingAmountAttribute(): float
    {
        return $this->total_amount - $this->paid_amount;
    }

    public function calculateTotals(): void
    {
        // الإجمالي الخام بدون أي خصومات
        $subtotal = $this->items->sum(fn($item) => $item->quantity * $item->unit_price);

        // خصم على مستوى البند (مبلغ ثابت لكل بند)
        $lineDiscounts = $this->items->sum(fn($item) => $item->discount ?? 0);

        // الإجمالي بعد خصم البنود
        $taxableAmount = $subtotal - $lineDiscounts;

        // خصم رأس الفاتورة
        $headerDiscount = $this->discount_type === 'percent'
            ? ($taxableAmount * $this->discount_amount / 100)
            : (float) $this->discount_amount;

        // الإجمالي الخاضع للضريبة بعد كل الخصومات
        $finalTaxable = max(0, $taxableAmount - $headerDiscount);

        // الضريبة: تُحسب لكل بند على قاعدته بعد خصم البند
        $taxAmount = $this->items->sum(function ($item) {
            if ((float) $item->tax_rate <= 0) return 0;
            $itemBase = ((float) $item->quantity * (float) $item->unit_price) - ((float) ($item->discount ?? 0));
            return max(0, $itemBase) * ((float) $item->tax_rate / 100);
        });

        // تعديل الضريبة نسبياً بعد خصم رأس الفاتورة
        if ($headerDiscount > 0 && $taxableAmount > 0.001) {
            $taxAmount = $taxAmount * ($finalTaxable / $taxableAmount);
        }

        $this->subtotal     = round($subtotal, 2);
        $this->tax_amount   = round($taxAmount, 2);
        $this->total_amount = round($finalTaxable + $taxAmount, 2);
        $this->save();
    }

    public static function generateNumber(int $tenantId): string
    {
        $year = date('Y');

        // lockForUpdate يجب أن يعمل داخل transaction فعلية
        // نستخدم DB::transaction إذا لم نكن داخل واحدة بالفعل
        if (\Illuminate\Support\Facades\DB::transactionLevel() > 0) {
            return static::_doGenerateNumber($tenantId, $year);
        }

        return \Illuminate\Support\Facades\DB::transaction(
            fn() => static::_doGenerateNumber($tenantId, $year)
        );
    }

    private static function _doGenerateNumber(int $tenantId, string $year): string
    {
        $prefix = \App\Models\Setting::where('tenant_id', $tenantId)
            ->where('key', 'invoice_prefix')
            ->value('value') ?? 'INV';

        // نبحث بـ created_at بدلاً من invoice_date لأن الفاتورة لم تُحفظ بعد عند الاستدعاء
        $last = static::where('tenant_id', $tenantId)
            ->whereYear('created_at', $year)
            ->lockForUpdate()
            ->latest('id')
            ->first();

        $number = 1;
        if ($last && preg_match('/-(\d+)$/', $last->invoice_number, $matches)) {
            $number = intval($matches[1]) + 1;
        }

        return $prefix . '-' . $year . '-' . str_pad($number, 4, '0', STR_PAD_LEFT);
    }
}
