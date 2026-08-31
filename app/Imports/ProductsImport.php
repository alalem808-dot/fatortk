<?php

namespace App\Imports;

use App\Models\Category;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\Warehouse;
use App\Models\WarehouseStock;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;

class ProductsImport implements ToModel, WithStartRow, SkipsOnError, WithBatchInserts, WithChunkReading
{
    use SkipsErrors;

    private int $tenantId;
    private ?int $warehouseId;
    private array $categoryCache = [];

    // ترتيب الأعمدة: 0=اسم_المنتج, 1=الفئة, 2=sku, 3=الباركود,
    //                4=سعر_البيع, 5=سعر_التكلفة, 6=نسبة_الضريبة,
    //                7=الكمية, 8=حد_التنبيه, 9=الوحدة, 10=الحالة

    public function __construct(int $tenantId, ?int $warehouseId = null)
    {
        $this->tenantId    = $tenantId;
        $this->warehouseId = $warehouseId ?? Warehouse::getDefault($tenantId)?->id;
    }

    public function startRow(): int
    {
        return 2; // تخطي صف العناوين
    }

    public function model(array $row): ?Product
    {
        $name = trim((string) ($row[0] ?? ''));
        if ($name === '') return null; // تخطي الصفوف الفارغة

        $unitPrice = (float) ($row[4] ?? 0);
        if ($unitPrice <= 0 && trim((string)($row[4] ?? '')) === '') return null;

        $categoryId = $this->resolveCategoryId(trim((string) ($row[1] ?? '')));
        $sku        = trim((string) ($row[2] ?? '')) ?: null;
        $barcode    = trim((string) ($row[3] ?? '')) ?: null;
        $costPrice  = (float) ($row[5] ?? 0);
        $taxRate    = (float) ($row[6] ?? 0);
        $qty        = (float) ($row[7] ?? 0);
        $minAlert   = (float) ($row[8] ?? 0);
        $unit       = trim((string) ($row[9] ?? '')) ?: null;
        $status     = trim((string) ($row[10] ?? 'نشط')) === 'نشط' ? 'active' : 'inactive';

        $conditions = ['tenant_id' => $this->tenantId];
        if ($sku) {
            $conditions['sku'] = $sku;
        } else {
            $conditions['name'] = $name;
        }

        $product = Product::updateOrCreate($conditions, [
            'tenant_id'       => $this->tenantId,
            'category_id'     => $categoryId,
            'name'            => $name,
            'sku'             => $sku,
            'barcode'         => $barcode,
            'unit_price'      => $unitPrice,
            'cost_price'      => $costPrice,
            'tax_rate'        => $taxRate,
            'stock_quantity'  => $qty,
            'min_stock_alert' => $minAlert,
            'unit'            => $unit,
            'status'          => $status,
        ]);

        if ($qty > 0 && $this->warehouseId) {
            WarehouseStock::updateOrCreate(
                ['warehouse_id' => $this->warehouseId, 'product_id' => $product->id],
                ['quantity' => $qty]
            );

            // تحديث stock_quantity ليعكس مجموع كل المخازن
            $totalStock = WarehouseStock::where('product_id', $product->id)->sum('quantity');
            $product->update(['stock_quantity' => $totalStock]);

            StockMovement::create([
                'tenant_id'       => $this->tenantId,
                'product_id'      => $product->id,
                'warehouse_id'    => $this->warehouseId,
                'type'            => 'in',
                'quantity'        => $qty,
                'quantity_before' => 0,
                'quantity_after'  => $qty,
                'reference_type'  => 'import',
                'reference_id'    => null,
                'notes'           => 'استيراد من Excel',
                'created_by'      => auth()->id(),
            ]);
        }

        return null;
    }

    public function batchSize(): int { return 100; }
    public function chunkSize(): int { return 100; }

    private function resolveCategoryId(string $name): ?int
    {
        if ($name === '') return null;

        if (!isset($this->categoryCache[$name])) {
            $this->categoryCache[$name] = Category::firstOrCreate(
                ['tenant_id' => $this->tenantId, 'name' => $name]
            )->id;
        }

        return $this->categoryCache[$name];
    }
}
