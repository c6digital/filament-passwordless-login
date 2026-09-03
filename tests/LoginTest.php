<?php

use App\Models\User;
use C6Digital\PasswordlessLogin\Mail\LoginLink;
use C6Digital\PasswordlessLogin\Pages\Login;
use C6Digital\PasswordlessLogin\PasswordlessLoginPlugin;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Livewire\Livewire;

it('registers the passwordless login page on the panel', function () {
    expect(Filament::getPanel('admin')->getLoginRouteAction())->toBe(Login::class);
});

it('renders the login page', function () {
    $this->get(Filament::getPanel('admin')->getLoginUrl())
        ->assertSuccessful()
        ->assertSeeLivewire(Login::class);
});

it('renders the email field and hides the password field by default', function () {
    Livewire::test(Login::class)
        ->assertSchemaStateSet(['email' => null], 'form')
        ->assertFormFieldExists('email', 'form')
        ->assertFormFieldHidden('password', 'form');
});

it('queues a login link for a known email address', function () {
    Mail::fake();

    $user = User::create([
        'name' => 'Ada',
        'email' => 'ada@example.com',
        'password' => 'password',
    ]);

    Livewire::test(Login::class)
        ->fillForm(['email' => 'ada@example.com'], 'form')
        ->call('authenticate')
        ->assertHasNoFormErrors()
        ->assertSet('sent', true)
        ->assertSee('you will receive an email with a link to login');

    Mail::assertQueued(
        LoginLink::class,
        fn (LoginLink $mail) => $mail->hasTo('ada@example.com') && $mail->user->is($user),
    );
});

it('does not reveal whether an email address is registered', function () {
    Mail::fake();

    Livewire::test(Login::class)
        ->fillForm(['email' => 'nobody@example.com'], 'form')
        ->call('authenticate')
        ->assertHasNoFormErrors()
        ->assertSet('sent', true);

    Mail::assertNothingQueued();
});

it('validates the email address', function () {
    Mail::fake();

    Livewire::test(Login::class)
        ->fillForm(['email' => 'not-an-email'], 'form')
        ->call('authenticate')
        ->assertHasFormErrors(['email' => 'email'], 'form')
        ->assertSet('sent', false);

    Mail::assertNothingQueued();
});

it('authenticates a user visiting a signed login link', function () {
    $user = User::create([
        'name' => 'Ada',
        'email' => 'ada@example.com',
        'password' => 'password',
    ]);

    $link = URL::temporarySignedRoute('filament.admin.auth.link', now()->addMinutes(30), $user);

    $this->get($link)->assertRedirect(Filament::getPanel('admin')->getUrl());

    $this->assertAuthenticatedAs($user);
});

it('rejects a login link with a tampered signature', function () {
    $user = User::create([
        'name' => 'Ada',
        'email' => 'ada@example.com',
        'password' => 'password',
    ]);

    $link = URL::temporarySignedRoute('filament.admin.auth.link', now()->addMinutes(30), $user);

    $this->get($link . 'tampered')->assertForbidden();

    $this->assertGuest();
});

it('rejects an expired login link', function () {
    $user = User::create([
        'name' => 'Ada',
        'email' => 'ada@example.com',
        'password' => 'password',
    ]);

    $link = URL::temporarySignedRoute('filament.admin.auth.link', now()->addMinutes(30), $user);

    $this->travel(31)->minutes();

    $this->get($link)->assertForbidden();

    $this->assertGuest();
});

describe('password in local environment', function () {
    beforeEach(function () {
        // `allowsPasswordInLocalEnvironment()` gates on `App::isLocal()`, so the tests in
        // this block have to run outside the `testing` environment. That also disables
        // Filament's `fillForm()` test helper, which no-ops unless the app is running
        // unit tests — so these tests set the form state directly instead.
        app()['env'] = 'local';

        PasswordlessLoginPlugin::get()->allowPasswordInLocalEnvironment();
    });

    it('shows the password field', function () {
        Livewire::test(Login::class)
            ->assertFormFieldVisible('password', 'form');
    });

    it('authenticates with a valid password', function () {
        $user = User::create([
            'name' => 'Ada',
            'email' => 'ada@example.com',
            'password' => 'password',
        ]);

        Livewire::test(Login::class)
            ->set('data.email', 'ada@example.com')
            ->set('data.password', 'password')
            ->call('authenticate')
            ->assertHasNoErrors();

        $this->assertAuthenticatedAs($user);
    });

    it('rejects an invalid password', function () {
        Mail::fake();

        User::create([
            'name' => 'Ada',
            'email' => 'ada@example.com',
            'password' => 'password',
        ]);

        Livewire::test(Login::class)
            ->set('data.email', 'ada@example.com')
            ->set('data.password', 'wrong-password')
            ->call('authenticate')
            ->assertHasErrors('data.email');

        $this->assertGuest();
        Mail::assertNothingQueued();
    });
});
