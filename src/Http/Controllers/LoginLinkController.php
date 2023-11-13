<?php

namespace RyanChandler\PasswordlessLogin\Http\Controllers;

use App\Models\User;
use Filament\Http\Responses\Auth\Contracts\LoginResponse;
use Illuminate\Support\Facades\Auth;

class LoginLinkController
{
    public function __invoke(User $user)
    {
        Auth::login($user, remember: true);

        session()->regenerate();

        return app(LoginResponse::class);
    }
}
