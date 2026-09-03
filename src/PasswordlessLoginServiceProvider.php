<?php

namespace C6Digital\PasswordlessLogin;

use C6Digital\PasswordlessLogin\Facades\PasswordlessLogin;
use C6Digital\PasswordlessLogin\Testing\TestsPasswordlessLogin;
use Filament\Support\Facades\FilamentIcon;
use Livewire\Features\SupportTesting\Testable;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class PasswordlessLoginServiceProvider extends PackageServiceProvider
{
    public static string $name = 'filament-passwordless-login';

    public static string $viewNamespace = 'filament-passwordless-login';

    public function configurePackage(Package $package): void
    {
        $package->name(static::$name);

        if (file_exists($package->basePath('/../resources/lang'))) {
            $package->hasTranslations();
        }

        if (file_exists($package->basePath('/../resources/views'))) {
            $package->hasViews(static::$viewNamespace);
        }

        $package->hasCommands([
            Commands\PasswordlessLinkCommand::class,
        ]);
    }

    public function packageRegistered(): void
    {
        $this->app->singleton(PasswordlessLogin::class);
    }

    public function packageBooted(): void
    {
        // Icon Registration
        FilamentIcon::register($this->getIcons());

        // Testing
        Testable::mixin(new TestsPasswordlessLogin);
    }

    /**
     * @return array<class-string>
     */
    protected function getCommands(): array
    {
        return [];
    }

    /**
     * @return array<string>
     */
    protected function getIcons(): array
    {
        return [];
    }

    /**
     * @return array<string>
     */
    protected function getRoutes(): array
    {
        return [];
    }

    /**
     * @return array<string>
     */
    protected function getMigrations(): array
    {
        return [];
    }
}
