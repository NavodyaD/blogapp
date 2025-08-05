<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Models\PostComment;

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
}
