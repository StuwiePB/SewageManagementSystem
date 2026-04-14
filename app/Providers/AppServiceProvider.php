<?php

namespace App\Providers;

use Carbon\CarbonImmutable;
<<<<<<< HEAD
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
=======
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
>>>>>>> 3wayfusionn-(use-this)
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
<<<<<<< HEAD
        $this->configureDefaults();
    }

=======
        Blade::anonymousComponentPath(resource_path('views/layouts'), 'layouts');
        $this->configureDefaults();

        Event::listen(\Illuminate\Auth\Events\Logout::class, function ($event) {
            if ($event->user && $event->user->hasRole('customer')) {
                \App\Models\SupportMessage::where('user_id', $event->user->id)->delete();
            }
        });
    }

    /**
     * Configure default behaviors for production-ready applications.
     */
>>>>>>> 3wayfusionn-(use-this)
    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        Password::defaults(fn (): ?Password => app()->isProduction()
            ? Password::min(12)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
                ->uncompromised()
            : null
        );
    }
}
