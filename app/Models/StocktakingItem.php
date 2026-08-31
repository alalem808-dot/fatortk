<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class StocktakingItem extends Model
{
    protected $fillable = ['session_id', 'product_id', 'system_qty', 'actual_qty', 'difference'];

    public function session() { return $this->belongsTo(StocktakingSession::class, 'session_id'); }
    public function product() { return $this->belongsTo(Product::class); }
}
