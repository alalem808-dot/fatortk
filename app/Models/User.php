<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use Notifiable, HasRoles;

    protected $fillable = ['tenant_id', 'name', 'username', 'email', 'password', 'role', 'is_active'];
    protected $hidden   = ['password', 'remember_token'];
    protected $casts    = ['is_active' => 'boolean', 'last_login' => 'datetime'];

    public function tenant()   { return $this->belongsTo(Tenant::class); }
    public function invoices() { return $this->hasMany(Invoice::class, 'created_by'); }

    /** المخازن المرتبطة بهذا المستخدم */
    public function warehouses()
    {
        return $this->belongsToMany(Warehouse::class, 'user_warehouses');
    }

    /** هل المستخدم Admin */
    public function isAdmin(): bool
    {
        return $this->role === 'admin' || $this->hasRole('admin');
    }

    /** المخزن الافتراضي للمستخدم */
    public function getDefaultWarehouse(): ?Warehouse
    {
        if ($this->isAdmin()) {
            return Warehouse::getDefault($this->tenant_id);
        }
        return $this->warehouses()->where('is_active', true)->first()
            ?? Warehouse::getDefault($this->tenant_id);
    }

    /** هل المستخدم يملك صلاحية الوصول لمخزن معين */
    public function canAccessWarehouse(int $warehouseId): bool
    {
        if ($this->isAdmin()) return true;
        $ids = $this->warehouses->pluck('id')->toArray();
        if (empty($ids)) return true; // بدون مخازن محددة = يصل لكل المخازن
        return in_array($warehouseId, $ids);
    }
}
