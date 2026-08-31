# دليل النشر — فاتورتك على Hostinger

*الموقع المستهدف: `https://unitedcaresd.com/fatortk`*

---

## هيكل المجلدات على Hostinger

```
/home/user/
├── public_html/
│   ├── fatortk/         ← محتوى مجلد public/ فقط (index.php + .htaccess + assets)
│   └── ...
└── fatortk_app/         ← باقي المشروع (app/ config/ vendor/ ...)
    ├── app/
    ├── bootstrap/
    ├── config/
    ├── database/
    ├── resources/
    ├── routes/
    ├── storage/
    ├── vendor/
    └── ...
```

---

## الخطوة 1 — تجهيز الملفات محلياً

```powershell
# شغّل في PowerShell من مجلد المشروع
cd c:\xampp8.2\htdocs\fatortk
.\build_deploy.ps1
```

سيُنشئ ملف `c:\xampp8.2\htdocs\fatortk_deploy.zip`

---

## الخطوة 2 — رفع الملفات على Hostinger

### 2أ — رفع ملفات public/

في **hPanel → File Manager → public_html**:
1. أنشئ مجلد `fatortk`
2. ارفع هذه الملفات من `public/`:
   - `index.php` ← **استخدم النسخة المعدّلة أدناه**
   - `.htaccess`
   - `favicon.ico`
   - `robots.txt`
   - مجلد `storage/` (للـ symlink)

### 2ب — رفع باقي المشروع

1. في File Manager اذهب **خارج** public_html
2. أنشئ مجلد `fatortk_app`
3. ارفع ZIP وافكّه هناك
4. تأكد أن هيكل المجلدات صحيح:
   - `fatortk_app/app/`
   - `fatortk_app/vendor/`
   - ... إلخ

---

## الخطوة 3 — تعديل `index.php` في public_html/fatortk/

```php
<?php

use Illuminate\Contracts\Http\Kernel;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// المسار لمجلد المشروع خارج public_html
$appPath = __DIR__ . '/../../fatortk_app';

if (file_exists($maintenance = $appPath . '/storage/framework/maintenance.php')) {
    require $maintenance;
}

require $appPath . '/vendor/autoload.php';

$app = require_once $appPath . '/bootstrap/app.php';

$kernel = $app->make(Kernel::class);

$response = $kernel->handle(
    $request = Request::capture()
)->send();

$kernel->terminate($request, $response);
```

---

## الخطوة 4 — ملف `.htaccess` في `public_html/fatortk/`

```apache
Options -MultiViews -Indexes
RewriteEngine On

# HTTPS إجباري
RewriteCond %{HTTPS} off
RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]

# توجيه كل الطلبات لـ index.php
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^ index.php [L]
```

---

## الخطوة 5 — إنشاء قاعدة البيانات

في **hPanel → Databases → MySQL Databases**:
1. أنشئ قاعدة بيانات جديدة (مثلاً: `u123456_fatortk`)
2. أنشئ مستخدم بكلمة مرور قوية
3. اربط المستخدم بقاعدة البيانات (All Privileges)
4. احفظ: اسم DB، اسم المستخدم، كلمة المرور

---

## الخطوة 6 — ملف `.env` على السيرفر

في `fatortk_app/.env`:

```env
APP_NAME=فاتورتك
APP_ENV=production
APP_KEY=base64:lO65LFQrPTZOhgQ0EtQOoxa16hopdR5q8zpERzHRtcM=
APP_DEBUG=false
APP_URL=https://unitedcaresd.com/fatortk

LOG_CHANNEL=stack
LOG_LEVEL=error

DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=u123456_fatortk
DB_USERNAME=u123456_fatortk
DB_PASSWORD=your_password_here

CACHE_DRIVER=file
FILESYSTEM_DISK=public
QUEUE_CONNECTION=sync
SESSION_DRIVER=file
SESSION_LIFETIME=120

MAIL_MAILER=smtp
MAIL_HOST=smtp.hostinger.com
MAIL_PORT=465
MAIL_USERNAME=your@email.com
MAIL_PASSWORD=your_email_password
MAIL_ENCRYPTION=ssl
MAIL_FROM_ADDRESS=your@email.com
MAIL_FROM_NAME="فاتورتك"
```

---

## الخطوة 7 — أوامر عبر SSH

```bash
# اتصل بالسيرفر
ssh u123456@your-server-ip

# انتقل لمجلد المشروع
cd ~/fatortk_app

# ضع ملف .env (انسخ المحتوى أعلاه)
nano .env

# شغّل الـ migrations
php artisan migrate --force

# أنشئ رابط storage
php artisan storage:link

# حسّن الأداء
php artisan config:cache
php artisan route:cache
php artisan view:cache

# اضبط الصلاحيات
chmod -R 755 storage
chmod -R 755 bootstrap/cache
```

---

## الخطوة 8 — التحقق

افتح `https://unitedcaresd.com/fatortk` في المتصفح.

إذا ظهرت مشكلة:
```bash
# شوف الـ logs
tail -50 ~/fatortk_app/storage/logs/laravel.log
```

---

## مشاكل شائعة

| المشكلة | الحل |
|---------|------|
| صفحة بيضاء | راجع `storage/logs/laravel.log` |
| 500 Error | تحقق من صحة `.env` وصلاحيات `storage/` |
| الصور لا تظهر | شغّل `php artisan storage:link` |
| CSS لا يظهر | تأكد أن `APP_URL` صحيح في `.env` |
| Session مشكلة | تأكد من `SESSION_DRIVER=file` |
