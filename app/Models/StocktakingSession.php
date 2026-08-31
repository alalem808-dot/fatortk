<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class StocktakingSession extends Model
{
    protected $fillable = ['tenant_id', 'warehouse_id', 'name', 'date', 'status', 'notes', 'created_by'];
    protected $casts = ['date' => 'date'];

    public function tenant()    { return $this->belongsTo(Tenant::class); }
    public function warehouse() { return $this->belongsTo(Warehouse::class); }
    public function items()     { return $this->hasMany(StocktakingItem::class, 'session_id'); }
    public function creator()   { return $this->belongsTo(User::class, 'created_by'); }
}
