<?php

namespace RyanChandler\PasswordlessLogin\Commands;

use Illuminate\Console\Command;

class PasswordlessLoginCommand extends Command
{
    public $signature = 'filament-passwordless-login';

    public $description = 'My command';

    public function handle(): int
    {
        $this->comment('All done');

        return self::SUCCESS;
    }
}
