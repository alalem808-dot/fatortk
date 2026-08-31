<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class InvoiceReturnItem extends Model
{
    protected $fillable = [
        'return_id', 'invoice_item_id', 'product_id',
        'description', 'quantity', 'unit_price', 'total',
    ];

    public function invoiceReturn() { return $this->belongsTo(InvoiceReturn::class, 'return_id'); }
    public function product()       { return $this->belongsTo(Product::class); }
    public function invoiceItem()   { return $this->belongsTo(InvoiceItem::class); }
}
