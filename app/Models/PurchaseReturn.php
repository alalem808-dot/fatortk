<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseReturn extends Model
{
    protected $fillable = [
        'tenant_id', 'purchase_id', 'reference', 'return_date', 'reason', 'total', 'created_by',
    ];

    protected $casts = ['return_date' => 'date'];

    public function tenant()   { return $this->belongsTo(Tenant::class); }
    public function purchase() { return $this->belongsTo(Purchase::class); }
    public function items()    { return $this->hasMany(PurchaseReturnItem::class); }
    public function creator()  { return $this->belongsTo(User::class, 'created_by'); }
}
