<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Throwable;

/**
 * Ping harian ke Supabase supaya project free-tier tidak di-pause
 * (Supabase mem-pause project yang tidak ada aktivitas selama 7 hari).
 *
 * Dipanggil oleh Vercel Cron (lihat vercel.json) dan GitHub Actions
 * (lihat .github/workflows/keep-alive.yml) sebagai cadangan.
 */
class KeepAliveController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $secret = config('app.cron_secret');

        if ($secret && ! hash_equals($secret, (string) $request->bearerToken())) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized.',
            ], 401);
        }

        try {
            $affected = DB::table('keep_alive')->where('id', 1)->update([
                'pinged_at' => now(),
                'ping_count' => DB::raw('ping_count + 1'),
            ]);

            if ($affected === 0) {
                DB::table('keep_alive')->insert([
                    'id' => 1,
                    'pinged_at' => now(),
                    'ping_count' => 1,
                ]);
            }

            $row = DB::table('keep_alive')->where('id', 1)->first();

            return response()->json([
                'success' => true,
                'data' => [
                    'database' => DB::connection()->getDatabaseName(),
                    'pingedAt' => $row?->pinged_at,
                    'pingCount' => (int) ($row?->ping_count ?? 0),
                ],
            ]);
        } catch (Throwable $e) {
            report($e);

            return response()->json([
                'success' => false,
                'message' => 'Keep-alive gagal: '.$e->getMessage(),
            ], 500);
        }
    }
}
