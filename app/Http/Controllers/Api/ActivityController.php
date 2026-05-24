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
        $data = $request->validate([
            'message' => 'required|string',
        ]);

        $activity = Activity::create([
            'actor_id' => $request->user()->id,
            'message' => $data['message'],
        ]);

        return response()->json([
            'success' => true,
            'data' => $activity,
        ], 201);
    }
}
