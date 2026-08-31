<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Expense extends Model
{
    protected $fillable = [
        'tenant_id', 'category_id', 'description', 'amount', 'currency',
        'exchange_rate', 'payment_method', 'expense_date', 'attachment', 'notes', 'created_by',
    ];
    protected $casts = ['expense_date' => 'date'];

    public function tenant()   { return $this->belongsTo(Tenant::class); }
    public function category() { return $this->belongsTo(ExpenseCategory::class, 'category_id'); }
    public function creator()  { return $this->belongsTo(User::class, 'created_by'); }

    // المبلغ بالعملة الأساسية
    public function getAmountBaseAttribute(): float
    {
        return $this->amount * $this->exchange_rate;
    }
}
