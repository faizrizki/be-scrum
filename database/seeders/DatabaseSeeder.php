<?php

namespace Database\Seeders;

use App\Models\Activity;
use App\Models\Comment;
use App\Models\Notification;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ===== Users =====
        $pm = User::create([
            'name' => 'Andi Wijaya',
            'email' => 'pm@test.com',
            'password' => Hash::make('pm123456'),
            'role' => 'PROJECT_MANAGER',
        ]);

        $admin = User::create([
            'name' => 'Admin Sistem',
            'email' => 'admin@test.com',
            'password' => Hash::make('admin123'),
            'role' => 'ADMIN',
        ]);

        $member = User::create([
            'name' => 'Budi Santoso',
            'email' => 'member@test.com',
            'password' => Hash::make('member123'),
            'role' => 'TEAM_MEMBER',
        ]);

        $client = User::create([
            'name' => 'Citra Lestari',
            'email' => 'client@test.com',
            'password' => Hash::make('client123'),
            'role' => 'CLIENT',
        ]);

        // ===== Project =====
        $project = Project::create([
            'name' => 'Website Redesign',
            'description' => 'Redesign perusahaan website dengan UI/UX modern',
            'status' => 'AKTIF',
            'start_date' => '2026-01-11',
            'end_date' => '2027-03-01',
            'progress' => 60,
            'owner_id' => $pm->id,
        ]);

        // ===== Tasks =====
        Task::create([
            'project_id' => $project->id,
            'title' => 'Setup Analytics',
            'description' => 'Integrasi Google Analytics dan Tracking Events',
            'status' => 'IN_PROGRESS',
            'priority' => 'MEDIUM',
            'progress' => 40,
            'deadline' => '2026-11-15',
            'assignee_id' => $pm->id,
        ]);

        Task::create([
            'project_id' => $project->id,
            'title' => 'Design Homepage Mockup',
            'description' => 'Buat Mockup Design untuk halaman utama Website',
            'status' => 'DONE',
            'priority' => 'HIGH',
            'progress' => 100,
            'deadline' => '2026-11-12',
            'assignee_id' => $member->id,
        ]);

        Task::create([
            'project_id' => $project->id,
            'title' => 'Konfigurasi CDN',
            'description' => 'Setup CloudFlare CDN untuk asset gambar',
            'status' => 'REVIEW',
            'priority' => 'LOW',
            'progress' => 80,
            'deadline' => '2026-11-20',
            'assignee_id' => $pm->id,
        ]);

        // ===== Additional Projects (untuk visualisasi dashboard) =====
        $project2 = Project::create([
            'name' => 'Mobile App Development',
            'description' => 'Pengembangan aplikasi mobile native untuk iOS & Android',
            'status' => 'AKTIF',
            'start_date' => '2026-02-01',
            'end_date' => '2026-09-30',
            'progress' => 35,
            'owner_id' => $pm->id,
        ]);

        Task::create([
            'project_id' => $project2->id,
            'title' => 'Wireframe Mobile',
            'description' => 'Buat wireframe untuk 10 halaman utama aplikasi',
            'status' => 'DONE',
            'priority' => 'HIGH',
            'progress' => 100,
            'deadline' => '2026-03-15',
            'assignee_id' => $member->id,
        ]);

        Task::create([
            'project_id' => $project2->id,
            'title' => 'Setup React Native Project',
            'description' => 'Inisialisasi project dan konfigurasi build',
            'status' => 'IN_PROGRESS',
            'priority' => 'MEDIUM',
            'progress' => 50,
            'deadline' => '2026-04-30',
            'assignee_id' => $pm->id,
        ]);

        Task::create([
            'project_id' => $project2->id,
            'title' => 'API Authentication Mobile',
            'description' => 'Implementasi login & JWT di mobile app',
            'status' => 'TODO',
            'priority' => 'HIGH',
            'progress' => 0,
            'deadline' => '2026-05-15',
            'assignee_id' => $member->id,
        ]);

        $project3 = Project::create([
            'name' => 'Migrasi Database',
            'description' => 'Migrasi dari MySQL 5.7 ke MySQL 8.0',
            'status' => 'SELESAI',
            'start_date' => '2025-10-01',
            'end_date' => '2025-12-31',
            'progress' => 100,
            'owner_id' => $pm->id,
        ]);

        Task::create([
            'project_id' => $project3->id,
            'title' => 'Backup Database Production',
            'description' => 'Full backup dengan dump SQL',
            'status' => 'DONE',
            'priority' => 'HIGH',
            'progress' => 100,
            'deadline' => '2025-10-15',
            'assignee_id' => $pm->id,
        ]);

        Task::create([
            'project_id' => $project3->id,
            'title' => 'Test Compatibility Query',
            'description' => 'Test semua query lama compatible dengan MySQL 8',
            'status' => 'DONE',
            'priority' => 'MEDIUM',
            'progress' => 100,
            'deadline' => '2025-11-30',
            'assignee_id' => $member->id,
        ]);

        $project4 = Project::create([
            'name' => 'Integrasi Payment Gateway',
            'description' => 'Integrasi Midtrans untuk pembayaran online',
            'status' => 'DITUNDA',
            'start_date' => '2026-03-01',
            'end_date' => '2026-06-30',
            'progress' => 20,
            'owner_id' => $pm->id,
        ]);

        Task::create([
            'project_id' => $project4->id,
            'title' => 'Setup Sandbox Midtrans',
            'description' => 'Daftar akun sandbox & dapat API key',
            'status' => 'DONE',
            'priority' => 'MEDIUM',
            'progress' => 100,
            'deadline' => '2026-03-10',
            'assignee_id' => $pm->id,
        ]);

        Task::create([
            'project_id' => $project4->id,
            'title' => 'Implementasi Snap Token',
            'description' => 'Generate snap token di backend untuk transaksi',
            'status' => 'TODO',
            'priority' => 'HIGH',
            'progress' => 0,
            'deadline' => '2026-04-15',
            'assignee_id' => $member->id,
        ]);

        $project5 = Project::create([
            'name' => 'Audit Keamanan Sistem',
            'description' => 'Audit keamanan menyeluruh untuk OWASP Top 10',
            'status' => 'AKTIF',
            'start_date' => '2026-04-01',
            'end_date' => '2026-07-31',
            'progress' => 80,
            'owner_id' => $pm->id,
        ]);

        Task::create([
            'project_id' => $project5->id,
            'title' => 'Penetration Testing',
            'description' => 'Run automated pen-test dengan OWASP ZAP',
            'status' => 'DONE',
            'priority' => 'HIGH',
            'progress' => 100,
            'deadline' => '2026-05-15',
            'assignee_id' => $pm->id,
        ]);

        Task::create([
            'project_id' => $project5->id,
            'title' => 'Review Auth Flow',
            'description' => 'Review implementasi Sanctum & session handling',
            'status' => 'REVIEW',
            'priority' => 'MEDIUM',
            'progress' => 90,
            'deadline' => '2026-06-30',
            'assignee_id' => $member->id,
        ]);

        Task::create([
            'project_id' => $project5->id,
            'title' => 'Fix Vulnerability XSS',
            'description' => 'Sanitasi input pada form komentar',
            'status' => 'IN_PROGRESS',
            'priority' => 'HIGH',
            'progress' => 60,
            'deadline' => '2026-07-15',
            'assignee_id' => $pm->id,
        ]);

        // ===== Comments =====
        Comment::create([
            'project_id' => $project->id,
            'author_id' => $member->id,
            'content' => 'Sudah cek mockup homepage, secara overall sudah oke.',
        ]);

        Comment::create([
            'project_id' => $project->id,
            'author_id' => $client->id,
            'content' => 'Tolong review bagian analytics ya.',
        ]);

        // ===== Notifications =====
        Notification::create([
            'user_id' => $pm->id,
            'title' => 'Task baru ditugaskan',
            'message' => "Anda mendapat task 'Setup Analytics' di proyek Website Redesign",
            'kind' => 'TASK',
            'read' => false,
        ]);

        Notification::create([
            'user_id' => $pm->id,
            'title' => 'Komentar baru',
            'message' => 'Budi Santoso mengomentari proyek Website Redesign',
            'kind' => 'COMMENT',
            'read' => false,
        ]);

        // ===== Activities =====
        Activity::create([
            'actor_id' => $pm->id,
            'message' => "membuat proyek 'Website Redesign'",
        ]);

        Activity::create([
            'actor_id' => $pm->id,
            'message' => "menambahkan task 'Setup Analytics'",
        ]);

        Activity::create([
            'actor_id' => $member->id,
            'message' => "mengomentari proyek 'Website Redesign'",
        ]);
    }
}
