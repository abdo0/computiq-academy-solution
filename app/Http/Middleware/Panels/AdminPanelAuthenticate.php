<?php

namespace App\Http\Middleware\Panels;

use Filament\Facades\Filament;
use Filament\Models\Contracts\FilamentUser;
use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Database\Eloquent\Model;

class AdminPanelAuthenticate extends Middleware
{
    /**
     * @param  array<string>  $guards
     */
    protected function authenticate($request, array $guards): void
    {
        $guard = Filament::auth();

        if (! $guard->check()) {
            $this->unauthenticated($request, $guards);

            return; /** @phpstan-ignore-line */
        }

        $this->auth->shouldUse(Filament::getAuthGuard());

        /** @var Model $user */
        $user = $guard->user();

        $panel = Filament::getCurrentOrDefaultPanel();

        // Custom authentication logic for Admin Panel
        abort_if(
            $user instanceof FilamentUser ?
                (! $user->canAccessPanel($panel)) :
                (config('app.env') !== 'local'),
            403,
        );

        // Additional admin-specific checks can be added here
        // For example: check if user has admin privileges
        if (method_exists($user, 'hasAdminAccess')) {
            abort_if(! $user->hasAdminAccess(), 403, 'You do not have access to the admin panel.');
        }
    }

    protected function redirectTo($request): ?string
    {
        return route('filament.admin.auth.login');
    }
}
