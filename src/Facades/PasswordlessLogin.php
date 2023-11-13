<?php

namespace RyanChandler\PasswordlessLogin\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \RyanChandler\PasswordlessLogin\PasswordlessLogin
 */
class PasswordlessLogin extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \RyanChandler\PasswordlessLogin\PasswordlessLogin::class;
    }
}
