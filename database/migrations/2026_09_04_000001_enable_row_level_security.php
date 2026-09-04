<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Supabase mengekspos seluruh schema "public" lewat REST API (PostgREST).
 * Tanpa Row Level Security, siapa pun yang punya publishable key bisa membaca
 * isi tabel - termasuk users beserta hash password-nya.
 *
 * Semua tabel di sini dikunci dengan RLS tanpa policy, jadi role "anon" dan
 * "authenticated" tidak bisa apa-apa. Laravel tidak terpengaruh karena konek
 * sebagai role "postgres" yang punya BYPASSRLS.
 */
return new class extends Migration
{
    private array $tables = [
        'activities',
        'attachments',
        'cache',
        'cache_locks',
        'comments',
        'failed_jobs',
        'job_batches',
        'jobs',
        'keep_alive',
        'migrations',
        'notifications',
        'password_reset_tokens',
        'personal_access_tokens',
        'projects',
        'sessions',
        'tasks',
        'users',
    ];

    public function up(): void
    {
        if (DB::connection()->getDriverName() !== 'pgsql') {
            return;
        }

        foreach ($this->tables as $table) {
            if (Schema::hasTable($table)) {
                DB::statement('alter table '.DB::getQueryGrammar()->wrapTable($table).' enable row level security');
            }
        }
    }

    public function down(): void
    {
        if (DB::connection()->getDriverName() !== 'pgsql') {
            return;
        }

        foreach ($this->tables as $table) {
            if (Schema::hasTable($table)) {
                DB::statement('alter table '.DB::getQueryGrammar()->wrapTable($table).' disable row level security');
            }
        }
    }
};
