<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class InvoiceReturn extends Model
{
    protected $fillable = ['tenant_id', 'invoice_id', 'return_date', 'reason', 'total', 'created_by'];
    protected $casts = ['return_date' => 'date'];

    public function tenant()  { return $this->belongsTo(Tenant::class); }
    public function invoice() { return $this->belongsTo(Invoice::class); }
    public function items()   { return $this->hasMany(InvoiceReturnItem::class, 'return_id'); }
    public function creator() { return $this->belongsTo(User::class, 'created_by'); }
}
