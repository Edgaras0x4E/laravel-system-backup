<?php

namespace Edgaras\SystemBackup\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class BackupMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $subjectText;
    public string $bodyText;
    public string $link;

    public function __construct(string $subjectText, string $bodyText, string $link)
    {
        $this->subjectText = $subjectText;
        $this->bodyText = $bodyText;
        $this->link = $link;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            from: new Address(
                config('backup.email.from.address'),
                config('backup.email.from.name')
            ),
            subject: $this->subjectText,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'systembackup::backup_mail',
            with: [
                'bodyText' => $this->bodyText,
                'link' => $this->link
            ]
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
