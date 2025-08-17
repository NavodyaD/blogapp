<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\PostComment;
use App\Helpers\ApiResponse;
use Exception;

class PostCommentController extends Controller
{
    public function getComments($id)
    {
        try {
            $comments = PostComment::with('user')
                ->where('blog_post_id', $id)
                ->get();

            return ApiResponse::success($comments, 'Comments fetched successfully');
        } catch (Exception $e) {
            return ApiResponse::error('Unable to fetch comments', $e->getMessage(), 500);
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

            return ApiResponse::success($comment, 'Comment added successfully', 201);
        } catch (Exception $e) {
            return ApiResponse::error('Unable to store comment', $e->getMessage(), 500);
        }
    }

    public function destroyComment($id)
    {
        try {
            $comment = PostComment::find($id);

            if (!$comment) {
                return ApiResponse::error('Comment not found', null, 404);
            }

            if ($comment->user_id !== Auth::id()) {
                return ApiResponse::error('Unauthorized', null, 403);
            }

            $comment->delete();

            return ApiResponse::success(null, 'Comment deleted successfully');
        } catch (Exception $e) {
            return ApiResponse::error('Unable to delete the comment', $e->getMessage(), 500);
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

            return ApiResponse::success($comments, 'User comments fetched successfully');
        } catch (Exception $e) {
            return ApiResponse::error('Unable to fetch user comments', $e->getMessage(), 500);
        }
    }


}
