<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Project;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => Project::all(),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => 'required|string',
            'description' => 'required|string',
            'status' => 'required|in:AKTIF,DITUNDA,SELESAI',
            'startDate' => 'required|date',
            'endDate' => 'required|date|after_or_equal:startDate',
        ]);

        $project = Project::create([
            'name' => $data['name'],
            'description' => $data['description'],
            'status' => $data['status'],
            'start_date' => $data['startDate'],
            'end_date' => $data['endDate'],
            'owner_id' => $request->user()->id,
        ]);

        return response()->json([
            'success' => true,
            'data' => $project,
        ], 201);
    }

    public function show(Project $project): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $project,
        ]);
    }

    public function update(Request $request, Project $project): JsonResponse
    {
        $data = $request->validate([
            'name' => 'sometimes|required|string',
            'description' => 'sometimes|required|string',
            'status' => 'sometimes|required|in:AKTIF,DITUNDA,SELESAI',
            'startDate' => 'sometimes|required|date',
            'endDate' => 'sometimes|required|date',
            'progress' => 'sometimes|integer|min:0|max:100',
        ]);

        if (isset($data['startDate'])) {
            $data['start_date'] = $data['startDate'];
            unset($data['startDate']);
        }
        if (isset($data['endDate'])) {
            $data['end_date'] = $data['endDate'];
            unset($data['endDate']);
        }

        $project->update($data);

        return response()->json([
            'success' => true,
            'data' => $project->fresh(),
        ]);
    }

    public function destroy(Project $project): JsonResponse
    {
        $project->delete();
        return response()->json(null, 204);
    }
}
