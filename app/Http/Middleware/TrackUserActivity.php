<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class TrackUserActivity
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Only track activity for authenticated users
        if (Auth::check()) {
            $user = Auth::user();

            // Only update last_activity_at if the column exists
            if ($user->getConnection()->getSchemaBuilder()->hasColumn($user->getTable(), 'last_activity_at')) {
                $user->withActivityLogging()->update(['last_activity_at' => now()]);
            }
        }

        return $response;
    }
}
