<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\BlogPost;
use App\Models\PostReaction;

class PostReactionController extends Controller
{
    public function toggleReaction($postId)
    {
        $user = Auth::user();

        $post = BlogPost::find($postId);

        if (!$post) {
            return response()->json(['error' => 'Post not found.'], 404);
        }

        $existing = PostReaction::where('user_id', $user->id)
            ->where('blog_post_id', $postId)
            ->first();

        if ($existing) {
            $existing->delete();
            return response()->json(['status' => 'unliked']);
        } else {
            PostReaction::create([
                'user_id' => $user->id,
                'blog_post_id' => $postId,
            ]);
            return response()->json(['status' => 'liked']);
        }
    }

    public function getReactions($postId)
    {
        $post = BlogPost::find($postId);

        if (!$post) {
            return response()->json(['error' => 'Post not found.'], 404);
        }

        $count = $post->reactions()->count();
        $userLiked = $post->reactions()->where('user_id', Auth::id())->exists();

        return response()->json([
            'count' => $count,
            'userLiked' => $userLiked,
        ]);
    }
}
