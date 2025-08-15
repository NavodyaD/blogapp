<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\BlogPost;
use Exception;

use App\Mail\PostApprovedMail;
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

            return response()->json($posts, 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Failed to fetch posts',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function allPosts()
    {
        try {

            $posts = BlogPost::with('user')->orderBy('created_at', 'desc')->get();
            return response()->json($posts);

        } catch (Exception $e) {
            return respose()->json([
                'message'=>'Failed to fetch posts',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function getPendingPosts()
    {
        try {

            $posts = BlogPost::with('user')
            ->where('post_status', 'pending')
            ->get();
            return response()->json($posts);

        } catch (Exception $e) {
            return response()->json([
                'message' => 'Failed to fetch pending posts',
                'error' => $e->getMessage()
            ], 500);
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

            return response()->json($post, 201);
        } catch (Exception $e) {
            return respose()->json([
                'message'=>'Failed to store blogpost',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function getSinglePost($id)
    {
        try {
            $post = BlogPost::find($id);

            if(!$post) {
                return response()->json(['message'=>'BlogPost not found'], 404);
            }

            return response()->json($post);

        } catch (Exception $e) {
            return response()->json([
                'message' => 'Failed to get post',
                'error' => $e->getMessage()
            ], 500);
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

            return response()->json(['message'=>'Blog post deleted successfully']);

        } catch (Exception $e) {
            return response()->json([
                'message' => 'Unable to delete the blogpost',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function ownPosts()
    {
        try {
            $user = auth()->user();

            $posts = BlogPost::with('user')->where('user_id', $user->id)->orderBy('created_at', 'desc')->get();

            return response()->json($posts);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Failed to fetch posts',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function ownDrafts()
    {
        try {
            $user = auth()->user();

            $posts = BlogPost::with('user')->where('user_id', $user->id)->where('post_status', 'draft')->get();

            return response()->json($posts);

        } catch (Exception $e) {
            return response()->json([
                'message' => 'Failed to fetch posts',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $post = BlogPost::find($id);

            if(!$post) {
                return response()->json(['message'=>'Cannot find the BlogPost.'], 404);
            }

            if(auth()->id() !== $post->user_id) {
                return response()->json(['message'=>'Unauthorized'], 403);
            }

            $validated = $request->validate([
                'post_title' => 'required|string|max:255',
                'post_body' => 'required|string',
                'cover_image' => 'nullable|string',
                'post_status' => 'required|in:draft,pending,published',
            ]);

            $post->update($validated);

            return response()->json(['message'=>'Blog post updated successful', 'post'=> $post]);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Unable to update post',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function savePost($id)
    {
        try {
            $post = BlogPost::find($id);

            if(!$post) {
                return response()->json(['message'=>'Cannot find the post'], 404);
            }

            if($post->post_status !== 'draft') {
                return response()->json(['message'=>'This post is not able to save']);
            }

            $post->post_status = 'pending';
            $post->save();

            return response()->json(['message'=>'Post saved successfully', 'post'=> $post]);

        } catch (Exception $e) {
            return response()->json([
                'message' => 'Unable to save post',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function approve($id)
    {
        try {
            $post = BlogPost::find($id);

            if(!$post) {
                return response()->json(['message'=>'Cannot find the post'], 404);
            }

            if($post->post_status !== 'pending') {
                return response()->json(['message'=> 'This is not a pending post']);
            }

            $post->post_status = 'published';
            $post->save();

            if ($post->user && $post->user->email) {
                Mail::to($post->user->email)->send(new PostApprovedMail($post));
            }

            return response()->json(['message'=>'Post approved and published successfully.', 'post' => $post]);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Unable to approve post',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function topLikedPosts()
    {
        return BlogPost::withCount('reactions')
            ->orderBy('reactions_count', 'desc')
            ->take(2)
            ->get(['id','post_title']);
    }

    public function topCommentedPosts()
    {
        return BlogPost::withCount('comments')
            ->orderBy('comments_count', 'desc')
            ->take(2)
            ->get(['id','post_title']);
    }

    public function searchPosts(Request $request)
    {
        $request->validate([
            'query' => 'required|string|max:255',
        ]);

        $query = $request->input('query');

        $posts = BlogPost::with('user', 'comments', 'reactions')
            ->withCount(['reactions', 'comments'])
            ->where('post_title', 'LIKE', "%{$query}%")
            ->get();

        return response()->json($posts);
    }
}
