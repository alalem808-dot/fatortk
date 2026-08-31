<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Currency extends Model
{
    protected $fillable = ['tenant_id', 'code', 'name', 'symbol', 'is_base', 'is_active'];
    protected $casts = ['is_base' => 'boolean', 'is_active' => 'boolean'];

    public function tenant()        { return $this->belongsTo(Tenant::class); }
    public function exchangeRates() { return $this->hasMany(ExchangeRate::class); }

    public function getLatestRateAttribute(): float
    {
        if ($this->is_base) return 1;
        $rate = $this->exchangeRates()->latest('date')->first();
        return $rate ? (float) $rate->rate : 1;
    }
}
