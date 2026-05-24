<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use App\Models\Project;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    public function index(Project $project): JsonResponse
    {
        $comments = $project->comments()
            ->with(['author:id,name', 'attachments'])
            ->orderBy('created_at')
            ->get()
            ->map(fn ($c) => [
                'id' => $c->id,
                'projectId' => $c->project_id,
                'authorId' => $c->author_id,
                'authorName' => $c->author?->name,
                'content' => $c->content,
                'attachments' => $c->attachments,
                'createdAt' => $c->created_at?->toIso8601String(),
            ]);

        return response()->json([
            'success' => true,
            'data' => $comments,
        ]);
    }

    public function store(Request $request, Project $project): JsonResponse
    {
        $data = $request->validate([
            'content' => 'required|string',
            'attachments.*' => 'file|max:10240',
        ]);

        $comment = $project->comments()->create([
            'author_id' => $request->user()->id,
            'content' => $data['content'],
        ]);

        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $path = $file->store('attachments', 'public');
                $comment->attachments()->create([
                    'name' => $file->getClientOriginalName(),
                    'size' => $file->getSize(),
                    'path' => $path,
                ]);
            }
        }

        $comment->load(['author:id,name', 'attachments']);

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $comment->id,
                'projectId' => $comment->project_id,
                'authorId' => $comment->author_id,
                'authorName' => $comment->author?->name,
                'content' => $comment->content,
                'attachments' => $comment->attachments,
                'createdAt' => $comment->created_at?->toIso8601String(),
            ],
        ], 201);
    }

    public function destroy(Request $request, Comment $comment): JsonResponse
    {
        if ($comment->author_id !== $request->user()->id && ! $request->user()->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Anda hanya bisa menghapus komentar Anda sendiri',
            ], 403);
        }

        $comment->delete();
        return response()->json(null, 204);
    }
}
