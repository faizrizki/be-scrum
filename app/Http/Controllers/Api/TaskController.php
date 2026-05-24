<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\Task;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    public function index(Project $project): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $project->tasks()->get(),
        ]);
    }

    public function store(Request $request, Project $project): JsonResponse
    {
        if (! $request->user()->canManageTasks()) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak punya izin membuat task',
            ], 403);
        }

        $data = $request->validate([
            'title' => 'required|string',
            'description' => 'required|string',
            'status' => 'required|in:TODO,IN_PROGRESS,REVIEW,DONE',
            'priority' => 'required|in:LOW,MEDIUM,HIGH',
            'progress' => 'required|integer|min:0|max:100',
            'deadline' => 'required|date',
            'assigneeId' => 'nullable|integer|exists:users,id',
        ]);

        $task = $project->tasks()->create([
            'title' => $data['title'],
            'description' => $data['description'],
            'status' => $data['status'],
            'priority' => $data['priority'],
            'progress' => $data['progress'],
            'deadline' => $data['deadline'],
            'assignee_id' => $data['assigneeId'] ?? null,
        ]);

        return response()->json([
            'success' => true,
            'data' => $task,
        ], 201);
    }

    public function update(Request $request, Task $task): JsonResponse
    {
        $user = $request->user();

        if (! $user->canUpdateTaskStatus()) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak punya izin mengubah task',
            ], 403);
        }

        // Member hanya boleh update status & progress (tidak title, deskripsi,
        // priority, deadline, assignee). PM & Admin bisa update semua field.
        if (! $user->canManageTasks()) {
            $request->merge(
                collect($request->only(['status', 'progress']))
                    ->filter(fn ($v) => $v !== null)
                    ->all()
            );

            $allowedKeys = ['status', 'progress'];
            $sent = array_keys($request->all());
            $forbidden = array_diff($sent, $allowedKeys);

            if (! empty($forbidden)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Member hanya boleh mengubah status & progress task',
                    'forbidden_fields' => array_values($forbidden),
                ], 403);
            }
        }

        $data = $request->validate([
            'title' => 'sometimes|required|string',
            'description' => 'sometimes|required|string',
            'status' => 'sometimes|required|in:TODO,IN_PROGRESS,REVIEW,DONE',
            'priority' => 'sometimes|required|in:LOW,MEDIUM,HIGH',
            'progress' => 'sometimes|integer|min:0|max:100',
            'deadline' => 'sometimes|required|date',
            'assigneeId' => 'nullable|integer|exists:users,id',
        ]);

        if (array_key_exists('assigneeId', $data)) {
            $data['assignee_id'] = $data['assigneeId'];
            unset($data['assigneeId']);
        }

        $task->update($data);

        return response()->json([
            'success' => true,
            'data' => $task->fresh(),
        ]);
    }

    public function destroy(Request $request, Task $task): JsonResponse
    {
        if (! $request->user()->canManageTasks()) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak punya izin menghapus task',
            ], 403);
        }

        $task->delete();
        return response()->json(null, 204);
    }
}
