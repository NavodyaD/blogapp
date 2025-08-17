<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\BlogPost;
use Exception;

use App\Mail\PostApprovedMail;
use App\Jobs\PostApprovedEmail;
use App\Helpers\ApiResponse;
use Illuminate\Support\Facades\Mail;

class BlogPostController extends Controller
{
    public function index()
    {
        try {
            $posts = BlogPost::with('user')
                ->withCount(['reactions', 'comments'])
                ->where('post_status', 'published')
                ->orderBy('created_at', 'desc')
                ->paginate(6);

            return ApiResponse::success($posts, 'Fetched published posts');
        } catch (Exception $e) {
            return ApiResponse::error('Failed to fetch posts', $e->getMessage());
        }
    }

    public function allPosts()
    {
        try {

            $posts = BlogPost::with('user')->orderBy('created_at', 'desc')->get();

            return ApiResponse::success($posts, 'Fetched all posts');

        } catch (Exception $e) {
            return ApiResponse::error('Failed to fetch posts', $e->getMessage());
        }
    }

    public function getPendingPosts()
    {
        try {

            $posts = BlogPost::with('user')
            ->where('post_status', 'pending')
            ->get();

            return ApiResponse::success($posts, 'Fetched pending posts');

        } catch (Exception $e) {
            return ApiResponse::error('Failed to fetch pending posts', $e->getMessage());
        }
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'post_title' => 'required|string|max:255',
                'post_body' => 'required|string',
                'cover_image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
                'post_status' => 'in:draft,pending',
            ]);

            $imagePath = null;

            if ($request->hasFile('cover_image')) {
                // storage/app/public/cover_images
                $imagePath = $request->file('cover_image')->store('cover_images', 'public');
            }

            $post = BlogPost::create([
                'user_id' => Auth::id(),
                'post_title' => $request->post_title,
                'post_body' => $request->post_body,
                'cover_image' => $imagePath,
                'post_status' => $request->post_status ?? 'draft',
            ]);

            return ApiResponse::success($post, 'Blog post uploaded successful', 201);

        } catch (Exception $e) {

            return ApiResponse::error('Failed to upload post', $e->getMessage());
        }
    }

    public function getSinglePost($id)
    {
        try {
            $post = BlogPost::find($id);

            if(!$post) {
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

            $post = BlogPost::find($id);

            if(!$post) {
                return response()->json(['message'=>'Blog post not found'], 404);
            }

            $post->delete();

            return ApiResponse::success(null, 'Post deleted successfully');

        } catch (Exception $e) {
            return ApiResponse::error('Unable to delete the blog post', $e->getMessage(), 500);
        }
    }

    public function ownPosts()
    {
        try {
            $user = auth()->user();

            $posts = BlogPost::with('user')->where('user_id', $user->id)->orderBy('created_at', 'desc')->get();

            return ApiResponse::success($posts, 'Fetched your posts');
        } catch (Exception $e) {
            return ApiResponse::error('Failed to fetch posts', $e->getMessage(), 500);
        }
    }

    public function ownDrafts()
    {
        try {
            $user = auth()->user();

            $posts = BlogPost::with('user')->where('user_id', $user->id)->where('post_status', 'draft')->get();

            return ApiResponse::success($posts, 'Fetched your draft posts');

        } catch (Exception $e) {
            return ApiResponse::error('Failed to fetch drafts', $e->getMessage(), 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $post = BlogPost::find($id);

            if(!$post) {
                return ApiResponse::error('Cannot find the BlogPost.', null, 404);
            }

            if(auth()->id() !== $post->user_id) {
                return ApiResponse::error('Unauthorized', null, 403);
            }

            $validated = $request->validate([
                'post_title' => 'required|string|max:255',
                'post_body' => 'required|string',
                'cover_image' => 'nullable|string',
                'post_status' => 'required|in:draft,pending,published',
            ]);

            $post->update($validated);

            return ApiResponse::success($post, 'Blog post updated successfully');
        } catch (Exception $e) {
            return ApiResponse::error('Unable to update post', $e->getMessage(), 500);
        }
    }

    public function savePost($id)
    {
        try {
            $post = BlogPost::find($id);

            if(!$post) {
                return ApiResponse::error('Cannot find the post', null, 404);
            }

            if($post->post_status !== 'draft') {
                return ApiResponse::error('This post is not able to save', null, 400);
            }

            $post->post_status = 'pending';
            $post->save();

            return ApiResponse::success($post, 'Post saved successfully');

        } catch (Exception $e) {
            return ApiResponse::error('Unable to save post', $e->getMessage(), 500);
        }
    }

    public function approve($id)
    {
        try {
            $post = BlogPost::find($id);

            if(!$post) {
                return ApiResponse::error('Cannot find the post', null, 404);
            }

            if($post->post_status !== 'pending') {
                return ApiResponse::error('This is not a pending post', null, 400);
            }

            $post->post_status = 'published';
            $post->save();

            PostApprovedEmail::dispatch($post);

            return ApiResponse::success($post, 'Post approved and published successfully.');
        } catch (Exception $e) {
            return ApiResponse::error('Unable to approve post', $e->getMessage(), 500);
        }
    }

    public function topLikedPosts()
    {
        try {
            $posts = BlogPost::withCount('reactions')
                ->orderBy('reactions_count', 'desc')
                ->take(2)
                ->get(['id', 'post_title']);

            return ApiResponse::success($posts, 'Top liked posts fetched successfully');
        } catch (Exception $e) {
            return ApiResponse::error('Failed to fetch top liked posts', $e->getMessage(), 500);
        }
    }

    public function topCommentedPosts()
    {
        try {
            $posts = BlogPost::withCount('comments')
                ->orderBy('comments_count', 'desc')
                ->take(2)
                ->get(['id', 'post_title']);

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

            $query = $request->input('query');

            $posts = BlogPost::with('user', 'comments', 'reactions')
                ->withCount(['reactions', 'comments'])
                ->where('post_title', 'LIKE', "%{$query}%")
                ->get();

            return ApiResponse::success($posts, 'Search results fetched successfully');
        } catch (Exception $e) {
            return ApiResponse::error('Failed to search posts', $e->getMessage(), 500);
        }
    }
}
