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
for ext in mbstring bcmath curl pdo_mysql xml tokenizer openssl fileinfo; do
  if ! php -m | grep -qi "^${ext}$"; then
    MISSING="$MISSING $ext"
  fi
done

if [ -n "$MISSING" ]; then
  echo "[ERROR] PHP extensions kurang:$MISSING"
  echo "Install dengan: sudo apt install -y$(echo $MISSING | sed 's/ / php8.3-/g; s/^/ php8.3-/')"
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
  echo "[INFO] Edit .env, sesuaikan DB_USERNAME & DB_PASSWORD dengan MySQL kamu."
  echo "      Lalu jalankan ./run.sh lagi."
  exit 0
fi

# 6. Generate APP_KEY kalau kosong
if ! grep -q "^APP_KEY=base64:" .env; then
  echo "==> Generate APP_KEY..."
  php artisan key:generate --ansi
  echo ""
fi

# 7. Cek koneksi DB & migrate
echo "==> Test koneksi DB & migrate..."
if ! php artisan migrate --force 2>&1 | tee /tmp/laravel-migrate.log; then
  echo ""
  echo "[ERROR] Migrate gagal. Cek error di atas."
  echo "Tips:"
  echo "  - Pastikan database 'project_management' sudah dibuat di MySQL"
  echo "    mysql -u root -p -e \"CREATE DATABASE project_management CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;\""
  echo "  - Verify DB_USERNAME & DB_PASSWORD di .env"
  exit 1
fi

# 8. Seed kalau belum ada user
USER_COUNT=$(php artisan tinker --execute='echo App\Models\User::count();' 2>/dev/null | tail -1)
if [ "$USER_COUNT" = "0" ] || [ -z "$USER_COUNT" ]; then
  echo ""
  echo "==> Database kosong, menjalankan seeder..."
  php artisan db:seed --force
  echo ""
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
