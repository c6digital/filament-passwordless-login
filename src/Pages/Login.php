<?php

namespace C6Digital\PasswordlessLogin\Pages;

use App\Models\User;
use C6Digital\PasswordlessLogin\Mail\LoginLink;
use Filament\Actions\Action;
use Filament\Facades\Filament;
use Filament\Forms\Components\Component;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Concerns\InteractsWithFormActions;
use Filament\Pages\SimplePage;
use Illuminate\Support\Facades\Mail;

class Login extends SimplePage
{
    use InteractsWithFormActions;

    protected static string $view = 'filament-passwordless-login::pages.login';

    public $email;

    public $sent = false;

    public function mount()
    {
        if (Filament::auth()->check()) {
            return redirect()->intended(Filament::getUrl());
        }

        $this->form->fill();
    }

    public function authenticate()
    {
        $data = $this->form->getState();
        $user = User::query()
            ->where('email', $data['email'])
            ->first();

        if ($user !== null) {
            Mail::to($data['email'])->queue(new LoginLink($user));
        }

        Notification::make()
            ->title('Sent!')
            ->body('If you have an account, you will receive an email with a link to login shortly.')
            ->success()
            ->send();

        $this->reset('email');
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                $this->getEmailFormComponent(),
            ]);
    }

    protected function getEmailFormComponent(): Component
    {
        return TextInput::make('email')
            ->label(__('filament-panels::pages/auth/login.form.email.label'))
            ->email()
            ->required()
            ->autocomplete()
            ->autofocus()
            ->extraInputAttributes(['tabindex' => 1]);
    }

    protected function getFormActions(): array
    {
        return [
            $this->getAuthenticateFormAction(),
        ];
    }

    protected function getAuthenticateFormAction(): Action
    {
        return Action::make('authenticate')
            ->label(__('filament-panels::pages/auth/login.form.actions.authenticate.label'))
            ->submit('authenticate');
    }

    protected function hasFullWidthFormActions(): bool
    {
        return true;
    }

    protected function messages()
    {
        return [
            'email.required' => 'Please enter your email address.',
            'email.email' => 'Please enter a valid email address.',
        ];
    }
}
