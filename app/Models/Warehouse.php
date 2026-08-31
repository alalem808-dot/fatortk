<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Warehouse extends Model
{
    protected $fillable = ['tenant_id', 'name', 'location', 'notes', 'is_default', 'is_active'];
    protected $casts = ['is_default' => 'boolean', 'is_active' => 'boolean'];

    public function tenant()  { return $this->belongsTo(Tenant::class); }
    public function stocks()  { return $this->hasMany(WarehouseStock::class); }
    public function movements() { return $this->hasMany(StockMovement::class); }

    public function getStockForProduct(int $productId): float
    {
        return $this->stocks()->where('product_id', $productId)->value('quantity') ?? 0;
    }

    public static function getDefault(int $tenantId): ?self
    {
        return static::where('tenant_id', $tenantId)
            ->where('is_default', true)
            ->where('is_active', true)
            ->first()
            ?? static::where('tenant_id', $tenantId)->where('is_active', true)->first();
    }
}
