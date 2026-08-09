# فاتورتك - نظام إدارة الفواتير والمخزون (SaaS)
## ملف مرجعي شامل

---

## 1. معلومات المشروع

| البند | التفاصيل |
|---|---|
| اسم المشروع | فاتورتك (FatorTK) |
| النوع | SaaS - Multi-tenant |
| اللغة | PHP 8.2+ |
| Framework | Laravel 10.x |
| قاعدة البيانات | MySQL 8.0+ |
| الواجهة | Blade + Bootstrap 5 RTL |

---

## 2. التقنيات المستخدمة

### Backend
- **Laravel 10.x** - Framework الرئيسي
- **Eloquent ORM** - التعامل مع قاعدة البيانات
- **Laravel Sanctum** - المصادقة والحماية
- **spatie/laravel-multitenancy** - نظام Multi-tenancy
- **spatie/laravel-permission** - إدارة الأدوار والصلاحيات
- **barryvdh/laravel-dompdf** - توليد PDF
- **maatwebsite/laravel-excel** - تصدير Excel
- **laravel/socialite** - تسجيل الدخول الاجتماعي

### Frontend
- **Bootstrap 5 RTL** - التصميم
- **Alpine.js** - التفاعلية
- **Chart.js** - الرسوم البيانية
- **Font Awesome 6** - الأيقونات
- **TinyMCE** - محرر القوالب

### الخدمات الخارجية
- **wa.me** - إرسال WhatsApp (رابط تحميل)
- **SMTP/Mailgun** - إرسال البريد الإلكتروني

---

## 3. هيكل قاعدة البيانات

### tenants - الشركات/المستأجرون
```
id, company_name, subdomain, email, phone, address, logo,
status(active/suspended/trial), subscription_plan(free/basic/pro),
subscription_expires_at, created_at, updated_at
```

### users - المستخدمون
```
id, tenant_id(FK), name, email, password, role(admin/manager/employee),
is_active, last_login, created_at, updated_at
```

### customers - العملاء
```
id, tenant_id(FK), name, email, phone, whatsapp_number,
address, city, country, tax_number, notes, created_at, updated_at
```

### categories - الفئات
```
id, tenant_id(FK), name, parent_id(FK nullable), created_at, updated_at
```

### products - المنتجات
```
id, tenant_id(FK), category_id(FK), name, description, sku,
barcode, unit_price, cost_price, tax_rate, stock_quantity,
min_stock_alert, unit(piece/kg/liter), image, status(active/inactive),
created_at, updated_at
```

### invoices - الفواتير
```
id, tenant_id(FK), invoice_number, customer_id(FK), invoice_date,
due_date, status(draft/sent/paid/overdue/cancelled),
subtotal, tax_amount, discount_amount, discount_type(fixed/percent),
total_amount, paid_amount, currency(SAR/USD/EUR),
notes, terms_conditions, template_id(FK), created_by(FK),
created_at, updated_at
```

### invoice_items - تفاصيل الفواتير
```
id, invoice_id(FK), product_id(FK nullable), description,
quantity, unit_price, tax_rate, discount, total, created_at, updated_at
```

### payments - المدفوعات
```
id, tenant_id(FK), invoice_id(FK), payment_date, amount,
payment_method(cash/bank/card/cheque), reference_number,
notes, created_at, updated_at
```

### stock_movements - حركات المخزون
```
id, tenant_id(FK), product_id(FK), type(in/out/adjustment),
quantity, quantity_before, quantity_after,
reference_type(invoice/purchase/manual), reference_id,
notes, created_by(FK), created_at
```

### invoice_templates - قوالب الفواتير
```
id, tenant_id(FK), name, is_default,
header_html, footer_html, css_styles,
primary_color, secondary_color, font_family,
show_logo, show_tax, show_discount, show_notes,
created_at, updated_at
```

### settings - الإعدادات
```
id, tenant_id(FK), key, value, created_at, updated_at
```

### sent_logs - سجل الإرسال
```
id, tenant_id(FK), invoice_id(FK), channel(email/whatsapp),
recipient, status(success/failed), error_message, sent_at
```

---

## 4. هيكل المجلدات

```
fatortk/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Auth/
│   │   │   │   ├── LoginController.php
│   │   │   │   └── RegisterController.php
│   │   │   ├── DashboardController.php
│   │   │   ├── InvoiceController.php
│   │   │   ├── ProductController.php
│   │   │   ├── CustomerController.php
│   │   │   ├── PaymentController.php
│   │   │   ├── ReportController.php
│   │   │   ├── TemplateController.php
│   │   │   └── SettingsController.php
│   │   ├── Middleware/
│   │   │   ├── TenantMiddleware.php
│   │   │   └── CheckSubscription.php
│   │   └── Requests/
│   │       ├── InvoiceRequest.php
│   │       └── ProductRequest.php
│   ├── Models/
│   │   ├── Tenant.php
│   │   ├── User.php
│   │   ├── Customer.php
│   │   ├── Product.php
│   │   ├── Category.php
│   │   ├── Invoice.php
│   │   ├── InvoiceItem.php
│   │   ├── Payment.php
│   │   ├── StockMovement.php
│   │   ├── InvoiceTemplate.php
│   │   ├── Setting.php
│   │   └── SentLog.php
│   └── Services/
│       ├── InvoiceService.php
│       ├── PDFService.php
│       ├── ExcelService.php
│       ├── EmailService.php
│       ├── WhatsAppService.php
│       └── StockService.php
├── database/
│   └── migrations/
├── resources/
│   └── views/
│       ├── layouts/
│       │   └── app.blade.php
│       ├── auth/
│       ├── dashboard/
│       ├── invoices/
│       ├── products/
│       ├── customers/
│       ├── reports/
│       └── settings/
├── routes/
│   └── web.php
└── REFERENCE.md
```

---

## 5. المسارات (Routes)

```
POST   /register              - تسجيل حساب جديد
POST   /login                 - تسجيل الدخول
POST   /logout                - تسجيل الخروج

GET    /dashboard             - لوحة التحكم

GET    /invoices              - قائمة الفواتير
GET    /invoices/create       - إنشاء فاتورة
POST   /invoices              - حفظ فاتورة
GET    /invoices/{id}         - عرض فاتورة
GET    /invoices/{id}/edit    - تعديل فاتورة
GET    /invoices/{id}/pdf     - تحميل PDF
GET    /invoices/{id}/whatsapp - رابط WhatsApp
POST   /invoices/{id}/send-email - إرسال بريد

GET    /products              - قائمة المنتجات
POST   /products              - إضافة منتج
PUT    /products/{id}         - تعديل منتج
DELETE /products/{id}         - حذف منتج

GET    /customers             - قائمة العملاء
POST   /customers             - إضافة عميل
PUT    /customers/{id}        - تعديل عميل

GET    /reports/sales         - تقرير المبيعات
GET    /reports/stock         - تقرير المخزون
GET    /reports/customers     - تقرير العملاء

GET    /settings              - الإعدادات
GET    /templates             - قوالب الفواتير
```

---

## 6. الأدوار والصلاحيات

| الصلاحية | Admin | Manager | Employee |
|---|---|---|---|
| إدارة المستخدمين | ✅ | ❌ | ❌ |
| إنشاء فواتير | ✅ | ✅ | ✅ |
| حذف فواتير | ✅ | ✅ | ❌ |
| إدارة المنتجات | ✅ | ✅ | ❌ |
| عرض التقارير | ✅ | ✅ | ❌ |
| الإعدادات | ✅ | ❌ | ❌ |

---

## 7. خطوات التثبيت

```bash
# 1. تثبيت المشروع
composer install

# 2. نسخ ملف البيئة
cp .env.example .env

# 3. توليد مفتاح التطبيق
php artisan key:generate

# 4. إعداد قاعدة البيانات في .env
DB_DATABASE=fatortk
DB_USERNAME=root
DB_PASSWORD=

# 5. تشغيل الـ Migrations
php artisan migrate --seed

# 6. رفع الـ Storage
php artisan storage:link
```

---

## 8. إعدادات البيئة (.env)

```env
APP_NAME=فاتورتك
APP_URL=http://localhost/fatortk/public

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=fatortk
DB_USERNAME=root
DB_PASSWORD=

MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your@email.com
MAIL_PASSWORD=your_password
MAIL_FROM_ADDRESS=your@email.com
MAIL_FROM_NAME=فاتورتك
```

---

## 9. الأمان

- ✅ CSRF Protection - مدمج في Laravel
- ✅ SQL Injection Protection - Eloquent ORM
- ✅ XSS Protection - Blade templating
- ✅ Password Hashing - bcrypt
- ✅ Rate Limiting - على تسجيل الدخول
- ✅ Tenant Isolation - كل بيانات معزولة بـ tenant_id
- ✅ Signed URLs - لروابط تحميل الفواتير العامة
- ✅ Input Validation - Form Requests

---

## 10. خارطة التطوير

- [x] هيكل المشروع
- [x] قاعدة البيانات
- [x] نظام المصادقة
- [x] Multi-tenancy
- [x] إدارة العملاء
- [x] إدارة المنتجات والمخزون
- [x] نظام الفواتير
- [x] تصدير PDF
- [x] تصدير Excel
- [x] إرسال Email
- [x] إرسال WhatsApp (wa.me)
- [x] قوالب الفواتير
- [x] التقارير
- [x] لوحة التحكم
