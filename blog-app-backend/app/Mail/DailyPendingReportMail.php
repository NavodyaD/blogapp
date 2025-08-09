<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use App\Models\BlogPost;

class DailyPendingReportMail extends Mailable
{
    use Queueable, SerializesModels;

    public $pendingCount;
    //public $pendingTitles;

    public function __construct()
    {
        $this->pendingCount = BlogPost::where('post_status', 'pending')->count();
        //$this->pendingCount = $pendingPosts->count();
        //$this->pendingTitles = $pendingPosts->pluck('title');
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'There are pending posts to approve',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.pending-posts',
            with: [
                'pendingCount' => $this->pendingCount,
                //'pendingTitles' => $this->pendingTitles,
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
