<?php

namespace App\Listeners;

use App\Models\ActivityLog;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Events\Dispatcher;
use Illuminate\Support\Facades\Auth;

class LogAuthEvents implements ShouldQueue
{
    /**
     * Handle user login events.
     */
    public function handleLogin(Login $event)
    {
        if ($event->guard == 'web') {
            ActivityLog::create([
                'user_id' => $event->user->id,
                'action' => 'login',
                'description' => __('User logged in'),
                'properties' => ['guard' => $event->guard],
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);
        }
    }

    /**
     * Handle user logout events.
     */
    public function handleLogout(Logout $event)
    {
        if ($event->user && $event->guard == 'web') {
            ActivityLog::create([
                'user_id' => $event->user->id,
                'action' => 'logout',
                'description' => __('User logged out'),
                'properties' => ['guard' => $event->guard],
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);
        }
    }

    /**
     * Handle failed login attempts.
     */
    public function handleFailedLogin(Failed $event)
    {
        // disable auth events
        return;

        ActivityLog::create([
            'user_id' => null,
            'action' => 'login_failed',
            'description' => __('Failed login attempt'),
            'properties' => [
                'email' => $event->credentials['email'] ?? 'unknown',
                'guard' => $event->guard,
            ],
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }

    /**
     * Register the listeners for the subscriber.
     *
     * @param  Dispatcher  $events
     * @return void
     */
    public function subscribe($events)
    {
        $events->listen(
            Login::class,
            [LogAuthEvents::class, 'handleLogin']
        );

        $events->listen(
            Logout::class,
            [LogAuthEvents::class, 'handleLogout']
        );

        $events->listen(
            Failed::class,
            [LogAuthEvents::class, 'handleFailedLogin']
        );
    }
}
