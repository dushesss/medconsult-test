<?php

declare(strict_types=1);

namespace App\Providers;

use App\Models\UserFile;
use App\Policies\UserFilePolicy;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

final class AppServiceProvider extends ServiceProvider
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
        Gate::policy(UserFile::class, UserFilePolicy::class);

        RateLimiter::for('api', function (Request $request): Limit {
            $key = $request->user() !== null
                ? 'api:user:' . (string) $request->user()->getAuthIdentifier()
                : 'api:ip:' . $request->ip();

            return Limit::perMinute(60)->by($key);
        });

        RateLimiter::for('login', function (Request $request): Limit {
            return Limit::perMinute(5)->by('login:' . $request->ip());
        });
    }
}
