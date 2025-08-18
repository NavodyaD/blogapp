<?php

namespace App\Handlers;

use App\Models\BlogPost;
use App\Models\PostReaction;

class PostReactionHandler
{
    public function toggle(int $postId, int $userId): array
    {
        $post = BlogPost::find($postId);
        if (!$post) {
            return ['status' => 'not_found'];
        }

        $existing = PostReaction::where('user_id', $userId)
            ->where('blog_post_id', $postId)
            ->first();

        if ($existing) {
            $existing->delete();
            return ['status' => 'unliked'];
        }

        PostReaction::create([
            'user_id'      => $userId,
            'blog_post_id' => $postId,
        ]);

        return ['status' => 'liked'];
    }

    public function getReactions(int $postId, ?int $userId = null): array
    {
        $post = BlogPost::find($postId);
        if (!$post) {
            return ['status' => 'not_found'];
        }

        $count = $post->reactions()->count();
        $userLiked = $userId ? $post->reactions()->where('user_id', $userId)->exists() : false;

        return [
            'status'     => 'ok',
            'count'      => $count,
            'userLiked'  => $userLiked,
        ];
    }
}
