<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\TemplateController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\CurrencyController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\StocktakingController;
use App\Http\Controllers\InvoiceReturnController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\PaymentMethodController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\PurchaseReturnController;
use App\Http\Controllers\WarehouseController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\QuickSaleController;
use App\Http\Controllers\SuperAdmin\AuthController as SuperAuthController;
use App\Http\Controllers\SuperAdmin\DashboardController as SuperDashController;
use App\Http\Controllers\SuperAdmin\PlansController;
use App\Http\Controllers\SuperAdmin\BackupController as SuperBackupController;
use App\Http\Controllers\BackupController;

// ===== الصفحة الرئيسية =====
Route::get('/', fn() => view('welcome'))->name('home');
Route::get('/pricing', fn() => view('pricing'))->name('pricing');

// ===== Super Admin =====
Route::prefix('super-admin')->name('super_admin.')->group(function () {

    Route::middleware('guest:super_admin')->group(function () {
        Route::get('/login',  [SuperAuthController::class, 'showLogin'])->name('login');
        Route::post('/login', [SuperAuthController::class, 'login']);
    });

    Route::middleware('super_admin')->group(function () {
        Route::post('/logout',    [SuperAuthController::class, 'logout'])->name('logout');
        Route::get('/dashboard',  [SuperDashController::class, 'index'])->name('dashboard');

        // المشتركون
        Route::get('/tenants',                [SuperDashController::class, 'tenants'])->name('tenants');
        Route::get('/tenants/create',         [SuperDashController::class, 'createTenant'])->name('tenants.create');
        Route::post('/tenants',               [SuperDashController::class, 'storeTenant'])->name('tenants.store');
        Route::get('/tenants/{tenant}',       [SuperDashController::class, 'showTenant'])->name('tenants.show');
        Route::put('/tenants/{tenant}',       [SuperDashController::class, 'updateTenant'])->name('tenants.update');
        Route::delete('/tenants/{tenant}',    [SuperDashController::class, 'deleteTenant'])->name('tenants.delete');
        Route::post('/tenants/{tenant}/toggle-currencies', [SuperDashController::class, 'toggleCurrencies'])->name('tenants.toggle-currencies');
        Route::post('/users/{user}/reset-password', [SuperDashController::class, 'resetUserPassword'])->name('users.reset-password');

        // خطط الاشتراك
        Route::get('/plans',             [PlansController::class, 'index'])->name('plans');
        Route::get('/plans/{plan}/edit', [PlansController::class, 'edit'])->name('plans.edit');
        Route::put('/plans/{plan}',      [PlansController::class, 'update'])->name('plans.update');

        // الإعدادات
        Route::get('/settings',  [SuperDashController::class, 'settings'])->name('settings');
        Route::put('/settings',  [SuperDashController::class, 'updateSettings'])->name('settings.update');

        // العملات
        Route::get('/currencies',                              [SuperDashController::class, 'currencies'])->name('currencies');
        Route::post('/currencies',                             [SuperDashController::class, 'storeCurrency'])->name('currencies.store');
        Route::put('/currencies/{globalCurrency}',             [SuperDashController::class, 'updateCurrency'])->name('currencies.update');
        Route::post('/currencies/{globalCurrency}/toggle',     [SuperDashController::class, 'toggleCurrency'])->name('currencies.toggle');
        Route::delete('/currencies/{globalCurrency}',          [SuperDashController::class, 'destroyCurrency'])->name('currencies.destroy');

        // ===== النسخ الاحتياطي (Super Admin) =====
        Route::get('/backups',               [SuperBackupController::class, 'index'])->name('backups.index');
        Route::post('/backups/full',         [SuperBackupController::class, 'storeFullBackup'])->name('backups.full');
        Route::post('/backups/tenant',       [SuperBackupController::class, 'storeTenantBackup'])->name('backups.tenant');
        Route::post('/backups/prune',        [SuperBackupController::class, 'prune'])->name('backups.prune');
        Route::get('/backups/{id}/download', [SuperBackupController::class, 'download'])->name('backups.download');
        Route::delete('/backups/{id}',       [SuperBackupController::class, 'destroy'])->name('backups.destroy');

        // ===== الإيرادات والمدفوعات =====
        Route::get('/revenue',              [\App\Http\Controllers\SuperAdmin\RevenueController::class, 'index'])->name('revenue.index');
        Route::post('/revenue',             [\App\Http\Controllers\SuperAdmin\RevenueController::class, 'store'])->name('revenue.store');
        Route::get('/revenue/export',       [\App\Http\Controllers\SuperAdmin\RevenueController::class, 'export'])->name('revenue.export');
        Route::delete('/revenue/{payment}', [\App\Http\Controllers\SuperAdmin\RevenueController::class, 'destroy'])->name('revenue.destroy');

        // ===== الإشعارات والتنبيهات =====
        Route::get('/notifications',                      [\App\Http\Controllers\SuperAdmin\NotificationController::class, 'index'])->name('notifications.index');
        Route::post('/notifications/send',                [\App\Http\Controllers\SuperAdmin\NotificationController::class, 'send'])->name('notifications.send');
        Route::post('/notifications/{tenant}/extend',     [\App\Http\Controllers\SuperAdmin\NotificationController::class, 'extend'])->name('notifications.extend');

        // ===== سجل النشاط =====
        Route::get('/activity-log', [\App\Http\Controllers\SuperAdmin\ActivityLogController::class, 'index'])->name('activity_log');

        // ===== إحصائيات الاستخدام =====
        Route::get('/usage-stats',        [\App\Http\Controllers\SuperAdmin\UsageStatsController::class, 'index'])->name('usage_stats');
        Route::get('/usage-stats/export', [\App\Http\Controllers\SuperAdmin\UsageStatsController::class, 'export'])->name('usage_stats.export');

        // ===== الدعم الفني =====
        Route::get('/support',                      [\App\Http\Controllers\SuperAdmin\SupportController::class, 'index'])->name('support.index');
        Route::post('/support',                     [\App\Http\Controllers\SuperAdmin\SupportController::class, 'store'])->name('support.store');
        Route::get('/support/{ticket}',             [\App\Http\Controllers\SuperAdmin\SupportController::class, 'show'])->name('support.show');
        Route::post('/support/{ticket}/reply',      [\App\Http\Controllers\SuperAdmin\SupportController::class, 'reply'])->name('support.reply');
        Route::patch('/support/{ticket}/status',    [\App\Http\Controllers\SuperAdmin\SupportController::class, 'updateStatus'])->name('support.status');
        Route::delete('/support/{ticket}',          [\App\Http\Controllers\SuperAdmin\SupportController::class, 'destroy'])->name('support.destroy');
    });
});

// ===== Auth =====
Route::middleware('guest')->group(function () {
    Route::get('/login',    [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login',   [AuthController::class, 'login'])->middleware('throttle:5,1');
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register',[AuthController::class, 'register'])->middleware('throttle:3,1');
});

Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// رابط عام لعرض الفاتورة (بدون تسجيل دخول)
Route::get('/invoice/{token}/view', [InvoiceController::class, 'publicView'])->name('invoices.public');

// صفحة انتهاء الاشتراك
Route::get('/subscription/expired', function () {
    return view('auth.subscription_expired');
})->name('subscription.expired')->middleware('auth');

// ===== المسارات المحمية =====
Route::middleware(['auth', 'tenant'])->group(function () {

    Route::get('/app', fn() => redirect()->route('dashboard'));
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // تغيير كلمة المرور (متاح للجميع)
    Route::get('/change-password',  [UserController::class, 'changePasswordForm'])->name('users.change-password');
    Route::post('/change-password', [UserController::class, 'changePassword']);

    // ===== البيع المباشر =====
    Route::get('/quick-sale',                   [QuickSaleController::class, 'index'])->name('quick-sale.index')->middleware('permission:quick_sale.access');
    Route::post('/quick-sale',                  [QuickSaleController::class, 'store'])->name('quick-sale.store')->middleware('permission:quick_sale.access');
    Route::get('/quick-sale/product-search',    [QuickSaleController::class, 'productSearch'])->name('quick-sale.search')->middleware('permission:quick_sale.access');
    Route::get('/quick-sale/{invoice}/receipt', [QuickSaleController::class, 'receipt'])->name('quick-sale.receipt')->middleware('permission:quick_sale.access');

    // ===== الفواتير =====
    Route::get('/invoices',                       [InvoiceController::class, 'index'])->name('invoices.index')->middleware('permission:invoices.view');
    Route::get('/invoices/create',                [InvoiceController::class, 'create'])->name('invoices.create')->middleware('permission:invoices.create');
    Route::post('/invoices',                      [InvoiceController::class, 'store'])->name('invoices.store')->middleware('permission:invoices.create');
    Route::get('/invoices/{invoice}',             [InvoiceController::class, 'show'])->name('invoices.show')->middleware('permission:invoices.view');
    Route::get('/invoices/{invoice}/edit',        [InvoiceController::class, 'edit'])->name('invoices.edit')->middleware('permission:invoices.edit');
    Route::put('/invoices/{invoice}',             [InvoiceController::class, 'update'])->name('invoices.update')->middleware('permission:invoices.edit');
    Route::delete('/invoices/{invoice}',          [InvoiceController::class, 'destroy'])->name('invoices.destroy')->middleware('permission:invoices.delete');
    Route::get('/invoices/{invoice}/pdf',         [InvoiceController::class, 'downloadPdf'])->name('invoices.pdf')->middleware('permission:invoices.view');
    Route::get('/invoices/{invoice}/whatsapp',    [InvoiceController::class, 'whatsapp'])->name('invoices.whatsapp')->middleware('permission:invoices.view');
    Route::patch('/invoices/{invoice}/status',    [InvoiceController::class, 'updateStatus'])->name('invoices.status')->middleware('permission:invoices.edit');
    Route::post('/invoices/{invoice}/send-email', [InvoiceController::class, 'sendEmail'])->name('invoices.email')->middleware('permission:invoices.edit');
    Route::get('/invoices/{invoice}/return',      [InvoiceReturnController::class, 'create'])->name('returns.create')->middleware('permission:returns.create');
    Route::post('/invoices/{invoice}/return',     [InvoiceReturnController::class, 'store'])->name('returns.store')->middleware('permission:returns.create');

    // المدفوعات
    Route::post('/payments', [PaymentController::class, 'store'])->name('payments.store')->middleware('permission:invoices.create');

    // دفعات الموردين
    Route::post('/supplier-payments',                    [\App\Http\Controllers\SupplierPaymentController::class, 'store'])->name('supplier-payments.store')->middleware('permission:purchases.edit');
    Route::delete('/supplier-payments/{supplierPayment}',[\App\Http\Controllers\SupplierPaymentController::class, 'destroy'])->name('supplier-payments.destroy')->middleware('permission:purchases.edit');

    // مرتجعات المبيعات - قائمة
    Route::get('/returns', [InvoiceReturnController::class, 'index'])->name('returns.index')->middleware('permission:returns.view');

    // ===== المنتجات =====
    Route::get('/products',                 [ProductController::class, 'index'])->name('products.index')->middleware('permission:products.view');
    Route::get('/products/import',          [ProductController::class, 'importForm'])->name('products.import')->middleware('permission:products.create');
    Route::post('/products/import',         [ProductController::class, 'import'])->name('products.import.store')->middleware('permission:products.create');
    Route::get('/products/import/template', [ProductController::class, 'downloadTemplate'])->name('products.import.template')->middleware('permission:products.create');
    Route::get('/products/create',          [ProductController::class, 'create'])->name('products.create')->middleware('permission:products.create');
    Route::post('/products',                [ProductController::class, 'store'])->name('products.store')->middleware('permission:products.create');
    Route::get('/products/{product}',       [ProductController::class, 'show'])->name('products.show')->middleware('permission:products.view');
    Route::get('/products/{product}/edit',  [ProductController::class, 'edit'])->name('products.edit')->middleware('permission:products.edit');
    Route::put('/products/{product}',       [ProductController::class, 'update'])->name('products.update')->middleware('permission:products.edit');
    Route::delete('/products/{product}',    [ProductController::class, 'destroy'])->name('products.destroy')->middleware('permission:products.delete');
    Route::post('/products/{product}/stock',[ProductController::class, 'adjustStock'])->name('products.stock')->middleware('permission:products.edit');

    // ===== العملاء =====
    Route::get('/customers',                          [CustomerController::class, 'index'])->name('customers.index')->middleware('permission:customers.view');
    Route::get('/customers/create',                   [CustomerController::class, 'create'])->name('customers.create')->middleware('permission:customers.create');
    Route::post('/customers',                         [CustomerController::class, 'store'])->name('customers.store')->middleware('permission:customers.create');
    Route::get('/customers/{customer}',               [CustomerController::class, 'show'])->name('customers.show')->middleware('permission:customers.view');
    Route::get('/customers/{customer}/export/pdf',    [CustomerController::class, 'exportPdf'])->name('customers.export.pdf')->middleware('permission:customers.view');
    Route::get('/customers/{customer}/export/excel',  [CustomerController::class, 'exportExcel'])->name('customers.export.excel')->middleware('permission:customers.view');
    Route::get('/customers/{customer}/edit',          [CustomerController::class, 'edit'])->name('customers.edit')->middleware('permission:customers.edit');
    Route::put('/customers/{customer}',               [CustomerController::class, 'update'])->name('customers.update')->middleware('permission:customers.edit');
    Route::delete('/customers/{customer}',            [CustomerController::class, 'destroy'])->name('customers.destroy')->middleware('permission:customers.delete');
    Route::post('/customers/{customer}/bulk-payment', [CustomerController::class, 'bulkPayment'])->name('customers.bulk-payment')->middleware('permission:invoices.create');

    // ===== الموردون =====
    Route::get('/suppliers',              [SupplierController::class, 'index'])->name('suppliers.index')->middleware('permission:suppliers.view');
    Route::get('/suppliers/create',       [SupplierController::class, 'create'])->name('suppliers.create')->middleware('permission:suppliers.create');
    Route::post('/suppliers',             [SupplierController::class, 'store'])->name('suppliers.store')->middleware('permission:suppliers.create');
    Route::get('/suppliers/{supplier}',   [SupplierController::class, 'show'])->name('suppliers.show')->middleware('permission:suppliers.view');
    Route::get('/suppliers/{supplier}/export/pdf',   [SupplierController::class, 'exportPdf'])->name('suppliers.export.pdf')->middleware('permission:suppliers.view');
    Route::get('/suppliers/{supplier}/export/excel', [SupplierController::class, 'exportExcel'])->name('suppliers.export.excel')->middleware('permission:suppliers.view');
    Route::get('/suppliers/{supplier}/edit', [SupplierController::class, 'edit'])->name('suppliers.edit')->middleware('permission:suppliers.edit');
    Route::put('/suppliers/{supplier}',      [SupplierController::class, 'update'])->name('suppliers.update')->middleware('permission:suppliers.edit');
    Route::delete('/suppliers/{supplier}',   [SupplierController::class, 'destroy'])->name('suppliers.destroy')->middleware('permission:suppliers.delete');

    // ===== المشتريات =====
    Route::get('/purchases',                      [PurchaseController::class, 'index'])->name('purchases.index')->middleware('permission:purchases.view');
    Route::get('/purchases/create',               [PurchaseController::class, 'create'])->name('purchases.create')->middleware('permission:purchases.create');
    Route::post('/purchases',                     [PurchaseController::class, 'store'])->name('purchases.store')->middleware('permission:purchases.create');
    Route::get('/purchases/{purchase}',           [PurchaseController::class, 'show'])->name('purchases.show')->middleware('permission:purchases.view');
    Route::get('/purchases/{purchase}/pdf',       [PurchaseController::class, 'downloadPdf'])->name('purchases.pdf')->middleware('permission:purchases.view');
    Route::get('/purchases/{purchase}/whatsapp',  [PurchaseController::class, 'whatsapp'])->name('purchases.whatsapp')->middleware('permission:purchases.view');
    Route::delete('/purchases/{purchase}',        [PurchaseController::class, 'destroy'])->name('purchases.destroy')->middleware('permission:purchases.delete');
    Route::patch('/purchases/{purchase}/status',  [PurchaseController::class, 'updateStatus'])->name('purchases.status')->middleware('permission:purchases.edit');
    Route::get('/purchases/{purchase}/return',    [PurchaseReturnController::class, 'create'])->name('purchase-returns.create')->middleware('permission:purchases.create');
    Route::post('/purchases/{purchase}/return',   [PurchaseReturnController::class, 'store'])->name('purchase-returns.store')->middleware('permission:purchases.create');

    // مرتجعات المشتريات - قائمة
    Route::get('/purchase-returns', [PurchaseReturnController::class, 'index'])->name('purchase-returns.index')->middleware('permission:purchases.view');

    // ===== المصروفات =====
    Route::get('/expenses',                         [ExpenseController::class, 'index'])->name('expenses.index')->middleware('permission:expenses.view');
    Route::get('/expenses/create',                  [ExpenseController::class, 'create'])->name('expenses.create')->middleware('permission:expenses.create');
    Route::post('/expenses',                        [ExpenseController::class, 'store'])->name('expenses.store')->middleware('permission:expenses.create');
    Route::post('/expense-categories',              [ExpenseController::class, 'storeCategory'])->name('expense-categories.store')->middleware('permission:expenses.create');
    Route::get('/expenses/{expense}/edit',          [ExpenseController::class, 'edit'])->name('expenses.edit')->middleware('permission:expenses.edit');
    Route::put('/expenses/{expense}',               [ExpenseController::class, 'update'])->name('expenses.update')->middleware('permission:expenses.edit');
    Route::delete('/expenses/{expense}',            [ExpenseController::class, 'destroy'])->name('expenses.destroy')->middleware('permission:expenses.delete');
    Route::delete('/expense-categories/{category}', [ExpenseController::class, 'destroyCategory'])->name('expense-categories.destroy')->middleware('permission:expenses.delete');

    // ===== الجرد =====
    Route::get('/stocktaking',                             [StocktakingController::class, 'index'])->name('stocktaking.index')->middleware('permission:stocktaking.view');
    Route::get('/stocktaking/create',                      [StocktakingController::class, 'create'])->name('stocktaking.create')->middleware('permission:stocktaking.create');
    Route::post('/stocktaking',                            [StocktakingController::class, 'store'])->name('stocktaking.store')->middleware('permission:stocktaking.create');
    Route::get('/stocktaking/{stocktaking}',               [StocktakingController::class, 'show'])->name('stocktaking.show')->middleware('permission:stocktaking.view');
    Route::post('/stocktaking/{stocktaking}/update-items', [StocktakingController::class, 'update'])->name('stocktaking.update')->middleware('permission:stocktaking.create');
    Route::post('/stocktaking/{stocktaking}/confirm',      [StocktakingController::class, 'confirm'])->name('stocktaking.confirm')->middleware('permission:stocktaking.confirm');

    // ===== التقارير =====
    Route::get('/reports/sales',     [ReportController::class, 'sales'])->name('reports.sales')->middleware('permission:reports.view_sales');
    Route::get('/reports/purchases', [ReportController::class, 'purchases'])->name('reports.purchases')->middleware('permission:reports.view_sales');
    Route::get('/reports/customers', [ReportController::class, 'customers'])->name('reports.customers')->middleware('permission:reports.view_sales');
    Route::get('/reports/stock',     [ReportController::class, 'stock'])->name('reports.stock')->middleware('permission:reports.view_stock');
    Route::get('/reports/profit',    [ReportController::class, 'profit'])->name('reports.profit')->middleware('permission:reports.view_profit');

    // ===== الإعدادات =====
    Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index')->middleware('permission:settings.view');
    Route::put('/settings', [SettingsController::class, 'update'])->name('settings.update')->middleware('permission:settings.edit');

    // القوالب
    Route::resource('templates', TemplateController::class)->middleware('permission:settings.edit');

    // طرق الدفع
    Route::post('/payment-methods',                        [PaymentMethodController::class, 'store'])->name('payment-methods.store')->middleware('permission:settings.edit');
    Route::put('/payment-methods/{paymentMethod}',         [PaymentMethodController::class, 'update'])->name('payment-methods.update')->middleware('permission:settings.edit');
    Route::post('/payment-methods/{paymentMethod}/toggle', [PaymentMethodController::class, 'toggle'])->name('payment-methods.toggle')->middleware('permission:settings.edit');
    Route::delete('/payment-methods/{paymentMethod}',      [PaymentMethodController::class, 'destroy'])->name('payment-methods.destroy')->middleware('permission:settings.edit');

    // الفئات والوحدات
    Route::get('/categories',               [App\Http\Controllers\CategoryController::class, 'index'])->name('categories.index')->middleware('permission:settings.edit');
    Route::post('/categories',              [App\Http\Controllers\CategoryController::class, 'storeCategory'])->name('categories.store')->middleware('permission:settings.edit');
    Route::put('/categories/{category}',    [App\Http\Controllers\CategoryController::class, 'updateCategory'])->name('categories.update')->middleware('permission:settings.edit');
    Route::delete('/categories/{category}', [App\Http\Controllers\CategoryController::class, 'destroyCategory'])->name('categories.destroy')->middleware('permission:settings.edit');
    Route::post('/units',                   [App\Http\Controllers\CategoryController::class, 'storeUnit'])->name('units.store')->middleware('permission:settings.edit');
    Route::put('/units/{unit}',             [App\Http\Controllers\CategoryController::class, 'updateUnit'])->name('units.update')->middleware('permission:settings.edit');
    Route::delete('/units/{unit}',          [App\Http\Controllers\CategoryController::class, 'destroyUnit'])->name('units.destroy')->middleware('permission:settings.edit');

    // ===== المستخدمون =====
    Route::get('/users',                             [UserController::class, 'index'])->name('users.index')->middleware('permission:users.view');
    Route::get('/users/create',                      [UserController::class, 'create'])->name('users.create')->middleware('permission:users.create');
    Route::post('/users',                            [UserController::class, 'store'])->name('users.store')->middleware('permission:users.create');
    Route::get('/users/{user}/edit',                 [UserController::class, 'edit'])->name('users.edit')->middleware('permission:users.edit');
    Route::put('/users/{user}',                      [UserController::class, 'update'])->name('users.update')->middleware('permission:users.edit');
    Route::post('/users/{user}/toggle',              [UserController::class, 'toggleActive'])->name('users.toggle')->middleware('permission:users.edit');
    Route::post('/users/{user}/reset-password',      [UserController::class, 'resetPassword'])->name('users.reset-password')->middleware('permission:users.edit');
    Route::delete('/users/{user}',                   [UserController::class, 'destroy'])->name('users.destroy')->middleware('permission:users.delete');

    // ===== العملات =====
    Route::get('/currencies',                  [CurrencyController::class, 'index'])->name('currencies.index')->middleware('permission:currencies.view');
    Route::get('/currencies/rate',             [CurrencyController::class, 'getRate'])->name('currencies.get-rate')->middleware('permission:currencies.view');
    Route::post('/currencies',                 [CurrencyController::class, 'store'])->name('currencies.store')->middleware('permission:currencies.manage');
    Route::post('/currencies/{currency}/base', [CurrencyController::class, 'setBase'])->name('currencies.base')->middleware('permission:currencies.manage');
    Route::post('/currencies/{currency}/rate', [CurrencyController::class, 'addRate'])->name('currencies.rate')->middleware('permission:currencies.manage');
    Route::delete('/currencies/{currency}',    [CurrencyController::class, 'destroy'])->name('currencies.destroy')->middleware('permission:currencies.manage');

    // ===== المخازن =====
    Route::get('/warehouses',                      [WarehouseController::class, 'index'])->name('warehouses.index')->middleware('permission:warehouses.view');
    Route::post('/warehouses/transfer',            [WarehouseController::class, 'transfer'])->name('warehouses.transfer')->middleware('permission:warehouses.manage');
    Route::post('/warehouses',                     [WarehouseController::class, 'store'])->name('warehouses.store')->middleware('permission:warehouses.manage');
    Route::get('/warehouses/{warehouse}/stock',    [WarehouseController::class, 'show'])->name('warehouses.show')->middleware('permission:warehouses.view');
    Route::put('/warehouses/{warehouse}',          [WarehouseController::class, 'update'])->name('warehouses.update')->middleware('permission:warehouses.manage');
    Route::post('/warehouses/{warehouse}/default', [WarehouseController::class, 'setDefault'])->name('warehouses.default')->middleware('permission:warehouses.manage');
    Route::post('/warehouses/{warehouse}/toggle',  [WarehouseController::class, 'toggle'])->name('warehouses.toggle')->middleware('permission:warehouses.manage');
    Route::delete('/warehouses/{warehouse}',       [WarehouseController::class, 'destroy'])->name('warehouses.destroy')->middleware('permission:warehouses.manage');

    // ===== الشركة =====
    Route::get('/companies',                 [CompanyController::class, 'index'])->name('companies.index')->middleware('permission:settings.view');
    Route::get('/companies/create',          [CompanyController::class, 'create'])->name('companies.create')->middleware('permission:settings.edit');
    Route::post('/companies',                [CompanyController::class, 'store'])->name('companies.store')->middleware('permission:settings.edit');
    Route::get('/companies/{company}/edit',  [CompanyController::class, 'edit'])->name('companies.edit')->middleware('permission:settings.edit');
    Route::put('/companies/{company}',       [CompanyController::class, 'update'])->name('companies.update')->middleware('permission:settings.edit');
    Route::delete('/companies/{company}',    [CompanyController::class, 'destroy'])->name('companies.destroy')->middleware('permission:settings.edit');

    // ===== نقطة البيع (POS) =====
    Route::get('/pos',                       [\App\Http\Controllers\PosController::class, 'index'])->name('pos.index')->middleware('permission:pos.access');
    Route::post('/pos/checkout',             [\App\Http\Controllers\PosController::class, 'checkout'])->name('pos.checkout')->middleware('permission:pos.access');
    Route::get('/pos/receipt/{invoice}',     [\App\Http\Controllers\PosController::class, 'receipt'])->name('pos.receipt')->middleware('permission:pos.access');

    // ===== النسخ الاحتياطي (Tenant) =====
    Route::get('/backups',               [BackupController::class, 'index'])->name('backups.index');
    Route::post('/backups',              [BackupController::class, 'store'])->name('backups.store');
    Route::get('/backups/{id}/download', [BackupController::class, 'download'])->name('backups.download');
    Route::delete('/backups/{id}',       [BackupController::class, 'destroy'])->name('backups.destroy');
});
