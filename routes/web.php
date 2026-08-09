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
use App\Http\Controllers\SuperAdmin\AuthController as SuperAuthController;
use App\Http\Controllers\SuperAdmin\DashboardController as SuperDashController;
use App\Http\Controllers\SuperAdmin\PlansController;

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
        Route::post('/logout',             [SuperAuthController::class, 'logout'])->name('logout');
        Route::get('/dashboard',           [SuperDashController::class, 'index'])->name('dashboard');
        Route::get('/tenants',             [SuperDashController::class, 'tenants'])->name('tenants');
        Route::get('/tenants/create',      [SuperDashController::class, 'createTenant'])->name('tenants.create');
        Route::post('/tenants',            [SuperDashController::class, 'storeTenant'])->name('tenants.store');
        Route::get('/tenants/{tenant}',    [SuperDashController::class, 'showTenant'])->name('tenants.show');
        Route::put('/tenants/{tenant}',    [SuperDashController::class, 'updateTenant'])->name('tenants.update');
        Route::delete('/tenants/{tenant}', [SuperDashController::class, 'deleteTenant'])->name('tenants.delete');
        Route::get('/plans',               [PlansController::class, 'index'])->name('plans');
        Route::get('/plans/{plan}/edit',   [PlansController::class, 'edit'])->name('plans.edit');
        Route::put('/plans/{plan}',        [PlansController::class, 'update'])->name('plans.update');
        Route::get('/settings',            [SuperDashController::class, 'settings'])->name('settings');
        Route::put('/settings',            [SuperDashController::class, 'updateSettings'])->name('settings.update');
    });
});

// ===== Auth =====
Route::middleware('guest')->group(function () {
    Route::get('/login',  [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});

Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// رابط عام لتحميل الفاتورة
Route::get('/invoice/{token}/view', [InvoiceController::class, 'publicView'])->name('invoices.public');

// ===== المسارات المحمية =====
Route::middleware(['auth', 'tenant'])->group(function () {

    Route::get('/app', fn() => redirect()->route('dashboard'));
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // الفواتير
    Route::resource('invoices', InvoiceController::class);
    Route::patch('/invoices/{invoice}/status',    [InvoiceController::class, 'updateStatus'])->name('invoices.status');
    Route::get('/invoices/{invoice}/pdf',         [InvoiceController::class, 'downloadPdf'])->name('invoices.pdf');
    Route::get('/invoices/{invoice}/whatsapp',    [InvoiceController::class, 'whatsapp'])->name('invoices.whatsapp');
    Route::post('/invoices/{invoice}/send-email', [InvoiceController::class, 'sendEmail'])->name('invoices.email');

    // المنتجات
    Route::resource('products', ProductController::class);
    Route::post('/products/{product}/stock', [ProductController::class, 'adjustStock'])->name('products.stock');

    // العملاء
    Route::resource('customers', CustomerController::class);

    // القوالب
    Route::resource('templates', TemplateController::class);

    // التقارير
    Route::get('/reports/sales',     [ReportController::class, 'sales'])->name('reports.sales');
    Route::get('/reports/stock',     [ReportController::class, 'stock'])->name('reports.stock');
    Route::get('/reports/customers', [ReportController::class, 'customers'])->name('reports.customers');

    // المدفوعات
    Route::post('/payments', [PaymentController::class, 'store'])->name('payments.store');

    // الإعدادات
    Route::get('/settings',  [SettingsController::class, 'index'])->name('settings.index');
    Route::put('/settings',  [SettingsController::class, 'update'])->name('settings.update');

    // الفئات ووحدات القياس
    Route::get('/categories',                    [App\Http\Controllers\CategoryController::class, 'index'])->name('categories.index');
    Route::post('/categories',                   [App\Http\Controllers\CategoryController::class, 'storeCategory'])->name('categories.store');
    Route::put('/categories/{category}',         [App\Http\Controllers\CategoryController::class, 'updateCategory'])->name('categories.update');
    Route::delete('/categories/{category}',      [App\Http\Controllers\CategoryController::class, 'destroyCategory'])->name('categories.destroy');
    Route::post('/units',                        [App\Http\Controllers\CategoryController::class, 'storeUnit'])->name('units.store');
    Route::put('/units/{unit}',                  [App\Http\Controllers\CategoryController::class, 'updateUnit'])->name('units.update');
    Route::delete('/units/{unit}',               [App\Http\Controllers\CategoryController::class, 'destroyUnit'])->name('units.destroy');
});
