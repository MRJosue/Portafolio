<?php

namespace App\Mail;

use App\Models\ChatSession;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ChatLeadReceived extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(
        public ChatSession $chatSession
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Nuevo contacto del chat: {$this->chatSession->name}",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mail.chat-lead-received',
        );
    }
}
