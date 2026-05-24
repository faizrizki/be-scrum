# Backend Project Management (Laravel)

REST API untuk aplikasi Project Management. Stack: **Laravel 12** + **Sanctum** + **MySQL**.

Frontend repo: `../TA-Project-Management` (Next.js 16 + Tailwind v4).

---

## Quick Start (3 Langkah)

### 1. Prasyarat

- **PHP 8.3+** — cek versi: `php --version`
- **Composer** — https://getcomposer.org/download/
- **MySQL 5.7+ / MariaDB 10.3+**
- **PHP Extensions:** `mbstring`, `bcmath`, `curl`, `xml`, `mysql`, `pdo_mysql`, `tokenizer`, `openssl`, `fileinfo`

Install extension di Ubuntu (kalau belum):
```bash
sudo apt install -y php8.3-mbstring php8.3-bcmath php8.3-curl php8.3-xml php8.3-mysql
```

Cek extension yang ada:
```bash
php -m | grep -iE "mbstring|bcmath|curl|pdo_mysql|xml|tokenizer|openssl"
```

### 2. Setup Database (MySQL)

**Pilih salah satu cara:**

#### A. Pakai `php artisan migrate --seed` (Recommended)

Buat database kosong dulu:
```bash
mysql -u root -p -e "CREATE DATABASE project_management CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
```

Nanti `run.sh` / `run.bat` (atau `php artisan migrate --seed` manual) yang isi schema + data.

#### B. Import SQL Dump Langsung (Lebih Cepat)

Sudah ada dump siap pakai di `database/dump/project_management.sql` (schema + demo data lengkap):

```bash
# 1. Buat database
mysql -u root -p -e "CREATE DATABASE project_management CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# 2. Import dump
mysql -u root -p project_management < database/dump/project_management.sql
```

Selesai — semua tabel + data demo (4 user, 1 project, 2 task, dll) langsung ada. Skip step `php artisan migrate --seed`.

> Tips: kalau pakai XAMPP/Laragon/MAMP, bisa import via phpMyAdmin → Import → upload file `project_management.sql`.

### 3. Install & Run

**Linux / Mac:**
```bash
./run.sh
```

**Windows:**
```cmd
run.bat
```

**Manual (semua OS):**
```bash
cp .env.example .env
# edit .env, sesuaikan DB credentials
php -r "echo getenv('PHP_INI_SCAN_DIR');" # verify
composer install
php artisan key:generate
php artisan migrate --seed
php artisan storage:link
php artisan serve
```

API jalan di **http://localhost:8000**.

---

## Konfigurasi `.env`

Edit `.env` sesuai MySQL setup kamu. Bagian penting:

```ini
APP_NAME="Project Management API"
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=project_management
DB_USERNAME=root        # ganti sesuai user MySQL kamu
DB_PASSWORD=            # ganti sesuai password MySQL kamu

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

Plus 1 proyek "Website Redesign" + 2 task + 2 komentar + 2 notifikasi + 3 log aktivitas.

---

## Endpoint Utama

Base URL: `http://localhost:8000/api`

| Method | URL | Auth |
|---|---|---|
| POST | `/login` | tidak |
| POST | `/register` | tidak |
| GET | `/me` | ya |
| POST | `/logout` | ya |
| GET/POST/PUT/DELETE | `/users` | ya (admin) |
| GET/POST/PUT/DELETE | `/projects` | ya |
| GET/POST | `/projects/{id}/tasks` | ya |
| PUT/DELETE | `/tasks/{id}` | ya |
| GET/POST | `/projects/{id}/comments` | ya |
| DELETE | `/comments/{id}` | ya |
| GET | `/notifications` | ya |
| PATCH | `/notifications/{id}/read` | ya |
| PATCH | `/notifications/read-all` | ya |
| GET/POST | `/activities` | ya |

Detail request/response per endpoint bisa dilihat via Postman Collection (lihat section di bawah).

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
└── seeders/
    └── DatabaseSeeder.php      # Demo data

routes/
└── api.php                     # Semua endpoint API (Sanctum protected)

config/
├── cors.php                    # CORS settings (default: allow all)
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
mysql -u root -p -e "DROP DATABASE IF EXISTS project_management; CREATE DATABASE project_management CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
mysql -u root -p project_management < database/dump/project_management.sql
```

---

## Re-generate SQL Dump (untuk maintainer)

Kalau ada perubahan schema/seed dan mau update `database/dump/project_management.sql`:

```bash
# Pastikan migrate + seed sudah jalan dengan data terbaru
php artisan migrate:fresh --seed

# Generate dump baru
mysqldump -u root -p --no-tablespaces --skip-comments --add-drop-table \
  project_management > database/dump/project_management.sql
```

Edit header comment di file kalau perlu (info credentials demo, jumlah row, dll).

---

## Common Issues

**"Connection refused" saat connect MySQL**
- Cek service jalan: `sudo systemctl status mysql` (Linux) atau buka XAMPP/Laragon GUI

**"Access denied for user 'root'@'localhost'"**
- Salah password. Verify `DB_USERNAME` & `DB_PASSWORD` di `.env`
- Atau MySQL root pakai socket auth — coba `sudo mysql` untuk akses, atau set password via `ALTER USER 'root'@'localhost' IDENTIFIED WITH mysql_native_password BY 'password';`

**"SQLSTATE[HY000] [2002] No such file or directory"**
- Default Unix socket tidak terdeteksi. Set `DB_HOST=127.0.0.1` (bukan `localhost`) di `.env`

**Migration fail "Table already exists"**
- Jalankan `php artisan migrate:fresh --seed` (akan drop semua tabel dulu)

**CORS error di frontend (browser console)**
- Edit `config/cors.php`, set `allowed_origins` ke `['http://localhost:3000']`
- Atau biarkan `['*']` untuk dev

**419 Page Expired / CSRF token mismatch**
- Pakai Bearer token (Sanctum), bukan session CSRF. Pastikan header `Accept: application/json` ada

**"Class 'Laravel\Sanctum\HasApiTokens' not found"**
- Run `composer require laravel/sanctum` lalu `php artisan migrate`

**"php artisan command not found"**
- Pastikan kamu di folder root project (di mana `artisan` file ada): `cd BE-PROJECT-MANAGEMENT`

**Port 8000 sudah dipakai**
- Pakai port lain: `php artisan serve --port=8001`

---

## Stack Detail

| Layer | Pilihan |
|---|---|
| Framework | Laravel 12 |
| Auth | Laravel Sanctum (token-based) |
| Database | MySQL 8 (via PDO) |
| PHP | 8.3+ |
| Manager | Composer |

---

## Lisensi & Kontribusi

Repository untuk portofolio / tugas akhir. Free to fork & learn.
