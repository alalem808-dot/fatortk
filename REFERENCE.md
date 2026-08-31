# مرجع سريع — فاتورتك

---

## الروتات الرئيسية

| الصفحة | المسار |
|--------|--------|
| الصفحة الرئيسية | `/` |
| تسجيل الدخول | `/login` |
| إنشاء حساب | `/register` |
| لوحة التحكم | `/dashboard` |
| الفواتير | `/invoices` |
| إنشاء فاتورة | `/invoices/create` |
| عرض فاتورة | `/invoices/{id}` |
| PDF فاتورة | `/invoices/{id}/pdf` |
| رابط عام للفاتورة | `/invoice/{uuid}/view` |
| المدفوعات | `/payments` |
| مرتجعات المبيعات | `/invoices/{id}/return` |
| العملاء | `/customers` |
| المشتريات | `/purchases` |
| مرتجعات المشتريات | `/purchases/{id}/return` |
| الموردون | `/suppliers` |
| المنتجات | `/products` |
| المخازن | `/warehouses` |
| الجرد الدوري | `/stocktaking` |
| نقطة البيع | `/pos` |
| البيع السريع | `/quick-sale` |
| المصروفات | `/expenses` |
| التقارير | `/reports` |
| المستخدمون | `/users` |
| الإعدادات | `/settings` |
| قوالب الفواتير | `/templates` |
| الفئات | `/categories` |
| Super Admin | `/super-admin` |

---

## نماذج البيانات الرئيسية

### Invoice (الفاتورة)
```
id, tenant_id, invoice_number, customer_id, template_id, warehouse_id
invoice_date, due_date, status, language, currency, exchange_rate
subtotal, discount_amount, discount_type, tax_amount, total_amount, paid_amount
notes, terms_conditions, public_token, created_by
```

**قيم status:** `draft | sent | partially_paid | paid | overdue | cancelled | returned`

---

### Purchase (أمر الشراء)
```
id, tenant_id, reference, supplier_id, warehouse_id
purchase_date, status, subtotal, discount_amount, tax_amount, total_amount, paid_amount
```

**قيم status:** `pending | received | cancelled`

---

### Product (المنتج)
```
id, tenant_id, name, sku, barcode, category_id, unit
unit_price, cost_price, tax_rate, stock_quantity, min_stock_alert, status
```

---

### WarehouseStock (مخزون المخزن)
```
id, product_id, warehouse_id, quantity
```

---

### SubscriptionPlan (خطط الاشتراك)
```
id, slug, name
price_monthly, price_yearly, price_monthly_usd, price_yearly_usd
max_invoices_per_month, max_customers, max_products, max_users, max_templates
excel_export, email_send, stock_management, custom_templates, api_access
is_active
```
**القيمة -1 = غير محدود**

---

### PlatformSetting (إعدادات المنصة)
```
key, value, label
```

**المفاتيح المستخدمة:**
| المفتاح | الوصف |
|---------|-------|
| `platform_name` | اسم المنصة |
| `platform_logo` | مسار الشعار |
| `platform_favicon` | مسار الفافيكون |
| `whatsapp_number` | رقم الواتساب |
| `whatsapp_subscribe_msg` | رسالة الاشتراك |
| `support_email` | بريد الدعم |

---

## الصلاحيات المتاحة

```
invoices.view | invoices.create | invoices.edit | invoices.delete | invoices.send | invoices.export
customers.view | customers.create | customers.edit | customers.delete
products.view | products.create | products.edit | products.delete
purchases.view | purchases.create | purchases.edit | purchases.delete
suppliers.view | suppliers.create | suppliers.edit | suppliers.delete
expenses.view | expenses.create | expenses.edit | expenses.delete
returns.view | returns.create
stock.view | stock.create | stock.confirm | warehouses.view | warehouses.manage
reports.view_sales | reports.view_stock | reports.view_profit
users.view | users.create | users.edit | users.delete
settings.view | settings.edit
```

---

## الخدمات (Services)

| الخدمة | الملف | الوظيفة |
|--------|-------|---------|
| StockService | `app/Services/StockService.php` | خصم وإضافة المخزون |

---

## أوامر Artisan المفيدة

```bash
php artisan migrate:fresh --seed    # إعادة بناء DB + بيانات تجريبية
php artisan config:cache            # كاش الإعدادات
php artisan config:clear            # مسح كاش الإعدادات
php artisan route:cache             # كاش الروتات
php artisan view:cache              # كاش الـ Views
php artisan cache:clear             # مسح كل الكاش
php artisan storage:link            # ربط storage بـ public
php artisan queue:work              # تشغيل Queue Worker
```
