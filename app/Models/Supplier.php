<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    protected $fillable = ['tenant_id', 'name', 'phone', 'email', 'address', 'tax_number', 'notes'];

    public function tenant()    { return $this->belongsTo(Tenant::class); }
    public function purchases() { return $this->hasMany(Purchase::class); }

    public function getTotalPurchasesAttribute(): float
    {
        // exchange_rate = كم وحدة من العملة الأجنبية تساوي 1 من العملة الأساسية
        // إذا كان exchange_rate = 500 (500 SDG = 1 USD) فالمبلغ بالعملة الأساسية = total / exchange_rate
        return $this->purchases()->where('status', 'received')->get()
            ->sum(fn($p) => ($p->exchange_rate > 0) ? ($p->total / $p->exchange_rate) : $p->total);
    }
}
