<?php

namespace C6Digital\PasswordlessLogin\Actions;

use App\Models\User;
use C6Digital\PasswordlessLogin\PasswordlessLogin;
use Filament\Forms\Components\TextInput;
use Filament\Support\Enums\Alignment;
use Filament\Tables\Actions\Action;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Js;

class LoginLinkAction extends Action
{
    public static function make(?string $name = 'login-link'): static
    {
        return parent::make($name);
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this
            ->icon('heroicon-o-arrow-right-on-rectangle')
            ->color('info')
            ->modalWidth('lg')
            ->form([
                TextInput::make('link')
                    ->readOnly()
                    ->afterStateHydrated(function (TextInput $component, User $user, PasswordlessLogin $passwordlessLogin) {
                        $component->state($passwordlessLogin->generateLoginLink($user));
                    }),
            ])
            ->modalFooterActionsAlignment(Alignment::Center)
            ->modalSubmitAction(
                fn (User $user) => $this
                    ->makeModalAction('Copy')
                    ->close(false)
                    ->color('info')
                    ->extraAttributes(fn (PasswordlessLogin $passwordlessLogin) => [
                        'x-on:click' => new HtmlString('window.navigator.clipboard.writeText(' . Js::from($passwordlessLogin->generateLoginLink($user)) . ').then(() => { $el.innerText = \'Copied!\'; setTimeout(() => $el.innerText = \'Copy\', 2000) })'),
                    ])
            );
    }
}
