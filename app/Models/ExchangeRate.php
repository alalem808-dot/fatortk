<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class ExchangeRate extends Model
{
    protected $fillable = ['currency_id', 'rate', 'date'];
    protected $casts = ['date' => 'date'];

    public function currency() { return $this->belongsTo(Currency::class); }
}
