<?php

namespace C6Digital\PasswordlessLogin;

use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\URL;

class PasswordlessLogin
{
    public function generateLoginLink(User $user): string
    {
        return URL::temporarySignedRoute('filament.' . Filament::getCurrentPanel()->getId() . '.auth.link', now()->addMinutes(30), $user);
    }
}
