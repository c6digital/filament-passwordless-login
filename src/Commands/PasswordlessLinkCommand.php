<?php

namespace C6Digital\PasswordlessLogin\Commands;

use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\URL;

class PasswordlessLinkCommand extends Command
{
    protected $signature = 'passwordless:link {email?}';

    public function handle()
    {
        $email = $this->argument('email');
        $user = User::query()
            ->where('email', $email)
            ->first();

        if ($user === null) {
            $this->error('Could not find a user with this email address.');
            return self::FAILURE;
        }

        $this->info('Link:');
        $this->line(URL::temporarySignedRoute('filament.' . Filament::getCurrentPanel()->getId() . '.auth.link', now()->addMinutes(30), $user));
        $this->warn('The generated link will expire in 30 minutes.');

        return self::SUCCESS;
    }
}
