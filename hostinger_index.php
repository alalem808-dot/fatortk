<?php

use Illuminate\Contracts\Http\Kernel;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

/*
|--------------------------------------------------------------------------
| تعديل المسارات لـ Hostinger
| ضع هذا الملف في public_html مباشرة
| وضع باقي ملفات المشروع في مجلد fatortk خارج public_html
|--------------------------------------------------------------------------
*/

if (file_exists($maintenance = __DIR__.'/../fatortk/storage/framework/maintenance.php')) {
    require $maintenance;
}

require __DIR__.'/../fatortk/vendor/autoload.php';

$app = require_once __DIR__.'/../fatortk/bootstrap/app.php';

$kernel = $app->make(Kernel::class);

$response = $kernel->handle(
    $request = Request::capture()
)->send();

$kernel->terminate($request, $response);
