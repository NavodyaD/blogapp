<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\PostComment;
use Exception;

class PostCommentController extends Controller
{
    public function getComments($id)
    {
        try {
            $comments = PostComment::with('user')
            ->where('blog_post_id', $id)
            ->get();

            return response()->json($comments);

        } catch (Exception $e) {
            return response()->json([
                'message' => 'Unable to fetch comments',
                'error' => $e->getMessages()
            ], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'blog_post_id' => 'required|exists:blog_posts,id',
                'comment_text' => 'required|string',
            ]);

            $comment = PostComment::create([
                'user_id' => Auth::id(),
                'blog_post_id' => $request->blog_post_id,
                'comment_text' => $request->comment_text,
            ]);

            return response()->json($comment, 201);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Unable to store comment',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroyComment($id)
    {
        try {
            $comment = PostComment::find($id);

            if (!$comment) {
                return response()->json(['message' => 'Comment not found'], 404);
            }

            if ($comment->user_id !== Auth::id()) {
                return response()->json(['message' => 'Unauthorized'], 403);
            }

            $comment->delete();

            return response()->json(['message' => 'Comment deleted successfully']);

        } catch (Exception $e) {
            return response()->json([
                'message' => 'Unable to delete the comment',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function getUserComments()
    {
        try {
            $userId = Auth::id();

            $comments = PostComment::with('user')
                ->where('user_id', $userId)
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json($comments);

        } catch (Exception $e) {
            return response()->json([
                'message' => 'Unable to fetch user comments',
                'error' => $e->getMessage()
            ], 500);
        }
    }

}
