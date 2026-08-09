@echo off
echo ================================================
echo    تجهيز ملفات فاتورتك للرفع على Hostinger
echo ================================================

set PROJECT=c:\xampp8.2\htdocs\fatortk
set OUTPUT=c:\xampp8.2\htdocs\fatortk_deploy

echo.
echo [1] إنشاء مجلدات الرفع...
if exist "%OUTPUT%" rmdir /s /q "%OUTPUT%"
mkdir "%OUTPUT%"
mkdir "%OUTPUT%\public_html_fatortk"
mkdir "%OUTPUT%\fatortk_app"

echo.
echo [2] نسخ محتوى مجلد public إلى public_html_fatortk...
xcopy "%PROJECT%\public\*" "%OUTPUT%\public_html_fatortk\" /E /H /Y

echo.
echo [3] نسخ باقي ملفات المشروع إلى fatortk_app...
xcopy "%PROJECT%\app\*"        "%OUTPUT%\fatortk_app\app\"        /E /H /Y
xcopy "%PROJECT%\bootstrap\*"  "%OUTPUT%\fatortk_app\bootstrap\"  /E /H /Y
xcopy "%PROJECT%\config\*"     "%OUTPUT%\fatortk_app\config\"     /E /H /Y
xcopy "%PROJECT%\database\*"   "%OUTPUT%\fatortk_app\database\"   /E /H /Y
xcopy "%PROJECT%\resources\*"  "%OUTPUT%\fatortk_app\resources\"  /E /H /Y
xcopy "%PROJECT%\routes\*"     "%OUTPUT%\fatortk_app\routes\"     /E /H /Y
xcopy "%PROJECT%\storage\*"    "%OUTPUT%\fatortk_app\storage\"    /E /H /Y
xcopy "%PROJECT%\vendor\*"     "%OUTPUT%\fatortk_app\vendor\"     /E /H /Y

copy "%PROJECT%\artisan"           "%OUTPUT%\fatortk_app\"
copy "%PROJECT%\composer.json"     "%OUTPUT%\fatortk_app\"
copy "%PROJECT%\composer.lock"     "%OUTPUT%\fatortk_app\"
copy "%PROJECT%\.env.production"   "%OUTPUT%\fatortk_app\.env"

echo.
echo [4] إنشاء مجلدات storage الفارغة...
mkdir "%OUTPUT%\fatortk_app\storage\app\public" 2>nul
mkdir "%OUTPUT%\fatortk_app\storage\framework\cache" 2>nul
mkdir "%OUTPUT%\fatortk_app\storage\framework\sessions" 2>nul
mkdir "%OUTPUT%\fatortk_app\storage\framework\views" 2>nul
mkdir "%OUTPUT%\fatortk_app\storage\logs" 2>nul

echo. > "%OUTPUT%\fatortk_app\storage\logs\laravel.log"

echo.
echo ================================================
echo    تم التجهيز بنجاح!
echo.
echo    ارفع محتوى هذا المجلد:
echo    %OUTPUT%\public_html_fatortk\
echo    إلى: /domains/unitedcaresd.com/public_html/fatortk/
echo.
echo    وارفع محتوى هذا المجلد:
echo    %OUTPUT%\fatortk_app\
echo    إلى: /domains/unitedcaresd.com/fatortk_app/
echo.
echo    ثم اتبع تعليمات DEPLOY_GUIDE.txt
echo ================================================
pause
