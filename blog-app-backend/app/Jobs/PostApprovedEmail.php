<?php

namespace App\Jobs;

use App\Models\BlogPost;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Mail;
use App\Mail\PostApprovedMail;

use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class PostApprovedEmail implements ShouldQueue
{
    use Queueable, Dispatchable, InteractsWithQueue, SerializesModels;

    public $post;
    public function __construct(BlogPost $post)
    {
        $this->post = $post;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        if ($this->post->user && $this->post->user->email) {
            Mail::to($this->post->user->email)
                ->send(new PostApprovedMail($this->post));
        }
    }
}

