<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\App;

class ReportExported extends Mailable
{
    use Queueable, SerializesModels;

    private string $filename;

    /**
     * Create a new message instance.
     */
    public function __construct(public User $user, public string $path)
    {
        $this->filename = basename($path);
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        App::setLocale($this->user->locale);
        return new Envelope(
            subject: __('email.report_exported.subject'),
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        App::setLocale($this->user->locale);
        return new Content(
            markdown: 'emails.report_exported',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, Attachment>
     */
    public function attachments(): array
    {
        return [
            Attachment::fromPath($this->path)
                ->as($this->filename)
                ->withMime('text/csv'),
        ];
    }
}
