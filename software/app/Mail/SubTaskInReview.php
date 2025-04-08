<?php

namespace App\Mail;

use App\Models\SubTask;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SubTaskInReview extends Mailable
{
    use Queueable, SerializesModels;

    public SubTask $subTask;

    public User $user;

    /**
     * Create a new message instance.
     */
    public function __construct(SubTask $subTask, User $user)
    {
        $this->subTask = $subTask;
        $this->user = $user;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Sub-Task Status Updated to In Review',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'mail.sub-task-in-review',
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
