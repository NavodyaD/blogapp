<?php

namespace App\Handlers;

use App\Models\PostComment;
use Illuminate\Support\Collection;

class PostCommentHandler
{
    public function getCommentsByPostId(int $postId): Collection
    {
        return PostComment::with('user')
            ->where('blog_post_id', $postId)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function createComment(int $userId, int $postId, string $text): PostComment
    {
        return PostComment::create([
            'user_id'      => $userId,
            'blog_post_id' => $postId,
            'comment_text' => $text,
        ]);
    }

    public function deleteComment(int $commentId, int $requestingUserId): string
    {
        $comment = PostComment::find($commentId);

        if (!$comment) {
            return 'not_found';
        }

        if ($comment->user_id !== $requestingUserId) {
            return 'unauthorized';
        }

        $comment->delete();
        return 'deleted';
    }

    public function getUserComments(int $userId): Collection
    {
        return PostComment::with('user')
            ->where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->get();
    }
}
