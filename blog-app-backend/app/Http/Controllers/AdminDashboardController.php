<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\BlogPost;
use App\Models\PostComment;
use App\Models\PostReaction;
use Exception;

class AdminDashboardController extends Controller
{
    public function getInsights()
    {
        try {
            $totalPosts = BlogPost::count();
            $publishedPosts = BlogPost::where('post_status', 'published')->count();
            $pendingPosts = BlogPost::where('post_status', 'pending')->count();

            $totalReactions = PostReaction::count();
            $totalComments = PostComment::count();

            return response()->json([
                'totalPosts' => $totalPosts,
                'publishedPosts' => $publishedPosts,
                'pendingPosts' => $pendingPosts,
                'totalReactions' => $totalReactions,
                'totalComments' => $totalComments,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Failed to fetch admin insights',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}

