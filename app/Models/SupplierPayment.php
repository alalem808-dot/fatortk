<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SupplierPayment extends Model
{
    protected $fillable = [
        'tenant_id', 'purchase_id', 'supplier_id', 'payment_date',
        'amount', 'payment_method', 'reference_number', 'notes', 'created_by',
    ];

    protected $casts = ['payment_date' => 'date'];

    public function tenant()   { return $this->belongsTo(Tenant::class); }
    public function purchase() { return $this->belongsTo(Purchase::class); }
    public function supplier() { return $this->belongsTo(Supplier::class); }
    public function creator()  { return $this->belongsTo(User::class, 'created_by'); }
}
