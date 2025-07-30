<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class BlogPost extends Model
{
    protected $fillable = [
        'user_id',
        'post_title',
        'post_body',
        'cover_image',
        'post_slug',
        'post_status',
        'published_at',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($post) {
            $post->post_slug = Str::of($post->post_title)->slug('-');
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
