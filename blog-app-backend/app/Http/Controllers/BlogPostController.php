<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Exception;

use App\Jobs\PostApprovedEmail;
use App\Helpers\ApiResponse;
use App\Models\BlogPost;
use App\Handlers\BlogPostHandler;

class BlogPostController extends Controller
{
    protected BlogPostHandler $handler;

    public function __construct(BlogPostHandler $handler)
    {
        $this->handler = $handler;
    }

    public function index()
    {
        try {
            $posts = $this->handler->getPublishedPaginated(6);
            return ApiResponse::success($posts, 'Fetched published posts');
        } catch (Exception $e) {
            return ApiResponse::error('Failed to fetch posts', $e->getMessage());
        }
    }

    public function allPosts()
    {
        try {
            $posts = $this->handler->getAllPosts();
            return ApiResponse::success($posts, 'Fetched all posts');
        } catch (Exception $e) {
            return ApiResponse::error('Failed to fetch posts', $e->getMessage());
        }
    }

    public function getPendingPosts()
    {
        try {
            $posts = $this->handler->getPendingPosts();
            return ApiResponse::success($posts, 'Fetched pending posts');
        } catch (Exception $e) {
            return ApiResponse::error('Failed to fetch pending posts', $e->getMessage());
        }
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'post_title'  => 'required|string|max:255',
                'post_body'   => 'required|string',
                'cover_image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
                'post_status' => 'in:draft,pending',
            ]);

            $post = $this->handler->createPost(
                (int) Auth::id(),
                $request->post_title,
                $request->post_body,
                $request->file('cover_image'),
                $request->post_status
            );

            return ApiResponse::success($post, 'Blog post uploaded successful', 201);
        } catch (Exception $e) {
            return ApiResponse::error('Failed to upload post', $e->getMessage());
        }
    }

    public function getSinglePost($id)
    {
        try {
            $post = $this->handler->findById((int) $id);

            if (!$post) {
                return ApiResponse::error('Blog post not found', null, 404);
            }

            return ApiResponse::success($post, 'Post fetched success');
        } catch (Exception $e) {
            return ApiResponse::error('Failed to fetch post', $e->getMessage());
        }
    }

    public function destroy($id)
    {
        try {
            $post = $this->handler->findById((int) $id);

            if (!$post) {
                return response()->json(['message' => 'Blog post not found'], 404);
            }

            $this->handler->deletePost($post);

            return ApiResponse::success(null, 'Post deleted successfully');
        } catch (Exception $e) {
            return ApiResponse::error('Unable to delete the blog post', $e->getMessage(), 500);
        }
    }

    public function ownPosts()
    {
        try {
            $userId = (int) auth()->id();
            $posts = $this->handler->getOwnPosts($userId);
            return ApiResponse::success($posts, 'Fetched your posts');
        } catch (Exception $e) {
            return ApiResponse::error('Failed to fetch posts', $e->getMessage(), 500);
        }
    }

    public function ownDrafts()
    {
        try {
            $userId = (int) auth()->id();
            $posts = $this->handler->getOwnDrafts($userId);
            return ApiResponse::success($posts, 'Fetched your draft posts');
        } catch (Exception $e) {
            return ApiResponse::error('Failed to fetch drafts', $e->getMessage(), 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $post = $this->handler->findById((int) $id);

            if (!$post) {
                return ApiResponse::error('Cannot find the BlogPost.', null, 404);
            }

            if (auth()->id() !== $post->user_id) {
                return ApiResponse::error('Unauthorized', null, 403);
            }

            $validated = $request->validate([
                'post_title'  => 'required|string|max:255',
                'post_body'   => 'required|string',
                'cover_image' => 'nullable|string',
                'post_status' => 'required|in:draft,pending,published',
            ]);

            $updated = $this->handler->updatePost($post, $validated);

            return ApiResponse::success($updated, 'Blog post updated successfully');
        } catch (Exception $e) {
            return ApiResponse::error('Unable to update post', $e->getMessage(), 500);
        }
    }

    public function savePost($id)
    {
        try {
            $post = $this->handler->findById((int) $id);

            if (!$post) {
                return ApiResponse::error('Cannot find the post', null, 404);
            }

            $result = $this->handler->savePostAsPending($post);

            if ($result['status'] !== 'ok') {
                return ApiResponse::error('This post is not able to save', null, 400);
            }

            return ApiResponse::success($result['post'], 'Post saved successfully');
        } catch (Exception $e) {
            return ApiResponse::error('Unable to save post', $e->getMessage(), 500);
        }
    }

    public function approve($id)
    {
        try {
            $post = $this->handler->findById((int) $id);

            if (!$post) {
                return ApiResponse::error('Cannot find the post', null, 404);
            }

            $result = $this->handler->approvePost($post);

            if ($result['status'] !== 'ok') {
                return ApiResponse::error('This is not a pending post', null, 400);
            }

            return ApiResponse::success($result['post'], 'Post approved and published successfully.');
        } catch (Exception $e) {
            return ApiResponse::error('Unable to approve post', $e->getMessage(), 500);
        }
    }

    public function topLikedPosts()
    {
        try {
            $posts = $this->handler->getTopLiked(2);
            return ApiResponse::success($posts, 'Top liked posts fetched successfully');
        } catch (Exception $e) {
            return ApiResponse::error('Failed to fetch top liked posts', $e->getMessage(), 500);
        }
    }

    public function topCommentedPosts()
    {
        try {
            $posts = $this->handler->getTopCommented(2);
            return ApiResponse::success($posts, 'Top commented posts fetched successfully');
        } catch (Exception $e) {
            return ApiResponse::error('Failed to fetch top commented posts', $e->getMessage(), 500);
        }
    }

    public function searchPosts(Request $request)
    {
        try {
            $request->validate([
                'query' => 'required|string|max:255',
            ]);

            $posts = $this->handler->searchByTitle($request->input('query'));

            return ApiResponse::success($posts, 'Search results fetched successfully');
        } catch (Exception $e) {
            return ApiResponse::error('Failed to search posts', $e->getMessage(), 500);
        }
    }
}
