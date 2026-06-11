<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => User::select('id', 'name', 'email', 'role')->get(),
        ]);
    }

    public function teamMembers(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => User::select('id', 'name', 'role')->orderBy('name')->get(),
        ]);
    }

    public function teamMembersOverview(): JsonResponse
    {
        $users = User::with(['assignedTasks.project:id,name'])
            ->select('id', 'name', 'role')
            ->orderBy('name')
            ->get()
            ->map(function ($user) {
                $tasks = $user->assignedTasks;

                $projects = $tasks
                    ->groupBy('project_id')
                    ->map(function ($group, $projectId) {
                        $first = $group->first();
                        return [
                            'projectId' => (int) $projectId,
                            'projectName' => $first->project?->name,
                            'tasks' => $group->map(fn ($t) => [
                                'id' => $t->id,
                                'title' => $t->title,
                                'status' => $t->status,
                                'storyPoints' => $t->story_points ?? 0,
                            ])->values(),
                        ];
                    })
                    ->values();

                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'role' => $user->role,
                    'totalProjects' => $projects->count(),
                    'totalTasks' => $tasks->count(),
                    'projects' => $projects,
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $users,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => 'required|string',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
            'role' => 'required|in:ADMIN,PROJECT_MANAGER,TEAM_MEMBER,CLIENT',
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'role' => $data['role'],
        ]);

        return response()->json([
            'success' => true,
            'data' => $user->only('id', 'name', 'email', 'role'),
        ], 201);
    }

    public function update(Request $request, User $user): JsonResponse
    {
        $data = $request->validate([
            'name' => 'sometimes|required|string',
            'email' => 'sometimes|required|email|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:6',
            'role' => 'sometimes|required|in:ADMIN,PROJECT_MANAGER,TEAM_MEMBER,CLIENT',
        ]);

        if (! empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        $user->update($data);

        return response()->json([
            'success' => true,
            'data' => $user->only('id', 'name', 'email', 'role'),
        ]);
    }

    public function destroy(User $user): JsonResponse
    {
        $user->delete();
        return response()->json(null, 204);
    }
}
