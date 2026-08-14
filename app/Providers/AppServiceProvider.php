<?php

namespace App\Providers;

use App\Models\LeadFollowUp;
use App\Models\User;
use App\View\Composers\OrgShellComposer;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Gate::define('manageFollowUp', function (User $user, LeadFollowUp $followUp) {
            return $user->isOrganizationAdmin()
                && $user->organization_id === $followUp->organization_id;
        });

        View::composer(['layouts.org', 'components.org.topbar', 'components.org.sidebar'], OrgShellComposer::class);

        $seconds = config('connectpulse.message_rate_limit_seconds', 2);

        RateLimiter::for('whatsapp-messages', function () use ($seconds) {
            return Limit::perMinute((int) max(1, floor(60 / $seconds)));
        });
    }
}
