<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use App\Models\Project;
use App\Models\Task;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    private function formatComment(Comment $c): array
    {
        return [
            'id' => $c->id,
            'projectId' => $c->project_id,
            'taskId' => $c->task_id,
            'authorId' => $c->author_id,
            'authorName' => $c->author?->name,
            'content' => $c->content,
            'attachments' => $c->attachments,
            'createdAt' => $c->created_at?->toIso8601String(),
        ];
    }

    public function index(Project $project): JsonResponse
    {
        $comments = $project->comments()
            ->whereNull('task_id')
            ->with(['author:id,name', 'attachments'])
            ->orderBy('created_at')
            ->get()
            ->map(fn ($c) => $this->formatComment($c));

        return response()->json([
            'success' => true,
            'data' => $comments,
        ]);
    }

    public function store(Request $request, Project $project): JsonResponse
    {
        if (! $request->user()->canComment()) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak punya izin membuat komentar',
            ], 403);
        }

        $data = $request->validate([
            'content' => 'required|string',
            'attachments.*' => 'file|max:10240',
        ]);

        $comment = $project->comments()->create([
            'author_id' => $request->user()->id,
            'content' => $data['content'],
        ]);

        $this->attachFiles($comment, $request);
        $comment->load(['author:id,name', 'attachments']);

        return response()->json([
            'success' => true,
            'data' => $this->formatComment($comment),
        ], 201);
    }

    public function taskIndex(Task $task): JsonResponse
    {
        $comments = $task->comments()
            ->with(['author:id,name', 'attachments'])
            ->orderBy('created_at')
            ->get()
            ->map(fn ($c) => $this->formatComment($c));

        return response()->json([
            'success' => true,
            'data' => $comments,
        ]);
    }

    public function taskStore(Request $request, Task $task): JsonResponse
    {
        if (! $request->user()->canComment()) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak punya izin membuat komentar',
            ], 403);
        }

        $data = $request->validate([
            'content' => 'required|string',
            'attachments.*' => 'file|max:10240',
        ]);

        $comment = $task->comments()->create([
            'project_id' => $task->project_id,
            'author_id' => $request->user()->id,
            'content' => $data['content'],
        ]);

        $this->attachFiles($comment, $request);
        $comment->load(['author:id,name', 'attachments']);

        return response()->json([
            'success' => true,
            'data' => $this->formatComment($comment),
        ], 201);
    }

    private function attachFiles(Comment $comment, Request $request): void
    {
        if (! $request->hasFile('attachments')) {
            return;
        }

        foreach ($request->file('attachments') as $file) {
            $path = $file->store('attachments', 'public');
            $comment->attachments()->create([
                'name' => $file->getClientOriginalName(),
                'size' => $file->getSize(),
                'path' => $path,
            ]);
        }
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
