<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class VerifyTurnstile
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Skip verification in testing environment
        if (app()->environment('testing')) {
            return $next($request);
        }

        $token = $request->input('cf-turnstile-response')
            ?? $request->header('X-Turnstile-Token');

        if (empty($token)) {
            return response()->json([
                'message' => __('CAPTCHA verification is required.'),
                'errors' => [
                    'turnstile' => [__('Please complete the CAPTCHA verification.')],
                ],
            ], 422);
        }

        $secretKey = config('services.turnstile.secret_key');

        if (empty($secretKey)) {
            Log::warning('Turnstile secret key is not configured. Skipping verification.');
            return $next($request);
        }

        try {
            $response = Http::asForm()->post('https://challenges.cloudflare.com/turnstile/v0/siteverify', [
                'secret' => $secretKey,
                'response' => $token,
                'remoteip' => $request->ip(),
            ]);

            $result = $response->json();

            if (!($result['success'] ?? false)) {
                Log::warning('Turnstile verification failed', [
                    'error-codes' => $result['error-codes'] ?? [],
                    'ip' => $request->ip(),
                ]);

                return response()->json([
                    'message' => __('CAPTCHA verification failed. Please try again.'),
                    'errors' => [
                        'turnstile' => [__('CAPTCHA verification failed. Please try again.')],
                    ],
                ], 422);
            }
        } catch (\Exception $e) {
            Log::error('Turnstile verification request failed', [
                'error' => $e->getMessage(),
            ]);

            // Fail open — allow the request if the API is unreachable
            // Change to fail closed by returning 422 here if preferred
        }

        return $next($request);
    }
}
