<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CommentsController extends Controller
{
    /**
     * Display the specified resource.
     */
    public function index(Request $request, $bugId)
    {
        $perPage = $request->query('per_page', 5);

        $comments = Comment::with(['user', 'images'])
            ->where('bug_id', $bugId)
            ->latest()
            ->paginate($perPage);

        return response()->json($comments);
    }

    /**
     * Update the specified resource in storage.
     */
    public function store(Request $request, $bugId)
{
    $validated = $request->validate([
        'comment' => 'required|string',
    ]);

    $validated['bug_id'] = $bugId;
    $validated['user_id'] = Auth::id();

    $comment = Comment::create($validated);

    return response()->json($comment->load(['user', 'images']), 201);
}

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($commentId)
    {
        $comment = Comment::findOrFail($commentId);

        // Only allow owner to delete
        if ($comment->user_id !== Auth::id()) {
            return response()->json([
                'message' => 'Unauthorized'
            ], 403);
        }

        $comment->delete();

        return response()->json([
            'message' => 'Comment deleted successfully.'
        ]);
    }
}
