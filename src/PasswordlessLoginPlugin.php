<?php

namespace RyanChandler\PasswordlessLogin;

use Filament\Contracts\Plugin;
use Filament\Panel;
use Illuminate\Support\Facades\Route;
use RyanChandler\PasswordlessLogin\Http\Controllers\LoginLinkController;
use RyanChandler\PasswordlessLogin\Mail\LoginLink;
use RyanChandler\PasswordlessLogin\Pages\Login;

class PasswordlessLoginPlugin implements Plugin
{
    public function getId(): string
    {
        return 'filament-passwordless-login';
    }

    public function register(Panel $panel): void
    {
        $panel
            ->login(Login::class)
            ->routes(function () {
                Route::get('/auth/{user}/link', LoginLinkController::class)
                    ->middleware(['signed', 'guest'])
                    ->name('auth.link');
            });
    }

    public function boot(Panel $panel): void
    {
        //
    }

    public static function make(): static
    {
        return app(static::class);
    }

    public static function get(): static
    {
        /** @var static $plugin */
        $plugin = filament(app(static::class)->getId());

        return $plugin;
    }
}
