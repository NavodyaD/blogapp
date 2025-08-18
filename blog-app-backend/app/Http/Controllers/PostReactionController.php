<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Helpers\ApiResponse;
use App\Handlers\PostReactionHandler;
use Exception;

class PostReactionController extends Controller
{
    protected PostReactionHandler $handler;

    public function __construct(PostReactionHandler $handler)
    {
        $this->handler = $handler;
    }

    public function toggleReaction($postId)
    {
        try {
            $userId = (int) Auth::id();

            $result = $this->handler->toggle((int) $postId, $userId);

            if ($result['status'] === 'not_found') {
                return ApiResponse::error('Post not found', null, 404);
            }

            if ($result['status'] === 'liked') {
                return ApiResponse::success(['status' => 'liked'], 'Reaction added successfully');
            }

            return ApiResponse::success(['status' => 'unliked'], 'Reaction removed successfully');
        } catch (Exception $e) {
            return ApiResponse::error('Failed to toggle reaction', $e->getMessage(), 500);
        }
    }

    public function getReactions($postId)
    {
        try {
            $userId = Auth::id();

            $result = $this->handler->getReactions((int) $postId, $userId ? (int) $userId : null);

            if ($result['status'] === 'not_found') {
                return ApiResponse::error('Post not found', null, 404);
            }

            return ApiResponse::success([
                'count'     => $result['count'],
                'userLiked' => $result['userLiked'],
            ], 'Reactions fetched successfully');
        } catch (Exception $e) {
            return ApiResponse::error('Failed to get reactions', $e->getMessage(), 500);
        }
    }
}
