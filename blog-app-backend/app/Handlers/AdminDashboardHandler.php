<?php

namespace App\Handlers;

use App\Models\BlogPost;
use App\Models\PostComment;
use App\Models\PostReaction;
use App\Models\User;
use Illuminate\Support\Collection;

class AdminDashboardHandler
{
    public function insights(): array
    {
        return [
            'totalPosts'     => BlogPost::count(),
            'publishedPosts' => BlogPost::where('post_status', 'published')->count(),
            'pendingPosts'   => BlogPost::where('post_status', 'pending')->count(),
            'totalReactions' => PostReaction::count(),
            'totalComments'  => PostComment::count(),
        ];
    }

    public function listAdmins(): Collection
    {
        return User::role('admin')->select('name', 'email')->get();
    }
}
