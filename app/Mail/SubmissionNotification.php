<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use App\Models\Submission;

class SubmissionNotification extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $subjectLine;
    public $greeting;
    public $lines;
    public $submission;

    /**
     * Create a new message instance.
     */
    public function __construct($subject, $greeting, $lines, Submission $submission)
    {
        $this->subjectLine = $subject;
        $this->greeting = $greeting;
        $this->lines = $lines;
        $this->submission = $submission;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->subjectLine,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.submission_notification',
            with: [
                'greeting' => $this->greeting,
                'lines' => $this->lines,
                'submission' => $this->submission
            ]
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
