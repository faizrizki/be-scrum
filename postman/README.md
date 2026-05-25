# Postman Collection

Postman collection untuk testing API Project Management (Laravel 12 + Sanctum + PostgreSQL).

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
   - Buka folder **Auth → Login (Admin)** atau salah satu role lain
   - Klik **Send**
   - Token otomatis tersimpan ke variable `{{token}}` (lihat console: `Token saved: ...`)

3. **Test endpoint lain:**
   - Semua request authenticated otomatis pakai Bearer `{{token}}` dari collection-level auth
   - Tinggal klik **Send** di endpoint yang mau dites

4. **Ganti ID resource via variables:**
   - Klik **collection name → Variables tab**
   - Edit `project_id`, `task_id`, `user_id`, `comment_id`, `notification_id`
   - Atau edit langsung di environment

## Endpoint Tersedia (35 total)

### Auth (7)
- `POST /login` — Login Admin (dengan test script auto-save token)
- `POST /login` — Login Project Manager
- `POST /login` — Login Team Member
- `POST /login` — Login Client
- `POST /register` — Daftar akun baru (name auto = prefix email, role = CLIENT)
- `GET /me` — User current
- `POST /logout` — Logout (revoke token)

### Team Members (1)
- `GET /team-members` — List ringkas user untuk dropdown assignee (semua role boleh akses)

### Users / Admin only (4)
- `GET /users` — List
- `POST /users` — Create
- `PUT /users/{id}` — Update
- `DELETE /users/{id}` — Delete

### Projects (5)
- `GET /projects` — List
- `GET /projects/{id}` — Detail
- `POST /projects` — Create *(PM/Admin only)*
- `PUT /projects/{id}` — Update *(PM/Admin only)*
- `DELETE /projects/{id}` — Delete *(PM/Admin only)*

### Tasks (6)
- `GET /projects/{id}/tasks` — List per project
- `GET /tasks/{id}` — Detail dengan eager-load assignee & project
- `POST /projects/{id}/tasks` — Create *(PM/Admin only)*
- `PUT /tasks/{id}` — Update full *(PM/Admin only)*
- `PUT /tasks/{id}` — Update status saja *(Member juga boleh — hanya field `status` & `progress`)*
- `DELETE /tasks/{id}` — Delete *(PM/Admin only)*

### Comments (7)
- `GET /projects/{id}/comments` — List comment project (exclude task-scoped)
- `POST /projects/{id}/comments` — Create text *(Admin/PM/Member)*
- `POST /projects/{id}/comments` — Create dengan file (multipart) *(Admin/PM/Member)*
- `GET /tasks/{id}/comments` — List comment per task
- `POST /tasks/{id}/comments` — Create text comment di task
- `POST /tasks/{id}/comments` — Create comment task dengan file
- `DELETE /comments/{id}` — Delete (hanya author atau admin)

### Notifications (3)
- `GET /notifications` — List user current
- `PATCH /notifications/{id}/read` — Mark single
- `PATCH /notifications/read-all` — Mark all

### Activities (2)
- `GET /activities?limit=N` — List recent
- `POST /activities` — Create manual log *(non-Client)*

## Authorization Matrix

| Aksi | Admin | PM | Member | Client |
|---|:-:|:-:|:-:|:-:|
| Lihat semua (GET) | ✅ | ✅ | ✅ | ✅ |
| Project CRUD | ✅ | ✅ | ❌ | ❌ |
| Task CRUD penuh | ✅ | ✅ | ❌ | ❌ |
| Update status/progress task | ✅ | ✅ | ✅ | ❌ |
| Comment | ✅ | ✅ | ✅ | ❌ |
| User CRUD | ✅ | ❌ | ❌ | ❌ |

> BE return **HTTP 403** kalau client/member coba akses endpoint yang tidak diizinkan.

## Akun Demo

| Role | Email | Password |
|---|---|---|
| ADMIN | `admin@test.com` | `admin123` |
| PROJECT_MANAGER | `pm@test.com` | `pm123456` |
| TEAM_MEMBER | `member@test.com` | `member123` |
| CLIENT | `client@test.com` | `client123` |

## Tips

**Auto-save token tanpa copy-paste**

Setiap request `Auth → Login (*)` punya script di tab **Tests** yang otomatis simpan token ke variable. Tinggal klik Send sekali, lalu pakai endpoint lain langsung.

**Test alur lengkap**

Urutan rekomendasi pertama kali:
1. Login Admin
2. List Projects → catat `id` salah satu project
3. Edit collection variable `project_id` dengan id project tsb
4. List Tasks (per Project) → catat `task_id`
5. Get Task Detail (endpoint baru `GET /tasks/{id}`)
6. List Comments (per Task) → cek comment khusus task
7. Create Task Comment with attachment (pilih file di field `attachments[]`)
8. Mark Notification as Read

**Test role-based access (Authorization)**

1. Login sebagai **Team Member** (`Auth → Login (Team Member)`)
2. Coba `Tasks → Update Task (full)` → **HTTP 403** dengan `forbidden_fields`
3. Coba `Tasks → Update Task Status (Member friendly)` → **HTTP 200** sukses
4. Coba `Comments → Create Task Comment` → sukses
5. Logout, login sebagai **Client** (`Auth → Login (Client)`)
6. Coba `Projects → Create Project` → **HTTP 403**
7. Coba `Comments → Create Task Comment` → **HTTP 403**

**Test team-members untuk assignee**

1. Login (siapa saja)
2. `Team Members → List Team Members` → response berisi `[{id, name, role}]`
3. Pakai salah satu `id` di body **Create Task** field `assigneeId`

**Upload file**

Di request **Create Comment (with file attachment)** atau **Create Task Comment (with file attachment)**:
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
