<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PostComment extends Model
{
    protected $fillable = ['user_id', 'blog_post_id', 'comment_text'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function blogpost() 
    {
        return $this->belongsTo(blogpost::class);
    }
}
