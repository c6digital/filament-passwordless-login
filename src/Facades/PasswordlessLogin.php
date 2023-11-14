<?php

namespace C6Digital\PasswordlessLogin\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static string generateLoginLink(\App\Models\User $user)
 *
 * @see \C6Digital\PasswordlessLogin\PasswordlessLogin
 */
class PasswordlessLogin extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \C6Digital\PasswordlessLogin\PasswordlessLogin::class;
    }
}
