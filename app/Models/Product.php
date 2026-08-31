<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'tenant_id', 'category_id', 'name', 'description', 'sku', 'barcode',
        'unit_price', 'cost_price', 'tax_rate', 'stock_quantity',
        'min_stock_alert', 'unit', 'image', 'status',
    ];

    protected $casts = [
        'unit_price'      => 'float',
        'cost_price'      => 'float',
        'tax_rate'        => 'float',
        'stock_quantity'  => 'float',
        'min_stock_alert' => 'float',
    ];

    public function tenant() { return $this->belongsTo(Tenant::class); }
    public function category() { return $this->belongsTo(Category::class); }
    public function stockMovements() { return $this->hasMany(StockMovement::class); }
    public function warehouseStocks() { return $this->hasMany(WarehouseStock::class); }

    public function isLowStock(): bool
    {
        return $this->min_stock_alert > 0 && $this->stock_quantity <= $this->min_stock_alert;
    }
}
