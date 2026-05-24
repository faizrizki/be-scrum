<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ActivityController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $limit = (int) $request->query('limit', 50);

        $activities = Activity::with('actor:id,name')
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get()
            ->map(fn ($a) => [
                'id' => $a->id,
                'actorId' => $a->actor_id,
                'actorName' => $a->actor?->name,
                'message' => $a->message,
                'createdAt' => $a->created_at?->toIso8601String(),
            ]);

        return response()->json([
            'success' => true,
            'data' => $activities,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        if (! $request->user()->canComment()) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak punya izin membuat aktivitas',
            ], 403);
        }

        $data = $request->validate([
            'message' => 'required|string',
        ]);

        $activity = Activity::create([
            'actor_id' => $request->user()->id,
            'message' => $data['message'],
        ]);

        $activity->load('actor:id,name');

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $activity->id,
                'actorId' => $activity->actor_id,
                'actorName' => $activity->actor?->name,
                'message' => $activity->message,
                'createdAt' => $activity->created_at?->toIso8601String(),
            ],
        ], 201);
    }
}
