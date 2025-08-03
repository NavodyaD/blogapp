<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Models\PostComment;

class PostCommentController extends Controller
{
    public function getComments($id)
    {
        $comments = PostComment::with('user')
        ->where('blog_post_id', $id)
        ->get();

        return response()->json($comments);
    }

    public function store(Request $request)
    {
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
    }
}
