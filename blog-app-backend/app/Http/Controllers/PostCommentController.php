<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\PostComment;
use App\Helpers\ApiResponse;
use App\Handlers\PostCommentHandler;
use Exception;

class PostCommentController extends Controller
{
    protected PostCommentHandler $handler;

    public function __construct(PostCommentHandler $handler)
    {
        $this->handler = $handler;
    }

    public function getComments($id)
    {
        try {
            $comments = $this->handler->getCommentsByPostId((int) $id);
            return ApiResponse::success($comments, 'Comments fetched successfully');
        } catch (Exception $e) {
            return ApiResponse::error('Unable to fetch comments', $e->getMessage(), 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'blog_post_id' => 'required|exists:blog_posts,id',
                'comment_text' => 'required|string',
            ]);

            $comment = $this->handler->createComment(
                Auth::id(),
                (int) $validated['blog_post_id'],
                $validated['comment_text']
            );

            return ApiResponse::success($comment, 'Comment added successfully', 201);
        } catch (Exception $e) {
            return ApiResponse::error('Unable to store comment', $e->getMessage(), 500);
        }
    }

    public function destroyComment($id)
    {
        try {
            $result = $this->handler->deleteComment((int) $id, (int) Auth::id());

            if ($result === 'not_found') {
                return ApiResponse::error('Comment not found', null, 404);
            }
            if ($result === 'unauthorized') {
                return ApiResponse::error('Unauthorized', null, 403);
            }

            return ApiResponse::success(null, 'Comment deleted successfully');
        } catch (Exception $e) {
            return ApiResponse::error('Unable to delete the comment', $e->getMessage(), 500);
        }
    }

    public function getUserComments()
    {
        try {
            $comments = $this->handler->getUserComments((int) Auth::id());
            return ApiResponse::success($comments, 'User comments fetched successfully');
        } catch (Exception $e) {
            return ApiResponse::error('Unable to fetch user comments', $e->getMessage(), 500);
        }
    }
}
