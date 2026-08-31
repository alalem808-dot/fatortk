<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Permission;

class UserController extends Controller
{
    // قائمة الصلاحيات مع تسمياتها العربية مجمّعة
    public static function allPermissions(): array
    {
        return [
            'الفواتير' => [
                'invoices.view'   => 'عرض الفواتير',
                'invoices.create' => 'إنشاء فاتورة',
                'invoices.edit'   => 'تعديل فاتورة',
                'invoices.delete' => 'حذف فاتورة',
                'invoices.send'   => 'إرسال فاتورة',
                'invoices.export' => 'تصدير الفواتير',
            ],
            'العملاء' => [
                'customers.view'   => 'عرض العملاء',
                'customers.create' => 'إضافة عميل',
                'customers.edit'   => 'تعديل عميل',
                'customers.delete' => 'حذف عميل',
            ],
            'المنتجات' => [
                'products.view'   => 'عرض المنتجات',
                'products.create' => 'إضافة منتج',
                'products.edit'   => 'تعديل منتج',
                'products.delete' => 'حذف منتج',
            ],
            'المشتريات' => [
                'purchases.view'   => 'عرض المشتريات',
                'purchases.create' => 'إنشاء أمر شراء',
                'purchases.edit'   => 'تعديل حالة الشراء',
                'purchases.delete' => 'حذف أمر شراء',
            ],
            'الموردون' => [
                'suppliers.view'   => 'عرض الموردين',
                'suppliers.create' => 'إضافة مورد',
                'suppliers.edit'   => 'تعديل مورد',
                'suppliers.delete' => 'حذف مورد',
            ],
            'المصروفات' => [
                'expenses.view'   => 'عرض المصروفات',
                'expenses.create' => 'إضافة مصروف',
                'expenses.edit'   => 'تعديل مصروف',
                'expenses.delete' => 'حذف مصروف',
            ],
            'المرتجعات' => [
                'returns.view'   => 'عرض المرتجعات',
                'returns.create' => 'إنشاء مرتجع',
            ],
            'المخزون والجرد' => [
                'stocktaking.view'    => 'عرض الجرد',
                'stocktaking.create'  => 'إنشاء جلسة جرد',
                'stocktaking.confirm' => 'اعتماد الجرد',
                'warehouses.view'     => 'عرض المخازن',
                'warehouses.manage'   => 'إدارة المخازن',
            ],
            'التقارير' => [
                'reports.view_sales'  => 'تقرير المبيعات',
                'reports.view_stock'  => 'تقرير المخزون',
                'reports.view_profit' => 'تقرير الأرباح',
            ],
            'المستخدمون' => [
                'users.view'   => 'عرض المستخدمين',
                'users.create' => 'إضافة مستخدم',
                'users.edit'   => 'تعديل مستخدم',
                'users.delete' => 'حذف مستخدم',
            ],
            'الإعدادات' => [
                'settings.view'      => 'عرض الإعدادات',
                'settings.edit'      => 'تعديل الإعدادات',
                'currencies.view'    => 'عرض العملات',
                'currencies.manage'  => 'إدارة العملات',
            ],
            'البيع المباشر' => [
                'quick_sale.access' => 'الوصول للبيع المباشر',
                'pos.access'        => 'الوصول لنقطة البيع (POS)',
            ],
        ];
    }

    public function index()
    {
        $users = User::where('tenant_id', auth()->user()->tenant_id)
            ->with('permissions')
            ->get();
        return view('users.index', compact('users'));
    }

    public function create()
    {
        $permissionGroups = self::allPermissions();
        // Admin فقط يرى كل المخازن لتحديد مخازن المستخدم الجديد
        $warehouses = \App\Models\Warehouse::where('tenant_id', auth()->user()->tenant_id)
            ->where('is_active', true)
            ->get();
        return view('users.create', compact('permissionGroups', 'warehouses'));
    }

    public function store(Request $request)
    {
        $tenantId = auth()->user()->tenant_id;

        $request->validate([
            'name'        => 'required|string|max:255',
            'username'    => 'required|string|unique:users,username|max:50',
            'email'       => 'required|email|unique:users,email',
            'password'    => 'required|min:8|confirmed',
            'permissions' => 'nullable|array',
            'warehouses'  => 'nullable|array',
            'warehouses.*' => [Rule::exists('warehouses', 'id')->where('tenant_id', $tenantId)],
        ]);

        $user = User::create([
            'tenant_id' => auth()->user()->tenant_id,
            'name'      => $request->name,
            'username'  => $request->username,
            'email'     => $request->email,
            'password'  => Hash::make($request->password),
            'role'      => 'employee',
            'is_active' => true,
        ]);

        $perms = $request->input('permissions', []);
        if (!empty($perms)) {
            $validPerms = \Spatie\Permission\Models\Permission::whereIn('name', $perms)->pluck('name')->toArray();
            $user->syncPermissions($validPerms);
        }

        if ($request->has('warehouses')) {
            $user->warehouses()->sync($request->input('warehouses', []));
        }

        // مسح كاش الصلاحيات لتطبيق التغييرات فوراً
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        return redirect()->route('users.index')->with('success', 'تم إضافة المستخدم.');
    }

    public function edit(User $user)
    {
        abort_if($user->tenant_id !== auth()->user()->tenant_id, 403);
        $permissionGroups   = self::allPermissions();
        $userPermissions    = $user->getDirectPermissions()->pluck('name')->toArray();
        $warehouses         = \App\Models\Warehouse::where('tenant_id', auth()->user()->tenant_id)
            ->where('is_active', true)
            ->get();
        $userWarehouseIds   = $user->warehouses->pluck('id')->toArray();
        return view('users.edit', compact('user', 'permissionGroups', 'userPermissions', 'warehouses', 'userWarehouseIds'));
    }

    public function update(Request $request, User $user)
    {
        abort_if($user->tenant_id !== auth()->user()->tenant_id, 403);
        abort_if($user->id === auth()->id(), 403, 'لا يمكنك تعديل صلاحياتك بنفسك.');

        $tenantId = auth()->user()->tenant_id;

        $request->validate([
            'name'        => 'required|string|max:255',
            'email'       => 'required|email|unique:users,email,' . $user->id,
            'username'    => 'required|string|max:50|unique:users,username,' . $user->id,
            'permissions' => 'nullable|array',
            'warehouses'  => 'nullable|array',
            'warehouses.*' => [Rule::exists('warehouses', 'id')->where('tenant_id', $tenantId)],
        ]);

        $user->update($request->only('name', 'email', 'username'));
        $validPerms = \Spatie\Permission\Models\Permission::whereIn('name', $request->input('permissions', []))->pluck('name')->toArray();
        $user->syncPermissions($validPerms);

        $user->warehouses()->sync($request->input('warehouses', []));

        // مسح كاش الصلاحيات لتطبيق التغييرات فوراً
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        return redirect()->route('users.index')->with('success', 'تم تحديث بيانات المستخدم.');
    }

    public function toggleActive(User $user)
    {
        abort_if($user->tenant_id !== auth()->user()->tenant_id, 403);
        abort_if($user->id === auth()->id(), 403);
        $user->update(['is_active' => !$user->is_active]);
        return back()->with('success', 'تم تحديث حالة المستخدم.');
    }

    public function destroy(User $user)
    {
        abort_if($user->tenant_id !== auth()->user()->tenant_id, 403);
        abort_if($user->id === auth()->id(), 403);
        $user->delete();
        return back()->with('success', 'تم حذف المستخدم.');
    }

    public function changePasswordForm()
    {
        return view('users.change-password');
    }

    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'password'         => 'required|min:8|confirmed',
        ]);

        if (!Hash::check($request->current_password, auth()->user()->password)) {
            return back()->withErrors(['current_password' => 'كلمة المرور الحالية غير صحيحة.']);
        }

        auth()->user()->update(['password' => Hash::make($request->password)]);
        return back()->with('success', 'تم تغيير كلمة المرور.');
    }

    public function resetPassword(Request $request, User $user)
    {
        abort_if($user->tenant_id !== auth()->user()->tenant_id, 403);
        $request->validate(['password' => 'required|min:8|confirmed']);
        $user->update(['password' => Hash::make($request->password)]);
        return back()->with('success', 'تم إعادة تعيين كلمة المرور.');
    }
}
