<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\BlogPost;

class BlogPostController extends Controller
{
    public function index()
    {
        $posts = BlogPost::with('user')->orderBy('created_at', 'desc')->get();

        return response()->json($posts);
    }

    public function store(Request $request)
    {
        $request->validate([
            'post_title' => 'required|string|max:255',
            'post_body' => 'required|string',
            'cover_image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'post_status' => 'in:draft,pending',
        ]);

        $imagePath = null;

        if ($request->hasFile('cover_image')) {
            // Store the image in storage/app/public/cover_images
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
    }

    public function getSinglePost($id) {
        $post = BlogPost::find($id);

        if(!$post) {
            return response()->json(['message'=>'BlogPost not found'], 404);
        }

        return response()->json($post);
    }

    public function destroy($id)
    {
        $post = BlogPost::find($id);

        if(!$post) {
            return response()->json(['message'=>'Blog post not found'], 404);
        }

        $post->delete();

        return response()->json(['message'=>'Blog post deleted successfully']);
    }

    public function ownPosts()
    {
        $user = auth()->user();

        $posts = BlogPost::with('user')->where('user_id', $user->id)->orderBy('created_at', 'desc')->get();

        return response()->json($posts);
    }

    public function update(Request $request, $id)
    {
        $post = BlogPost::find($id);

        if(!$post) {
            return response()->json(['message'=>'Cannot find the BlogPost.'], 404);
        }

        if(auth()->id() !== $post->user_id) {
            return response()->json(['message'=>'Unauthorized'], 403);
        }

        $post->update();

        return response()->json(['message'=>'Blog post updated successful', 'post'=> $post]);
    }

    public function savePost($id)
    {
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
    }

    public function approve($id)
    {
        $post = BlogPost::find($id);

        if(!$post) {
            return response()->json(['message'=>'Cannot find the post'], 404);
        }

        if($post->post_status !== 'pending') {
            return response()->json(['message'=> 'This is not a pending post']);
        }

        $post->post_status = 'published';
        $post->save();

        return response()->json(['message'=>'Post approved and published successfully.', 'post' => $post]);
    }
}
