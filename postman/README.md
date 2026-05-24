# Postman Collection

Postman collection untuk testing API Project Management.

## File

- `project-management.postman_collection.json` — collection berisi semua endpoint
- `project-management-local.postman_environment.json` — environment untuk local dev (localhost:8000)

## Cara Import

1. Buka **Postman** (https://www.postman.com/downloads/)
2. Klik **Import** (tombol di kiri atas)
3. Drag & drop kedua file `.json` di atas, atau pilih via dialog
4. Setelah import:
   - Collection muncul di tab **Collections**
   - Environment muncul di tab **Environments**

## Cara Pakai

1. **Aktifkan environment:**
   - Pojok kanan atas Postman → dropdown environment → pilih **"Project Management - Local"**

2. **Login dulu untuk dapat token:**
   - Buka folder **Auth → Login (Admin)** atau **Login (Project Manager)**
   - Klik **Send**
   - Token otomatis tersimpan ke variable `{{token}}` (lihat console: `Token saved: ...`)

3. **Test endpoint lain:**
   - Semua request authenticated otomatis pakai Bearer `{{token}}` dari collection-level auth
   - Tinggal klik **Send** di endpoint yang mau dites

4. **Ganti ID resource via variables:**
   - Klik **collection name → Variables tab**
   - Edit `project_id`, `task_id`, `user_id`, `comment_id`, `notification_id`
   - Atau edit langsung di environment

## Endpoint Tersedia

### Auth (5)
- `POST /login` - Login Admin (dengan test script auto-save token)
- `POST /login` - Login Project Manager
- `POST /register` - Daftar akun baru
- `GET /me` - User current
- `POST /logout` - Logout (revoke token)

### Users / Admin only (4)
- `GET /users` - List
- `POST /users` - Create
- `PUT /users/{id}` - Update
- `DELETE /users/{id}` - Delete

### Projects (5)
- `GET /projects` - List
- `GET /projects/{id}` - Detail
- `POST /projects` - Create
- `PUT /projects/{id}` - Update
- `DELETE /projects/{id}` - Delete

### Tasks (4)
- `GET /projects/{id}/tasks` - List per project
- `POST /projects/{id}/tasks` - Create
- `PUT /tasks/{id}` - Update
- `DELETE /tasks/{id}` - Delete

### Comments (4)
- `GET /projects/{id}/comments` - List per project
- `POST /projects/{id}/comments` - Create (text only)
- `POST /projects/{id}/comments` - Create with file (multipart)
- `DELETE /comments/{id}` - Delete

### Notifications (3)
- `GET /notifications` - List user current
- `PATCH /notifications/{id}/read` - Mark single
- `PATCH /notifications/read-all` - Mark all

### Activities (2)
- `GET /activities?limit=N` - List recent
- `POST /activities` - Create manual log

Total: **27 request** terbagi 7 folder.

## Tips

**Auto-save token tanpa copy-paste**

Request `Auth → Login (Admin)` punya script di tab **Tests** yang otomatis simpan token ke variable. Tinggal klik Send sekali, lalu pakai endpoint lain langsung.

**Test alur lengkap**

Urutan rekomendasi pertama kali:
1. Login Admin
2. List Projects → catat `id` salah satu project
3. Edit collection variable `project_id` dengan id project tsb
4. List Tasks (per Project)
5. Create Task
6. List Comments
7. Create Comment with attachment (pilih file di field `attachments[]`)
8. Mark Notification as Read

**Test role-based access**

1. Login sebagai **Project Manager** (`pm@test.com`)
2. Coba `Users → List Users` → harus dapat **HTTP 403** (hanya admin)
3. Login sebagai **Admin** → coba lagi → harus sukses

**Upload file**

Di request **Create Comment (with file attachment)**:
- Klik tab **Body** → form-data
- Field `attachments[]` → klik dropdown Type → pilih **File** → klik **Select Files**
- Bisa multi-file dengan ulangi field `attachments[]`

## Konversi ke Format Lain

Postman support export ke:
- **OpenAPI 3.0** (Swagger) — klik collection → Export → OpenAPI 3.0
- **cURL** — klik request → Code (tombol `</>`) → cURL

## Multi-Environment (Optional)

Mau test ke staging/production? Duplicate environment file:
```bash
cp project-management-local.postman_environment.json project-management-staging.postman_environment.json
```
Edit `base_url` ke URL staging, import ke Postman.
