<?php

namespace C6Digital\PasswordlessLogin\Commands;

use App\Models\User;
use C6Digital\PasswordlessLogin\PasswordlessLogin;
use Illuminate\Console\Command;

class PasswordlessLinkCommand extends Command
{
    protected $signature = 'passwordless:link {email?}';

    public function handle(PasswordlessLogin $passwordlessLogin)
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
        $this->line($passwordlessLogin->generateLoginLink($user));
        $this->warn('The generated link will expire in 30 minutes.');

        return self::SUCCESS;
    }
}
