<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class ExpenseCategory extends Model
{
    protected $fillable = ['tenant_id', 'name'];
    public function expenses() { return $this->hasMany(Expense::class, 'category_id'); }
}
