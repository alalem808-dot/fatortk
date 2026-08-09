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

    protected $casts = ['unit_price' => 'float', 'cost_price' => 'float', 'tax_rate' => 'float'];

    public function tenant() { return $this->belongsTo(Tenant::class); }
    public function category() { return $this->belongsTo(Category::class); }
    public function stockMovements() { return $this->hasMany(StockMovement::class); }

    public function isLowStock(): bool
    {
        return $this->stock_quantity <= $this->min_stock_alert;
    }
}
