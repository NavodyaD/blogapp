<?php

namespace App\Handlers;

use App\Models\BlogPost;
use App\Jobs\PostApprovedEmail;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;

class BlogPostHandler
{
    public function getPublishedPaginated(int $perPage = 6): LengthAwarePaginator
    {
        return BlogPost::with('user')
            ->withCount(['reactions', 'comments'])
            ->where('post_status', 'published')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    public function getAllPosts(): Collection
    {
        return BlogPost::with('user')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function getPendingPosts(): Collection
    {
        return BlogPost::with('user')
            ->where('post_status', 'pending')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function findById(int $id): ?BlogPost
    {
        return BlogPost::find($id);
    }

    public function createPost(
        int $userId,
        string $title,
        string $body,
        ?UploadedFile $coverImage,
        ?string $status
    ): BlogPost {
        $imagePath = null;

        if ($coverImage) {
            $imagePath = $coverImage->store('cover_images', 'public');
        }

        return BlogPost::create([
            'user_id'     => $userId,
            'post_title'  => $title,
            'post_body'   => $body,
            'cover_image' => $imagePath,
            'post_status' => $status ?? 'draft',
        ]);
    }

    public function deletePost(BlogPost $post): void
    {
        $post->delete();
    }

    public function getOwnPosts(int $userId): Collection
    {
        return BlogPost::with('user')
            ->where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function getOwnDrafts(int $userId): Collection
    {
        return BlogPost::with('user')
            ->where('user_id', $userId)
            ->where('post_status', 'draft')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function updatePost(BlogPost $post, array $validated): BlogPost
    {
        $post->update($validated);
        return $post->refresh();
    }

    public function savePostAsPending(BlogPost $post): array
    {
        if ($post->post_status !== 'draft') {
            return ['status' => 'not_draft'];
        }

        $post->post_status = 'pending';
        $post->save();

        return ['status' => 'ok', 'post' => $post];
    }

    public function approvePost(BlogPost $post): array
    {
        if ($post->post_status !== 'pending') {
            return ['status' => 'not_pending'];
        }

        $post->post_status = 'published';
        $post->save();

        PostApprovedEmail::dispatch($post);

        return ['status' => 'ok', 'post' => $post];
    }

    public function getTopLiked(int $limit = 2): Collection
    {
        return BlogPost::withCount('reactions')
            ->orderBy('reactions_count', 'desc')
            ->take($limit)
            ->get(['id', 'post_title']);
    }

    public function getTopCommented(int $limit = 2): Collection
    {
        return BlogPost::withCount('comments')
            ->orderBy('comments_count', 'desc')
            ->take($limit)
            ->get(['id', 'post_title']);
    }

    public function searchByTitle(string $query): Collection
    {
        return BlogPost::with('user', 'comments', 'reactions')
            ->withCount(['reactions', 'comments'])
            ->where('post_title', 'LIKE', "%{$query}%")
            ->get();
    }
}
