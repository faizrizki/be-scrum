<?php

use Illuminate\Support\Facades\Route;

// Backend ini API-only; frontend-nya Next.js terpisah. Route root sengaja
// tidak me-render view welcome supaya tidak bergantung pada aset Vite yang
// memang tidak dibangun saat deploy (lihat vercel.json).
Route::get('/', function () {
    return response()->json([
        'name' => config('app.name'),
        'status' => 'ok',
        'api' => url('/api'),
        'health' => url('/up'),
    ]);
});
