@echo off
REM ==========================================================
REM Setup + jalankan Laravel API untuk Windows
REM Cek PHP, Composer, install dependency, .env, migrate, run server
REM ==========================================================

cd /d "%~dp0"

echo ==^> Backend Project Management - Setup ^& Run
echo.

REM 1. Cek PHP
where php >nul 2>&1
if errorlevel 1 (
    echo [ERROR] PHP tidak ditemukan.
    echo Install dulu: download dari https://windows.php.net/ atau pakai XAMPP/Laragon
    pause
    exit /b 1
)

for /f "tokens=2 delims= " %%v in ('php -v ^| findstr /B "PHP"') do set PHPVER=%%v
echo [OK] PHP %PHPVER%

REM 2. Cek extensions
set MISSING=
for %%E in (mbstring bcmath curl pdo_mysql xml tokenizer openssl fileinfo) do (
    php -m | findstr /I /R "^%%E$" >nul
    if errorlevel 1 set MISSING=!MISSING! %%E
)

setlocal enabledelayedexpansion
if not "!MISSING!"=="" (
    echo [ERROR] PHP extensions kurang: !MISSING!
    echo Edit php.ini, uncomment extension yang dibutuhkan.
    pause
    exit /b 1
)
endlocal

echo [OK] Semua PHP extensions ada

REM 3. Cek Composer
where composer >nul 2>&1
if errorlevel 1 (
    echo [ERROR] Composer tidak ditemukan.
    echo Install dari: https://getcomposer.org/Composer-Setup.exe
    pause
    exit /b 1
)

echo [OK] Composer terinstall
echo.

REM 4. Install dependency kalau belum
if not exist "vendor" (
    echo ==^> vendor/ belum ada, menjalankan composer install...
    call composer install --no-interaction
    if errorlevel 1 (
        echo [ERROR] composer install gagal.
        pause
        exit /b 1
    )
    echo.
)

REM 5. Setup .env kalau belum
if not exist ".env" (
    echo ==^> Copy .env.example -^> .env
    copy ".env.example" ".env" >nul
    echo [INFO] Edit .env, sesuaikan DB_USERNAME ^& DB_PASSWORD dengan MySQL kamu.
    echo        Lalu jalankan run.bat lagi.
    pause
    exit /b 0
)

REM 6. Generate APP_KEY kalau kosong
findstr /B "APP_KEY=base64:" .env >nul
if errorlevel 1 (
    echo ==^> Generate APP_KEY...
    call php artisan key:generate
    echo.
)

REM 7. Migrate
echo ==^> Migrate database...
call php artisan migrate --force
if errorlevel 1 (
    echo.
    echo [ERROR] Migrate gagal. Cek error di atas.
    echo Tips:
    echo   - Pastikan database 'project_management' sudah dibuat di MySQL
    echo   - Verify DB_USERNAME ^& DB_PASSWORD di .env
    pause
    exit /b 1
)

REM 8. Seed kalau database kosong
for /f %%c in ('php artisan tinker --execute^="echo App\Models\User::count();" 2^>nul') do set USER_COUNT=%%c
if "%USER_COUNT%"=="0" (
    echo.
    echo ==^> Database kosong, menjalankan seeder...
    call php artisan db:seed --force
    echo.
)

REM 9. Storage link
if not exist "public\storage" (
    call php artisan storage:link
)

REM 10. Run server
echo.
echo ==^> API jalan di http://localhost:8000
echo ==^> Akun demo: admin@test.com / admin123
echo.
call php artisan serve --host=0.0.0.0 --port=8000
