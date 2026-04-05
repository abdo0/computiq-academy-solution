<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\Cart\CartService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use Throwable;

class SocialAuthController extends Controller
{
    public function __construct(
        protected CartService $cartService,
    ) {}

    protected function supportedProviders(): array
    {
        return ['google', 'github'];
    }

    protected function resolveRequestedRedirect(?string $path): ?string
    {
        if (! is_string($path) || $path === '') {
            return null;
        }

        if (! str_starts_with($path, '/') || str_starts_with($path, '//')) {
            return null;
        }

        return $path;
    }

    protected function failureRedirect(Request $request, string $errorCode, bool $isPopup = false, ?string $redirectTo = null): \Illuminate\Http\Response|RedirectResponse
    {
        if ($isPopup) {
            return response(
                "<script>if(window.opener){window.opener.postMessage('social_login_error:{$errorCode}', '*'); window.close();}else{window.location.href='/login?error={$errorCode}';}</script>"
            )->header('Content-Type', 'text/html');
        }

        $redirectTo = $this->resolveRequestedRedirect($redirectTo);

        if ($redirectTo) {
            $separator = str_contains($redirectTo, '?') ? '&' : '?';

            return redirect($redirectTo.$separator.'auth_error='.$errorCode);
        }

        return redirect('/login?error='.$errorCode);
    }

    protected function missingProviderConfig(string $provider): bool
    {
        return blank(config("services.{$provider}.client_id"))
            || blank(config("services.{$provider}.client_secret"))
            || blank(config("services.{$provider}.redirect"));
    }

    public function redirect(string $provider): JsonResponse
    {
        if (! in_array($provider, $this->supportedProviders(), true)) {
            return response()->json([
                'message' => __('Unsupported provider.'),
            ], 422);
        }

        if ($this->missingProviderConfig($provider)) {
            return response()->json([
                'message' => __('The :provider login integration is not configured yet.', [
                    'provider' => ucfirst($provider),
                ]),
            ], 500);
        }

        $redirectTo = $this->resolveRequestedRedirect(request()->query('redirect_to'));
        $isPopup = (bool) request()->query('popup');

        $statePayload = base64_encode(json_encode([
            'redirect_to' => $redirectTo,
            'popup' => $isPopup,
        ]));

        $redirectUrl = Socialite::driver($provider)
            ->stateless()
            ->with(['state' => $statePayload])
            ->redirect()
            ->getTargetUrl();

        return response()->json([
            'data' => [
                'redirect_url' => $redirectUrl,
            ],
        ]);
    }

    public function callback(string $provider, Request $request): \Illuminate\Http\Response|RedirectResponse
    {
        $statePayload = $request->query('state');
        $isPopup = false;
        $redirectTo = null;

        if ($statePayload) {
            $stateData = json_decode(base64_decode($statePayload), true);
            if (is_array($stateData)) {
                $isPopup = !empty($stateData['popup']);
                $redirectTo = $stateData['redirect_to'] ?? null;
            }
        }

        if (! in_array($provider, $this->supportedProviders(), true)) {
            return $this->failureRedirect($request, 'unsupported_provider', $isPopup, $redirectTo);
        }

        if ($this->missingProviderConfig($provider)) {
            return $this->failureRedirect($request, 'social_provider_not_configured', $isPopup, $redirectTo);
        }

        try {
            $socialUser = Socialite::driver($provider)->stateless()->user();
            $email = $socialUser->getEmail();

            if (! $email) {
                return $this->failureRedirect($request, 'social_email_required', $isPopup, $redirectTo);
            }

            $user = User::query()
                ->where('provider', $provider)
                ->where('provider_id', (string) $socialUser->getId())
                ->first();

            if (! $user) {
                $user = User::query()->where('email', $email)->first();
            }

            if (! $user) {
                $user = User::create([
                    'name' => $socialUser->getName() ?: $socialUser->getNickname() ?: 'User',
                    'email' => $email,
                    'password' => Hash::make(Str::random(40)),
                    'locale' => 'ar',
                    'is_active' => true,
                    'active_role' => 'student',
                    'provider' => $provider,
                    'provider_id' => (string) $socialUser->getId(),
                    'email_verified_at' => now(),
                ]);
            } else {
                $user->forceFill([
                    'provider' => $provider,
                    'provider_id' => (string) $socialUser->getId(),
                    'active_role' => $user->resolvedActiveRole(),
                ])->save();
            }

            $user->ensureDefaultAppRole();

            Auth::guard('student')->login($user, true);
            $request->session()->regenerate();
            $this->cartService->mergeGuestCartIntoUser($request, $user);

            if ($isPopup) {
                return response(
                    "<script>if(window.opener){window.opener.postMessage('social_login_success', '*'); window.close();}else{window.location.href='/dashboard';}</script>"
                )->header('Content-Type', 'text/html');
            }

            $redirectTo = $this->resolveRequestedRedirect($redirectTo);

            return redirect($redirectTo ?: '/dashboard');
        } catch (Throwable $exception) {
            report($exception);

            return $this->failureRedirect($request, 'social_auth_failed', $isPopup, $redirectTo);
        }
    }
}
