<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\BlogPost;
use App\Models\PostComment;
use App\Models\PostReaction;
use App\Models\User;
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

    public function assignUser(Request $request, $email) {
        $user = User::where('email', $email);

        if(!$user) {
            return response()->json(['message'=>'Cannot find the user for this email'], 404);
        }

        $user->assignRole('admin');

        return rsponse()->json(['message'=>'Gave admin permissions to user', 'user' => $user]);
    }


    public function listAdmins()
    {
        try {
            $admins = User::role('admin')->select('name', 'email')->get();
            return response()->json($admins, 200);
        } catch (Exception $e) {
            return response()->json(['message' => 'Failed to fetch admins', 'error' => $e->getMessage()], 500);
        }
    }
}
