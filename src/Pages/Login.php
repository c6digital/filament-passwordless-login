<?php

namespace C6Digital\PasswordlessLogin\Pages;

use App\Models\User;
use C6Digital\PasswordlessLogin\Mail\LoginLink;
use C6Digital\PasswordlessLogin\PasswordlessLoginPlugin;
use DanHarrin\LivewireRateLimiting\Exceptions\TooManyRequestsException;
use DanHarrin\LivewireRateLimiting\WithRateLimiting;
use Filament\Actions\Action;
use Filament\Auth\Http\Responses\Contracts\LoginResponse;
use Filament\Facades\Filament;
use Filament\Forms\Components\TextInput;
use Filament\Models\Contracts\FilamentUser;
use Filament\Notifications\Notification;
use Filament\Pages\Concerns\InteractsWithFormActions;
use Filament\Pages\SimplePage;
use Filament\Schemas\Components\Actions as ActionsComponent;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Concerns\RestrictsFileUploadsToSchemaComponents;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\ValidationException;

/**
 * @property-read Schema $form
 */
class Login extends SimplePage
{
    use InteractsWithFormActions;

    // This page is reachable unauthenticated, so — as Filament does on its own auth
    // pages — refuse temporary file uploads for anything that is not a file upload
    // component in one of this page's schemas.
    use RestrictsFileUploadsToSchemaComponents;
    use WithRateLimiting;

    protected string $view = 'filament-passwordless-login::pages.login';

    /**
     * @var array<string, mixed> | null
     */
    public ?array $data = [];

    public bool $sent = false;

    public function mount(): void
    {
        if (Filament::auth()->check()) {
            redirect()->intended(Filament::getUrl());

            return;
        }

        $this->form->fill();
    }

    public function authenticate(): ?LoginResponse
    {
        try {
            $this->rateLimit(5);
        } catch (TooManyRequestsException $e) {
            Notification::make()
                ->title(__('filament-panels::auth/pages/login.notifications.throttled.title', [
                    'seconds' => $e->secondsUntilAvailable,
                    'minutes' => ceil($e->secondsUntilAvailable / 60),
                ]))
                ->body(array_key_exists('body', __('filament-panels::auth/pages/login.notifications.throttled') ?: []) ? __('filament-panels::auth/pages/login.notifications.throttled.body', [
                    'seconds' => $e->secondsUntilAvailable,
                    'minutes' => ceil($e->secondsUntilAvailable / 60),
                ]) : null)
                ->danger()
                ->send();

            return null;
        }

        $data = $this->form->getState();

        if (PasswordlessLoginPlugin::get()->allowsPasswordInLocalEnvironment() && ! blank($data['password'])) {
            return $this->authenticateWithPassword($data);
        }

        $user = User::query()
            ->where('email', $data['email'])
            ->first();

        if ($user !== null) {
            Mail::to($data['email'])->queue(new LoginLink($user));
        }

        $this->sent = true;

        $this->data['email'] = null;

        return null;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected function authenticateWithPassword(array $data): ?LoginResponse
    {
        if (! PasswordlessLoginPlugin::get()->allowsPasswordInLocalEnvironment()) {
            return null;
        }

        if (! Filament::auth()->attempt($this->getCredentialsFromFormData($data))) {
            $this->throwFailureValidationException();
        }

        $user = Filament::auth()->user();

        if (
            ($user instanceof FilamentUser) &&
            (! $user->canAccessPanel(Filament::getCurrentOrDefaultPanel()))
        ) {
            Filament::auth()->logout();

            $this->throwFailureValidationException();
        }

        Session::regenerate();

        return app(LoginResponse::class);
    }

    protected function throwFailureValidationException(): never
    {
        throw ValidationException::withMessages([
            'data.email' => __('filament-panels::auth/pages/login.messages.failed'),
        ]);
    }

    public function defaultForm(Schema $schema): Schema
    {
        return $schema
            ->statePath('data');
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                $this->getEmailFormComponent(),
                $this->getPasswordFormComponent(),
            ]);
    }

    public function content(Schema $schema): Schema
    {
        return $schema
            ->components([
                $this->getFormContentComponent(),
            ]);
    }

    public function getFormContentComponent(): Component
    {
        return Form::make([EmbeddedSchema::make('form')])
            ->id('form')
            ->livewireSubmitHandler('authenticate')
            ->footer([
                ActionsComponent::make($this->getCachedFormActions())
                    ->alignment($this->getFormActionsAlignment())
                    ->fullWidth($this->hasFullWidthFormActions())
                    ->key('form-actions'),
            ]);
    }

    protected function getEmailFormComponent(): Component
    {
        return TextInput::make('email')
            ->label(__('filament-panels::auth/pages/login.form.email.label'))
            ->email()
            ->required()
            ->autocomplete()
            ->autofocus()
            ->extraInputAttributes(['tabindex' => 1]);
    }

    protected function getPasswordFormComponent(): Component
    {
        return TextInput::make('password')
            ->visible(fn (): bool => PasswordlessLoginPlugin::get()->allowsPasswordInLocalEnvironment())
            ->label(__('filament-panels::auth/pages/login.form.password.label'))
            ->helperText('You are currently in a local environment, so you can use a password instead of a login link.')
            ->password()
            ->revealable(filament()->arePasswordsRevealable())
            ->autocomplete('current-password')
            ->extraInputAttributes(['tabindex' => 2]);
    }

    /**
     * @return array<Action>
     */
    protected function getFormActions(): array
    {
        return [
            $this->getAuthenticateFormAction(),
        ];
    }

    protected function getAuthenticateFormAction(): Action
    {
        return Action::make('authenticate')
            ->label(__('filament-panels::auth/pages/login.form.actions.authenticate.label'))
            ->submit('authenticate');
    }

    protected function hasFullWidthFormActions(): bool
    {
        return true;
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function getCredentialsFromFormData(array $data): array
    {
        return [
            'email' => $data['email'],
            'password' => $data['password'],
        ];
    }

    /**
     * @return array<string, string>
     */
    protected function messages(): array
    {
        return [
            'data.email.required' => 'Please enter your email address.',
            'data.email.email' => 'Please enter a valid email address.',
        ];
    }
}
