<?php

namespace C6Digital\PasswordlessLogin\Mail;

use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\URL;

class LoginLink extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(
        public User $user
    ) {
    }

    public function envelope()
    {
        return new Envelope(
            subject: 'Your Login Link',
        );
    }

    public function content()
    {
        return new Content(
            markdown: 'filament-passwordless-login::mail.login-link',
            with: [
                'url' => URL::temporarySignedRoute('filament.' . Filament::getCurrentPanel()->getId() . '.auth.link', now()->addMinutes(30), $this->user),
            ]
        );
    }
}
