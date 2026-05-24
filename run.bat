@echo off
REM ==========================================================
REM Setup + jalankan Laravel API untuk Windows
REM Cek PHP, Composer, install dependency, .env, auto-setup
REM database PostgreSQL, migrate / import dump, run server.
REM ==========================================================

setlocal enabledelayedexpansion
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
for %%E in (mbstring bcmath curl pdo_pgsql xml tokenizer openssl fileinfo) do (
    php -m | findstr /I /R "^%%E$" >nul
    if errorlevel 1 set MISSING=!MISSING! %%E
)

if not "!MISSING!"=="" (
    echo.
    echo [ERROR] PHP extensions kurang:!MISSING!
    echo.
    echo --- Diagnostic: lokasi konfigurasi PHP ---
    php --ini
    echo -------------------------------------------
    echo.

    REM Deteksi apakah php.ini ke-load
    set INI_LOADED=
    for /f "tokens=*" %%i in ('php -r "echo php_ini_loaded_file() ?: '''';"') do set INI_LOADED=%%i

    if "!INI_LOADED!"=="" (
        echo [HINT] PHP belum punya php.ini ^(Loaded Configuration File = none^).
        echo.
        echo   Cara fix:
        echo   1. Cari folder PHP-mu ^(lihat path di output 'where php' di bawah^).
        echo   2. Di folder itu, copy 'php.ini-development' jadi 'php.ini':
        echo        copy php.ini-development php.ini
        echo   3. Buka php.ini, hapus tanda ; di depan baris-baris berikut:
        echo        extension_dir = "ext"
        echo        extension=mbstring
        echo        extension=bcmath
        echo        extension=curl
        echo        extension=pdo_pgsql
        echo        extension=pgsql
        echo        extension=fileinfo
        echo        extension=openssl
        echo        extension=tokenizer
        echo        extension=xml
        echo   4. Save, buka cmd baru, jalankan run.bat lagi.
        echo.
        echo   Lokasi PHP-mu:
        where php
    ) else (
        echo [HINT] php.ini sudah ke-load di: !INI_LOADED!
        echo.
        echo   Buka file di atas dengan Notepad / editor lain, lalu hapus tanda
        echo   ; di depan baris extension yang missing^^:
        for %%E in (!MISSING!) do echo        extension=%%E
        echo.
        echo   Catatan tambahan:
        echo   - Untuk Laragon: edit lewat menu Laragon ^> PHP ^> php.ini, lalu Reload.
        echo   - Untuk XAMPP: edit C:\xampp\php\php.ini, restart XAMPP.
        echo   - Pastikan ada baris: extension_dir = "ext"  ^(tanpa ; di depan^)
        echo   - Setelah save php.ini, BUKA CMD BARU sebelum run.bat lagi.
    )
    echo.
    pause
    exit /b 1
)

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
    echo.
    echo ==^> Konfigurasi PostgreSQL credentials ^(tekan Enter untuk pakai default^)
    set /p DB_USERNAME_INPUT="    DB_USERNAME [postgres]: "
    set /p DB_PASSWORD_INPUT="    DB_PASSWORD [root]:     "
    if "!DB_USERNAME_INPUT!"=="" set DB_USERNAME_INPUT=postgres
    if "!DB_PASSWORD_INPUT!"=="" set DB_PASSWORD_INPUT=root

    set "NEW_USER=!DB_USERNAME_INPUT!"
    set "NEW_PASS=!DB_PASSWORD_INPUT!"
    php -r "$c=file_get_contents('.env'); $c=preg_replace('/^DB_USERNAME=.*/m','DB_USERNAME='.getenv('NEW_USER'),$c); $c=preg_replace('/^DB_PASSWORD=.*/m','DB_PASSWORD='.getenv('NEW_PASS'),$c); file_put_contents('.env',$c);"
    set "NEW_USER="
    set "NEW_PASS="
    echo [OK] .env dibuat dengan user '!DB_USERNAME_INPUT!'
    echo.
)

REM 6. Generate APP_KEY kalau kosong
findstr /B "APP_KEY=base64:" .env >nul
if errorlevel 1 (
    echo ==^> Generate APP_KEY...
    call php artisan key:generate
    echo.
)

REM 7. Parse .env untuk DB credentials
set DB_HOST=127.0.0.1
set DB_PORT=5432
set DB_DATABASE=project_management
set DB_USERNAME=postgres
set DB_PASSWORD=

for /f "tokens=1,* delims==" %%a in ('findstr /B "DB_HOST="     .env') do set DB_HOST=%%b
for /f "tokens=1,* delims==" %%a in ('findstr /B "DB_PORT="     .env') do set DB_PORT=%%b
for /f "tokens=1,* delims==" %%a in ('findstr /B "DB_DATABASE=" .env') do set DB_DATABASE=%%b
for /f "tokens=1,* delims==" %%a in ('findstr /B "DB_USERNAME=" .env') do set DB_USERNAME=%%b
for /f "tokens=1,* delims==" %%a in ('findstr /B "DB_PASSWORD=" .env') do set DB_PASSWORD=%%b

REM 8. Auto-setup PostgreSQL database (kalau psql ada di PATH)
set IMPORTED_DUMP=0
where psql >nul 2>&1
if errorlevel 1 (
    echo [INFO] psql CLI tidak ada di PATH, skip auto-create DB.
    echo        Pastikan database '!DB_DATABASE!' sudah dibuat manual via pgAdmin.
    goto :after_dbsetup
)

echo ==^> Cek koneksi PostgreSQL ^(!DB_USERNAME!@!DB_HOST!:!DB_PORT!^)...
set PGPASSWORD=!DB_PASSWORD!
psql -h !DB_HOST! -p !DB_PORT! -U !DB_USERNAME! -d postgres -tAc "SELECT 1" >nul 2>&1
if errorlevel 1 (
    echo [ERROR] Tidak bisa konek PostgreSQL.
    echo   - Pastikan service PostgreSQL jalan
    echo   - Verify DB_USERNAME ^& DB_PASSWORD di .env
    pause
    exit /b 1
)
echo [OK] Konek PostgreSQL

for /f %%d in ('psql -h !DB_HOST! -p !DB_PORT! -U !DB_USERNAME! -d postgres -tAc "SELECT 1 FROM pg_database WHERE datname='!DB_DATABASE!'"') do set DB_EXIST=%%d
if not "!DB_EXIST!"=="1" (
    echo ==^> Database '!DB_DATABASE!' belum ada, membuat...
    psql -h !DB_HOST! -p !DB_PORT! -U !DB_USERNAME! -d postgres -c "CREATE DATABASE \"!DB_DATABASE!\";"
)

for /f %%t in ('psql -h !DB_HOST! -p !DB_PORT! -U !DB_USERNAME! -d !DB_DATABASE! -tAc "SELECT to_regclass('public.users')" 2^>nul') do set USERS_TABLE=%%t
if "!USERS_TABLE!"=="" (
    if exist "database\dump\project_management.sql" (
        echo ==^> Database fresh, import dari database\dump\project_management.sql...
        psql -h !DB_HOST! -p !DB_PORT! -U !DB_USERNAME! -d !DB_DATABASE! -v ON_ERROR_STOP=1 -q -f database\dump\project_management.sql
        if errorlevel 1 (
            echo [ERROR] Import dump gagal.
            pause
            exit /b 1
        )
        set IMPORTED_DUMP=1
        echo [OK] Dump imported ^(schema + demo data lengkap^)
    )
)

:after_dbsetup
set PGPASSWORD=

REM 9. Migrate (skip kalau baru import dump)
if "!IMPORTED_DUMP!"=="0" (
    echo ==^> Migrate database...
    call php artisan migrate --force
    if errorlevel 1 (
        echo.
        echo [ERROR] Migrate gagal. Cek error di atas.
        pause
        exit /b 1
    )

    for /f %%c in ('php artisan tinker --execute^="echo App\Models\User::count();" 2^>nul') do set USER_COUNT=%%c
    if "!USER_COUNT!"=="0" (
        echo.
        echo ==^> Database kosong, menjalankan seeder...
        call php artisan db:seed --force
        echo.
    )
)

REM 10. Storage link
if not exist "public\storage" (
    call php artisan storage:link
)

REM 11. Run server
echo.
echo ==^> API jalan di http://localhost:8000
echo ==^> Akun demo: admin@test.com / admin123
echo.
call php artisan serve --host=0.0.0.0 --port=8000
