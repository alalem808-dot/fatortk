<?php
/**
 * نسخة index.php لـ Hostinger
 * ضع هذا الملف في public_html/ باسم index.php
 * بعد وضع باقي المشروع في ~/fatortk_app/
 */

use Illuminate\Contracts\Http\Kernel;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

$appPath = __DIR__ . '/../fatortk_app';

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
