<?php

namespace App\Mail;

use App\Models\AccountInvitation;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AccountInvitationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public AccountInvitation $invitation) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Te invitaron a colaborar en {$this->invitation->account->name}",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mail.account-invitation',
            with: [
                'accountName' => $this->invitation->account->name,
                'role' => $this->invitation->role->getLabel(),
                'url' => $this->invitation->acceptanceUrl(),
            ],
        );
    }
}
