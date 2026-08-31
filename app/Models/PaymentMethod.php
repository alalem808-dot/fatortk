<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentMethod extends Model
{
    protected $fillable = ['tenant_id', 'name', 'code', 'is_active', 'sort_order'];
    protected $casts = ['is_active' => 'boolean'];

    public function tenant() { return $this->belongsTo(Tenant::class); }

    // طرق الدفع الافتراضية عند إنشاء tenant جديد
    public static function createDefaults(int $tenantId): void
    {
        $defaults = [
            ['name' => 'نقدي',           'code' => 'cash',   'sort_order' => 1],
            ['name' => 'تحويل بنكي',     'code' => 'bank',   'sort_order' => 2],
            ['name' => 'بطاقة',          'code' => 'card',   'sort_order' => 3],
            ['name' => 'شيك',            'code' => 'cheque', 'sort_order' => 4],
            ['name' => 'محفظة إلكترونية','code' => 'wallet', 'sort_order' => 5],
            ['name' => 'آجل',            'code' => 'credit', 'sort_order' => 6],
        ];
        foreach ($defaults as $d) {
            static::create(array_merge($d, ['tenant_id' => $tenantId, 'is_active' => true]));
        }
    }
}
