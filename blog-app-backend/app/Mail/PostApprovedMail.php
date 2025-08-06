<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

use App\Models\BlogPost;

class PostApprovedMail extends Mailable
{
    use Queueable, SerializesModels;

    public $post;

    public function __construct(BlogPost $post)
    {
        $this->post = $post;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Your Blog Post Has Been Approved',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.post-approved',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
