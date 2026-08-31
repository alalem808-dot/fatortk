<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            'invoices.view', 'invoices.create', 'invoices.edit', 'invoices.delete', 'invoices.send', 'invoices.export',
            'customers.view', 'customers.create', 'customers.edit', 'customers.delete',
            'products.view', 'products.create', 'products.edit', 'products.delete',
            'purchases.view', 'purchases.create', 'purchases.edit', 'purchases.delete',
            'suppliers.view', 'suppliers.create', 'suppliers.edit', 'suppliers.delete',
            'expenses.view', 'expenses.create', 'expenses.edit', 'expenses.delete',
            'reports.view_sales', 'reports.view_stock', 'reports.view_profit',
            'stocktaking.view', 'stocktaking.create', 'stocktaking.confirm',
            'returns.view', 'returns.create',
            'users.view', 'users.create', 'users.edit', 'users.delete',
            'settings.view', 'settings.edit',
            'currencies.view', 'currencies.manage',
            'warehouses.view', 'warehouses.manage',
            'quick_sale.access',
            'pos.access',
        ];

        foreach ($permissions as $perm) {
            Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
        }

        // admin - كل الصلاحيات
        $admin = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $admin->syncPermissions($permissions);

        // accountant - فواتير + مدفوعات + تقارير + مصروفات (بدون حذف)
        $accountant = Role::firstOrCreate(['name' => 'accountant', 'guard_name' => 'web']);
        $accountant->syncPermissions([
            'invoices.view', 'invoices.create', 'invoices.edit', 'invoices.send', 'invoices.export',
            'customers.view',
            'expenses.view', 'expenses.create', 'expenses.edit',
            'reports.view_sales', 'reports.view_profit',
            'returns.view',
        ]);

        // sales - فواتير + عملاء (بدون حذف + بدون تقارير مالية)
        $sales = Role::firstOrCreate(['name' => 'sales', 'guard_name' => 'web']);
        $sales->syncPermissions([
            'invoices.view', 'invoices.create', 'invoices.edit', 'invoices.send',
            'customers.view', 'customers.create', 'customers.edit',
            'products.view',
            'returns.view', 'returns.create',
            'quick_sale.access',
            'pos.access',
        ]);

        // purchasing - مشتريات + موردون + منتجات
        $purchasing = Role::firstOrCreate(['name' => 'purchasing', 'guard_name' => 'web']);
        $purchasing->syncPermissions([
            'purchases.view', 'purchases.create', 'purchases.edit',
            'suppliers.view', 'suppliers.create', 'suppliers.edit',
            'products.view', 'products.create', 'products.edit',
        ]);

        // warehouse - منتجات + مخزون + جرد (بدون أسعار)
        $warehouse = Role::firstOrCreate(['name' => 'warehouse', 'guard_name' => 'web']);
        $warehouse->syncPermissions([
            'products.view', 'products.edit',
            'stocktaking.view', 'stocktaking.create', 'stocktaking.confirm',
            'reports.view_stock',
            'warehouses.view', 'warehouses.manage',
        ]);

        // staff - عرض فقط
        $staff = Role::firstOrCreate(['name' => 'staff', 'guard_name' => 'web']);
        $staff->syncPermissions([
            'invoices.view', 'customers.view', 'products.view',
            'purchases.view', 'suppliers.view', 'expenses.view',
            'reports.view_sales', 'stocktaking.view', 'returns.view',
        ]);
    }
}
