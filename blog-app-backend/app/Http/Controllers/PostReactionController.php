<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\BlogPost;
use App\Models\PostReaction;
use App\Helpers\ApiResponse;
use Exception;

class PostReactionController extends Controller
{
    public function toggleReaction($postId)
    {
        try {
            $user = Auth::user();

            $post = BlogPost::find($postId);

            if (!$post) {
                return ApiResponse::error('Post not found', null, 404);
            }

            $existing = PostReaction::where('user_id', $user->id)
                ->where('blog_post_id', $postId)
                ->first();

            if ($existing) {
                $existing->delete();
                return ApiResponse::success(['status' => 'unliked'], 'Reaction removed successfully');
            } else {
                PostReaction::create([
                    'user_id' => $user->id,
                    'blog_post_id' => $postId,
                ]);
                return ApiResponse::success(['status' => 'liked'], 'Reaction added successfully');
            }
        } catch (Exception $e) {
            return ApiResponse::error('Failed to toggle reaction', $e->getMessage(), 500);
        }
    }

    public function getReactions($postId)
    {
        try {
            $post = BlogPost::find($postId);

            if (!$post) {
                return ApiResponse::error('Post not found', null, 404);
            }

            $count = $post->reactions()->count();
            $userLiked = $post->reactions()->where('user_id', Auth::id())->exists();

            $data = [
                'count' => $count,
                'userLiked' => $userLiked,
            ];

            return ApiResponse::success($data, 'Reactions fetched successfully');
        } catch (Exception $e) {
            return ApiResponse::error('Failed to get reactions', $e->getMessage(), 500);
        }
    }
}
