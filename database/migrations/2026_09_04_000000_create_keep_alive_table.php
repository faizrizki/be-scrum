<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Idempoten: tabel ini bisa saja sudah dibuat manual lewat SQL Editor
        // Supabase (lihat DEPLOYMENT.md langkah 2.2).
        if (! Schema::hasTable('keep_alive')) {
            Schema::create('keep_alive', function (Blueprint $table) {
                $table->id();
                $table->timestamp('pinged_at')->nullable();
                $table->unsignedBigInteger('ping_count')->default(0);
            });
        }

        if (! DB::table('keep_alive')->where('id', 1)->exists()) {
            DB::table('keep_alive')->insert([
                'id' => 1,
                'pinged_at' => now(),
                'ping_count' => 0,
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('keep_alive');
    }
};
