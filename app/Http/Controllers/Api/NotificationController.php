<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $notifications = $request->user()
            ->notifications()
            ->orderByDesc('created_at')
            ->get()
            ->map(fn ($n) => [
                'id' => $n->id,
                'title' => $n->title,
                'message' => $n->message,
                'kind' => $n->kind,
                'read' => $n->read,
                'createdAt' => $n->created_at?->toIso8601String(),
            ]);

        return response()->json([
            'success' => true,
            'data' => $notifications,
        ]);
    }

    public function markRead(Request $request, Notification $notification): JsonResponse
    {
        if ($notification->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak punya akses',
            ], 403);
        }

        $notification->update(['read' => true]);

        return response()->json([
            'success' => true,
            'data' => ['id' => $notification->id, 'read' => true],
        ]);
    }

    public function markAllRead(Request $request): JsonResponse
    {
        $updated = $request->user()
            ->notifications()
            ->where('read', false)
            ->update(['read' => true]);

        return response()->json([
            'success' => true,
            'message' => 'Semua notifikasi ditandai dibaca',
            'data' => ['updated' => $updated],
        ]);
    }
}
