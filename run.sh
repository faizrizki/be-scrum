#!/usr/bin/env bash
#
# Setup + jalankan Laravel API untuk Linux / Mac
# Otomatis cek PHP, Composer, install dependency, .env, migrate, dan run server
#

set -e

cd "$(dirname "$0")"

echo "==> Backend Project Management - Setup & Run"
echo ""

# 1. Cek PHP
if ! command -v php >/dev/null 2>&1; then
  echo "[ERROR] PHP tidak ditemukan."
  echo "Install dulu: sudo apt install php8.3 php8.3-cli"
  exit 1
fi

PHP_MAJOR=$(php -r 'echo PHP_MAJOR_VERSION;')
PHP_MINOR=$(php -r 'echo PHP_MINOR_VERSION;')

if [ "$PHP_MAJOR" -lt 8 ] || { [ "$PHP_MAJOR" -eq 8 ] && [ "$PHP_MINOR" -lt 3 ]; }; then
  echo "[ERROR] Butuh PHP >= 8.3. Versi sekarang: $(php -r 'echo PHP_VERSION;')"
  exit 1
fi

echo "[OK] PHP $(php -r 'echo PHP_VERSION;')"

# 2. Cek extensions
MISSING=""
for ext in mbstring bcmath curl pdo_pgsql xml tokenizer openssl fileinfo; do
  if ! php -m | grep -qi "^${ext}$"; then
    MISSING="$MISSING $ext"
  fi
done

if [ -n "$MISSING" ]; then
  echo ""
  echo "[ERROR] PHP extensions kurang:$MISSING"
  echo ""
  echo "--- Diagnostic: lokasi konfigurasi PHP ---"
  php --ini
  echo "-------------------------------------------"
  echo ""

  INI_LOADED=$(php -r 'echo php_ini_loaded_file() ?: "";')

  if [ -z "$INI_LOADED" ]; then
    echo "[HINT] PHP belum punya php.ini (Loaded Configuration File = none)."
    echo ""
    echo "  Install extension dengan apt:"
    echo "    sudo apt install -y$(echo $MISSING | sed 's/ / php8.3-/g; s/^/ php8.3-/')"
  else
    echo "[HINT] php.ini sudah ke-load di: $INI_LOADED"
    echo ""
    echo "  Install extension dengan apt:"
    echo "    sudo apt install -y$(echo $MISSING | sed 's/ / php8.3-/g; s/^/ php8.3-/')"
    echo ""
    echo "  Setelah install, restart php-fpm / web server kalau pakai:"
    echo "    sudo systemctl restart php8.3-fpm"
  fi
  exit 1
fi

echo "[OK] Semua PHP extensions ada"

# 3. Cek Composer
COMPOSER_CMD="composer"
if ! command -v composer >/dev/null 2>&1; then
  if [ -x "$HOME/bin/composer" ]; then
    COMPOSER_CMD="$HOME/bin/composer"
    echo "[OK] Pakai Composer dari $COMPOSER_CMD"
  else
    echo "[ERROR] Composer tidak ditemukan."
    echo "Install dengan: curl -sS https://getcomposer.org/installer | php && sudo mv composer.phar /usr/local/bin/composer"
    exit 1
  fi
else
  echo "[OK] Composer $($COMPOSER_CMD --version | head -1)"
fi

echo ""

# 4. Install dependency kalau belum
if [ ! -d "vendor" ]; then
  echo "==> vendor/ belum ada, menjalankan composer install..."
  $COMPOSER_CMD install --no-interaction
  echo ""
fi

# 5. Setup .env kalau belum
if [ ! -f ".env" ]; then
  echo "==> Copy .env.example -> .env"
  cp .env.example .env
  echo ""
  echo "==> Konfigurasi PostgreSQL credentials (tekan Enter untuk pakai default)"
  read -r -p "    DB_USERNAME [postgres]: " DB_USERNAME_INPUT
  read -r -p "    DB_PASSWORD [root]:     " DB_PASSWORD_INPUT
  DB_USERNAME_INPUT=${DB_USERNAME_INPUT:-postgres}
  DB_PASSWORD_INPUT=${DB_PASSWORD_INPUT:-root}

  NEW_USER="$DB_USERNAME_INPUT" NEW_PASS="$DB_PASSWORD_INPUT" php -r '
    $c = file_get_contents(".env");
    $c = preg_replace("/^DB_USERNAME=.*/m", "DB_USERNAME=" . getenv("NEW_USER"), $c);
    $c = preg_replace("/^DB_PASSWORD=.*/m", "DB_PASSWORD=" . getenv("NEW_PASS"), $c);
    file_put_contents(".env", $c);
  '
  echo "[OK] .env dibuat dengan user '$DB_USERNAME_INPUT'"
  echo ""
fi

# 6. Generate APP_KEY kalau kosong
if ! grep -q "^APP_KEY=base64:" .env; then
  echo "==> Generate APP_KEY..."
  php artisan key:generate --ansi
  echo ""
fi

# 7. Auto-setup PostgreSQL database
get_env() {
  grep -E "^${1}=" .env | head -1 | cut -d= -f2- | tr -d '"' | tr -d "'"
}

DB_HOST=$(get_env DB_HOST)
DB_PORT=$(get_env DB_PORT)
DB_DATABASE=$(get_env DB_DATABASE)
DB_USERNAME=$(get_env DB_USERNAME)
DB_PASSWORD=$(get_env DB_PASSWORD)

: "${DB_HOST:=127.0.0.1}"
: "${DB_PORT:=5432}"

IMPORTED_DUMP=0

if command -v psql >/dev/null 2>&1; then
  echo "==> Cek koneksi PostgreSQL ($DB_USERNAME@$DB_HOST:$DB_PORT)..."

  if ! PGPASSWORD="$DB_PASSWORD" psql -h "$DB_HOST" -p "$DB_PORT" -U "$DB_USERNAME" -d postgres -tAc "SELECT 1" >/dev/null 2>&1; then
    echo "[ERROR] Tidak bisa konek PostgreSQL."
    echo "  - Pastikan service jalan: sudo systemctl status postgresql"
    echo "  - Verify DB_USERNAME & DB_PASSWORD di .env"
    echo "  - Set password user postgres: sudo -u postgres psql -c \"ALTER USER postgres WITH PASSWORD 'root';\""
    exit 1
  fi

  echo "[OK] Konek PostgreSQL"

  DB_EXIST=$(PGPASSWORD="$DB_PASSWORD" psql -h "$DB_HOST" -p "$DB_PORT" -U "$DB_USERNAME" -d postgres -tAc "SELECT 1 FROM pg_database WHERE datname='$DB_DATABASE'")
  if [ "$DB_EXIST" != "1" ]; then
    echo "==> Database '$DB_DATABASE' belum ada, membuat..."
    PGPASSWORD="$DB_PASSWORD" psql -h "$DB_HOST" -p "$DB_PORT" -U "$DB_USERNAME" -d postgres -c "CREATE DATABASE \"$DB_DATABASE\";"
  fi

  USERS_TABLE=$(PGPASSWORD="$DB_PASSWORD" psql -h "$DB_HOST" -p "$DB_PORT" -U "$DB_USERNAME" -d "$DB_DATABASE" -tAc "SELECT to_regclass('public.users')" 2>/dev/null)
  if [ -z "$USERS_TABLE" ] && [ -f "database/dump/project_management.sql" ]; then
    echo "==> Database fresh, import dari database/dump/project_management.sql..."
    PGPASSWORD="$DB_PASSWORD" psql -h "$DB_HOST" -p "$DB_PORT" -U "$DB_USERNAME" -d "$DB_DATABASE" -v ON_ERROR_STOP=1 -q -f database/dump/project_management.sql
    IMPORTED_DUMP=1
    echo "[OK] Dump imported (schema + demo data lengkap)"
  fi
else
  echo "[INFO] psql CLI tidak ada, skip auto-create DB. Pastikan database '$DB_DATABASE' sudah ada."
fi

# 8. Migrate (skip kalau baru import dump)
if [ "$IMPORTED_DUMP" -eq 0 ]; then
  echo "==> Migrate database..."
  if ! php artisan migrate --force 2>&1 | tee /tmp/laravel-migrate.log; then
    echo ""
    echo "[ERROR] Migrate gagal. Cek error di atas."
    exit 1
  fi

  USER_COUNT=$(php artisan tinker --execute='echo App\Models\User::count();' 2>/dev/null | tail -1)
  if [ "$USER_COUNT" = "0" ] || [ -z "$USER_COUNT" ]; then
    echo ""
    echo "==> Database kosong, menjalankan seeder..."
    php artisan db:seed --force
    echo ""
  fi
fi

# 9. Storage link
if [ ! -L "public/storage" ]; then
  php artisan storage:link 2>&1 | tail -1
fi

# 10. Run server
echo ""
echo "==> API jalan di http://localhost:8000"
echo "==> Akun demo: admin@test.com / admin123"
echo ""
php artisan serve --host=0.0.0.0 --port=8000
