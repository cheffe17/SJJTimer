<?php

namespace App\Mail;

use App\Models\Invitation;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PartnerInvitation extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public User $inviter,
        public Invitation $invitation,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->inviter->name . ' möchte den SSJTimer mit dir teilen',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.partner-invitation',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
