# Backend Project Management (Laravel)

REST API untuk aplikasi Project Management. Stack: **Laravel 12** + **Sanctum** + **PostgreSQL**.

Frontend repo: `../fe-project-management` (Next.js 16 + Tailwind v4).

---

## Quick Start (3 Langkah)

### 1. Prasyarat

Yang harus terinstall di mesin:

- **PHP 8.3+** dengan extension: `mbstring`, `bcmath`, `curl`, `xml`, `pgsql`, `pdo_pgsql`, `tokenizer`, `openssl`, `fileinfo`
- **Composer** — https://getcomposer.org/download/
- **PostgreSQL 14+** (project ini dites di PostgreSQL 16) — pastikan `psql` CLI ada di PATH
- **Git** — buat clone repo

#### Linux / Ubuntu / WSL

```bash
# PHP + extensions
sudo apt install -y php8.3 php8.3-cli php8.3-mbstring php8.3-bcmath \
                    php8.3-curl php8.3-xml php8.3-pgsql

# Composer
curl -sS https://getcomposer.org/installer | php && sudo mv composer.phar /usr/local/bin/composer

# PostgreSQL
sudo apt install -y postgresql postgresql-client
sudo -u postgres psql -c "ALTER USER postgres WITH PASSWORD 'root';"

# Cek hasil
php -m | grep -iE "pdo_pgsql|mbstring|bcmath|curl|xml"
psql --version
```

#### Windows (Step-by-step Detail)

> Catatan: lakukan dari **cmd Administrator** untuk install. Setelah selesai, **restart cmd** supaya PATH ter-refresh sebelum jalankan `run.bat`.

1. **PHP 8.3+**

   Pilih salah satu cara:
   - **Mudah (recommended):** install **[Laragon](https://laragon.org/download/)** full version. Bundle PHP 8.3 + Composer + PATH-ready, semua extension sudah aktif.
   - **Manual:** download dari https://windows.php.net/download/ (pilih **VS16 x64 Thread Safe**) → extract ke `C:\php` → tambah `C:\php` ke **System Environment Variables → PATH**.

   Lalu **aktifkan extension** — buka `C:\php\php.ini` (atau `C:\laragon\bin\php\php-8.3.x\php.ini`), hapus tanda `;` di depan baris berikut:
   ```ini
   extension=pdo_pgsql
   extension=pgsql
   extension=mbstring
   extension=bcmath
   extension=curl
   extension=fileinfo
   extension=openssl
   extension=tokenizer
   ```

   Verify di cmd baru:
   ```cmd
   php -v
   php -m | findstr pgsql
   ```
   Harus muncul `pdo_pgsql` dan `pgsql`.

2. **Composer**

   - Sudah include kalau pakai Laragon, skip step ini.
   - Manual: download https://getcomposer.org/Composer-Setup.exe → install (installer auto-detect PHP).

   Verify:
   ```cmd
   composer --version
   ```

3. **PostgreSQL 16**

   Download dari https://www.postgresql.org/download/windows/ → **EDB installer**.

   Saat installer jalan, perhatikan:
   - **Password** untuk user `postgres` → **catat** (ini yang dipakai aplikasi)
   - **Port**: biarkan default `5432`
   - **Centang `Command Line Tools`** (penting — supaya `psql` ada di PATH)

   Verify:
   ```cmd
   psql --version
   ```

   Kalau `psql` tidak dikenali, tambah manual ke PATH: `C:\Program Files\PostgreSQL\16\bin`, lalu restart cmd.

4. **Git**

   Download https://git-scm.com/download/win → install (semua opsi default OK).

   Verify:
   ```cmd
   git --version
   ```

#### Verify Semua Prasyarat (Windows)

Sebelum lanjut, pastikan semua command di bawah muncul versi-nya (bukan *"is not recognized"*):

```cmd
php -v
php -m | findstr pgsql
composer --version
psql --version
git --version
```

Kalau salah satu missing → install dulu sebelum jalankan `run.bat`.

### 2. Setup Database (PostgreSQL)

Pastikan **service PostgreSQL jalan**:
```bash
sudo systemctl status postgresql        # Linux
# Windows: cek PostgreSQL service di Services.msc atau lewat pgAdmin
```

Database `project_management` **tidak perlu dibuat manual** — `run.sh` / `run.bat` akan otomatis:
1. Cek koneksi PostgreSQL pakai kredensial dari `.env`
2. Buat database kalau belum ada
3. **Import langsung dari `database/dump/project_management.sql`** (schema + 4 user + 5 project + 13 task + dst.) kalau DB masih kosong
4. Atau jalankan `migrate --seed` kalau `psql` CLI tidak tersedia

> Syarat agar auto-setup jalan: `psql` CLI ada di PATH. Sudah default di Ubuntu (`apt install postgresql-client`) maupun installer resmi Windows (centang opsi *Command Line Tools*).

### 3. Install & Run

**Linux / Mac:**
```bash
./run.sh
```

**Windows:**
```cmd
run.bat
```

#### Konfigurasi Kredensial PostgreSQL (Interaktif)

Saat pertama dijalankan, script akan **prompt kredensial PostgreSQL**:

```
==> Konfigurasi PostgreSQL credentials (tekan Enter untuk pakai default)
    DB_USERNAME [postgres]: <Enter atau ketik user kamu>
    DB_PASSWORD [root]:     <Enter atau ketik password kamu>
```

- **Default** (`postgres` / `root`) → tekan **Enter dua kali**, langsung jalan.
- **Kredensial beda** → ketik user & password kamu, lalu Enter. Script otomatis tulis ke `.env`, tidak perlu edit manual.

> Belum punya password untuk user `postgres`? Set dulu (sekali setup):
> ```bash
> sudo -u postgres psql -c "ALTER USER postgres WITH PASSWORD 'password_kamu';"
> ```
>
> Pada Windows installer PostgreSQL, password user `postgres` sudah di-set saat install — pakai password itu.

Setelah prompt diisi, script lanjut otomatis: konek ke PostgreSQL → buat DB → import dump → start server di `http://localhost:8000`.

> Mau ubah kredensial setelahnya? Edit langsung `.env` di bagian `DB_USERNAME` & `DB_PASSWORD`, lalu jalankan ulang `run.sh` / `run.bat`. Prompt hanya muncul saat `.env` belum ada.

**Manual (semua OS):**
```bash
cp .env.example .env
# edit .env, sesuaikan DB_USERNAME & DB_PASSWORD
composer install
php artisan key:generate
# pilih salah satu:
psql -h 127.0.0.1 -U postgres -c "CREATE DATABASE project_management;"
psql -h 127.0.0.1 -U postgres -d project_management -f database/dump/project_management.sql
# atau:
php artisan migrate --seed
php artisan storage:link
php artisan serve
```

API jalan di **http://localhost:8000**.

---

## Konfigurasi `.env`

Edit `.env` sesuai PostgreSQL setup kamu. Bagian penting:

```ini
APP_NAME="Project Management API"
APP_URL=http://localhost:8000

DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=project_management
DB_USERNAME=postgres        # ganti sesuai user PostgreSQL kamu
DB_PASSWORD=root            # ganti sesuai password PostgreSQL kamu

# Sanctum (default sudah cukup, ubah kalau frontend di port lain)
SANCTUM_STATEFUL_DOMAINS=localhost:3000,127.0.0.1:3000
```

---

## Akun Demo (Auto-Seed)

Setelah `php artisan migrate --seed` jalan, ada 4 akun siap pakai:

| Role | Email | Password |
|---|---|---|
| Admin | `admin@test.com` | `admin123` |
| Project Manager | `pm@test.com` | `pm123456` |
| Team Member | `member@test.com` | `member123` |
| Client | `client@test.com` | `client123` |

Plus 5 proyek (Website Redesign, Mobile App, Migrasi Database, Payment Gateway, Audit Keamanan) + 13 task + 2 komentar + 2 notifikasi + 3 log aktivitas.

---

## Endpoint Utama

Base URL: `http://localhost:8000/api`

| Method | URL | Auth | Akses |
|---|---|---|---|
| POST | `/login` | - | semua |
| POST | `/register` | - | semua |
| GET | `/me` | ya | semua role |
| POST | `/logout` | ya | semua role |
| GET | `/team-members` | ya | semua role (untuk dropdown assignee) |
| GET/POST/PUT/DELETE | `/users` | ya | **admin only** |
| GET | `/projects` | ya | semua role |
| POST/PUT/DELETE | `/projects` | ya | **admin/PM** |
| GET | `/projects/{id}/tasks` | ya | semua role |
| POST | `/projects/{id}/tasks` | ya | **admin/PM** |
| GET | `/tasks/{id}` | ya | semua role |
| PUT | `/tasks/{id}` | ya | admin/PM full, member: status & progress only |
| DELETE | `/tasks/{id}` | ya | **admin/PM** |
| GET/POST | `/projects/{id}/comments` | ya | GET: semua, POST: non-client |
| GET/POST | `/tasks/{id}/comments` | ya | GET: semua, POST: non-client |
| DELETE | `/comments/{id}` | ya | author atau admin |
| GET | `/notifications` | ya | milik sendiri |
| PATCH | `/notifications/{id}/read` | ya | milik sendiri |
| PATCH | `/notifications/read-all` | ya | milik sendiri |
| GET | `/activities` | ya | semua role |
| POST | `/activities` | ya | non-client |

Detail request/response per endpoint bisa dilihat via Postman Collection (lihat section di bawah).

### Authorization Matrix

| Aksi | Admin | PM | Member | Client |
|---|:-:|:-:|:-:|:-:|
| Lihat semua (GET) | ✅ | ✅ | ✅ | ✅ |
| Project CRUD | ✅ | ✅ | ❌ | ❌ |
| Task CRUD penuh | ✅ | ✅ | ❌ | ❌ |
| Update status/progress task | ✅ | ✅ | ✅ | ❌ |
| Comment (project / task) | ✅ | ✅ | ✅ | ❌ |
| Upload attachment | ✅ | ✅ | ✅ | ❌ |
| User CRUD | ✅ | ❌ | ❌ | ❌ |

Defense-in-depth: BE return **HTTP 403** kalau client/member coba akses endpoint yang tidak diizinkan, walaupun UI di-bypass.

---

## Test API Cepat

```bash
# 1. Login -> dapat token
TOKEN=$(curl -s -X POST http://localhost:8000/api/login \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{"email":"admin@test.com","password":"admin123"}' \
  | grep -oE '"token":"[^"]+"' | cut -d'"' -f4)

echo "Token: $TOKEN"

# 2. Pakai token untuk endpoint lain
curl http://localhost:8000/api/projects \
  -H "Authorization: Bearer $TOKEN" \
  -H "Accept: application/json"

# 3. Test admin endpoint
curl http://localhost:8000/api/users \
  -H "Authorization: Bearer $TOKEN" \
  -H "Accept: application/json"

# 4. Test logout
curl -X POST http://localhost:8000/api/logout \
  -H "Authorization: Bearer $TOKEN" \
  -H "Accept: application/json"
```

---

## Test API dengan Postman

Sudah disiapkan collection lengkap (27 endpoint) di folder `postman/`. Tinggal import.

### 1. Download & Install Postman

https://www.postman.com/downloads/ — pilih sesuai OS (Windows/Mac/Linux).

### 2. Import Collection + Environment

1. Buka Postman → klik tombol **Import** (kiri atas)
2. Drag & drop **2 file** sekaligus dari folder `postman/`:
   - `project-management.postman_collection.json` (collection 27 endpoint)
   - `project-management-local.postman_environment.json` (variable lokal)
3. Klik **Import**

### 3. Aktifkan Environment

Pojok kanan atas Postman → dropdown environment → pilih **"Project Management - Local"**.

Verify variable `base_url` = `http://localhost:8000/api`.

### 4. Login & Auto-save Token

1. Expand collection **"Project Management API"** di sidebar kiri
2. Buka folder **Auth → Login (Admin)**
3. Klik tombol **Send**
4. Cek response — kalau sukses, status `200` dan response body ada `data.token`
5. **Token otomatis tersimpan** ke variable `{{token}}` (cek di Postman Console: `Token saved: ...`)

> Tips: ada juga "Login (Project Manager)" untuk test sebagai PM.

### 5. Test Endpoint Lain

Semua endpoint authenticated (kecuali Login & Register) otomatis pakai header `Authorization: Bearer {{token}}`.

Contoh test cepat:
- **Projects → List Projects** → Send → harus dapat list proyek seed
- **Users → List Users** (sebagai admin) → Send → harus dapat 4 user
- **Notifications → List Notifications** → Send → harus dapat notif user current

### 6. Ganti ID Resource

Sebagian endpoint pakai variable seperti `{{project_id}}`, `{{task_id}}`. Cara ganti:

- **Cara A** — Edit collection variable:
  - Klik nama collection (kanan-klik) → **Edit** → tab **Variables**
  - Ubah value, **Save**

- **Cara B** — Edit environment:
  - Sidebar **Environments** → pilih "Project Management - Local"
  - Ubah value, **Save**

- **Cara C** — Override per request:
  - Klik tab **Params** atau langsung edit URL

### 7. Test Upload File (Comment dengan Lampiran)

1. Buka **Comments → Create Comment (with file attachment)**
2. Tab **Body** → format `form-data`
3. Field `attachments[]` → klik dropdown type kolom **Value** → pilih **File**
4. Klik tombol **Select Files** → pilih file dari komputer
5. Bisa tambah field `attachments[]` lagi untuk multi-file
6. Klik **Send**

### 8. Test Role-Based Access

1. Login sebagai **Project Manager** (`Auth → Login (Project Manager)` → Send)
2. Coba **Users → List Users** → Send → harus dapat **HTTP 403** ("Hanya admin...")
3. Kembali login sebagai **Admin** → coba lagi → harus sukses

### Detail Lengkap

Untuk dokumentasi penuh setiap endpoint + script auto-save token + tips advance, baca: **`postman/README.md`**.

### Alternatif Tool Lain

Collection format `.json` ini juga bisa di-import ke:
- **Insomnia** (https://insomnia.rest/) — File → Import → pilih file
- **Thunder Client** (VS Code extension) — Collections → Import
- **Bruno** (https://www.usebruno.com/) — Import → Postman Collection

---

## Auth (Sanctum Token)

Setiap request authenticated kirim header:
```
Authorization: Bearer <token>
Accept: application/json
```

Token didapat dari response `POST /api/login` di field `data.token`. Token tidak expire (kecuali di-set manual via Sanctum config).

---

## Struktur Folder

```
app/
├── Http/
│   ├── Controllers/Api/        # 7 controllers
│   │   ├── AuthController.php
│   │   ├── UserController.php
│   │   ├── ProjectController.php
│   │   ├── TaskController.php
│   │   ├── CommentController.php
│   │   ├── NotificationController.php
│   │   └── ActivityController.php
│   └── Middleware/
│       └── EnsureAdmin.php     # cek role ADMIN
└── Models/                     # 7 models + relationships
    ├── User.php (HasApiTokens, role)
    ├── Project.php
    ├── Task.php
    ├── Comment.php
    ├── Attachment.php
    ├── Notification.php
    └── Activity.php

database/
├── migrations/                 # Schema (7 tabel + Sanctum + Laravel base)
├── dump/
│   └── project_management.sql  # pg_dump (schema + seed data)
└── seeders/
    └── DatabaseSeeder.php      # Demo data

routes/
└── api.php                     # Semua endpoint API (Sanctum protected)

config/
├── cors.php                    # CORS settings (default: allow all)
├── database.php                # Default connection: pgsql
└── sanctum.php                 # Sanctum config
```

---

## Reset Data Demo

```bash
php artisan migrate:fresh --seed
```

Akan drop semua tabel, re-create, dan isi ulang dari `DatabaseSeeder`.

Atau re-import dari SQL dump:
```bash
PGPASSWORD=root psql -h 127.0.0.1 -U postgres -d postgres \
  -c "DROP DATABASE IF EXISTS project_management;" \
  -c "CREATE DATABASE project_management;"

PGPASSWORD=root psql -h 127.0.0.1 -U postgres -d project_management \
  -f database/dump/project_management.sql
```

---

## Re-generate SQL Dump (untuk maintainer)

Kalau ada perubahan schema/seed dan mau update `database/dump/project_management.sql`:

```bash
# Pastikan migrate + seed sudah jalan dengan data terbaru
php artisan migrate:fresh --seed

# Generate dump baru (portable, idempotent — bisa di-restore ulang)
PGPASSWORD=root pg_dump -h 127.0.0.1 -U postgres \
  --clean --if-exists --no-owner --no-privileges \
  -d project_management \
  > database/dump/project_management.sql
```

Penjelasan flag:
- `--clean --if-exists` — generate `DROP TABLE IF EXISTS` di awal, dump bisa di-rerun
- `--no-owner --no-privileges` — tidak include `OWNER TO postgres` / `GRANT`, portable lintas user

---

## Common Issues

### Khusus Windows

**`'php' is not recognized as an internal or external command`**
- PHP belum di-PATH. Kalau pakai Laragon, jalankan dari Laragon's *Terminal* (bukan cmd biasa).
- Manual: tambah folder PHP (mis. `C:\php`) ke **System Environment Variables → PATH**, lalu **restart cmd**.

**`'psql' is not recognized`**
- PostgreSQL Command Line Tools tidak ke-install / belum di-PATH.
- Re-run installer PostgreSQL dan centang *Command Line Tools*, **atau** tambah manual: `C:\Program Files\PostgreSQL\16\bin` ke PATH, lalu restart cmd.

**`'composer' is not recognized`**
- Install Composer dari https://getcomposer.org/Composer-Setup.exe (auto-detect PHP).

**`Could not open input file: artisan`**
- Kamu tidak di folder project. `cd` dulu ke `be-project-management` lalu jalankan `run.bat`.

**`extension=pdo_pgsql` tidak aktif walau sudah uncomment di `php.ini`**
- Pastikan kamu edit `php.ini` yang benar. Cek path-nya: `php --ini` (lihat baris `Loaded Configuration File`).
- Untuk Laragon, edit via menu **Laragon → PHP → php.ini**, lalu restart Laragon.
- Pastikan file DLL ada di `ext/`: `php_pdo_pgsql.dll` dan `php_pgsql.dll`.

**Password user `postgres` lupa**
- Buka **SQL Shell (psql)** dari Start Menu → login pakai password lama (kalau ingat), ganti password:
  ```sql
  ALTER USER postgres WITH PASSWORD 'password_baru';
  ```
- Atau reset via `pg_hba.conf` (ganti method jadi `trust` sementara) — googling "reset postgres password windows" untuk panduan detail.

**`run.bat` exit langsung tanpa pesan**
- Buka cmd dulu, lalu **drag `run.bat` ke window cmd** dan tekan Enter. Dengan cara ini, pesan error tidak akan hilang setelah script selesai.

**`UnicodeEncodeError` atau karakter aneh di output**
- Default cmd Windows pakai code page cp1252. Ganti ke UTF-8: `chcp 65001` sebelum `run.bat`. Atau pakai Windows Terminal (sudah default UTF-8).

### Umum (semua OS)

**"Connection refused" saat connect PostgreSQL**
- Cek service jalan: `sudo systemctl status postgresql` (Linux) / `Services.msc` → PostgreSQL → Status (Windows)
- Cek port listen: `ss -tlnp | grep 5432` (Linux) / `netstat -ano | findstr 5432` (Windows)
- Default PostgreSQL Linux hanya listen di socket Unix. Pastikan `listen_addresses = 'localhost'` di `postgresql.conf`

**"FATAL: password authentication failed for user 'postgres'"**
- Salah password. Verify `DB_USERNAME` & `DB_PASSWORD` di `.env`
- Set password user `postgres`: `sudo -u postgres psql -c "ALTER USER postgres WITH PASSWORD 'root';"`
- Pastikan `pg_hba.conf` pakai `md5` / `scram-sha-256` untuk host `127.0.0.1/32`, bukan `peer` only

**"FATAL: Peer authentication failed for user 'postgres'"**
- Default Ubuntu pakai peer auth via socket. Pakai TCP: set `DB_HOST=127.0.0.1` (bukan `localhost` di .env) dan login lewat host:
  `psql -h 127.0.0.1 -U postgres` (bukan tanpa `-h`)

**"SQLSTATE[08006] could not find driver"**
- Extension `pdo_pgsql` belum terinstall.
  - Linux: `sudo apt install php8.3-pgsql` lalu restart php-fpm/server
  - Windows: uncomment `extension=pdo_pgsql` di `php.ini`, lalu restart cmd / Laragon

**Migration fail "relation already exists"**
- Jalankan `php artisan migrate:fresh --seed` (akan drop semua tabel dulu)

**Migration error "syntax error at or near 'AFTER'"**
- PostgreSQL tidak support `->after('column')` di Schema builder. Hapus modifier `after()` dari migration

**CORS error di frontend (browser console)**
- Edit `config/cors.php`, set `allowed_origins` ke `['http://localhost:3000']`
- Atau biarkan `['*']` untuk dev

**419 Page Expired / CSRF token mismatch**
- Pakai Bearer token (Sanctum), bukan session CSRF. Pastikan header `Accept: application/json` ada

**"Class 'Laravel\Sanctum\HasApiTokens' not found"**
- Run `composer require laravel/sanctum` lalu `php artisan migrate`

**"php artisan command not found"**
- Pastikan kamu di folder root project (di mana `artisan` file ada): `cd be-project-management`

**Port 8000 sudah dipakai**
- Pakai port lain: `php artisan serve --port=8001`

---

## Stack Detail

| Layer | Pilihan |
|---|---|
| Framework | Laravel 12 |
| Auth | Laravel Sanctum (token-based) |
| Database | PostgreSQL 16 (via PDO) |
| PHP | 8.3+ |
| Manager | Composer |

---

## Lisensi & Kontribusi

Repository untuk portofolio / tugas akhir. Free to fork & learn.
