<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CourseEnrollment;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;

class UserAuthController extends Controller
{
    /**
     * Login a user via Sanctum session.
     */
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        if (!Auth::guard('student')->attempt(
            $request->only('email', 'password'),
            $request->boolean('remember')
        )) {
            return response()->json([
                'message' => __('The email or password you entered is incorrect. Please try again.'),
            ], 422);
        }

        $request->session()->regenerate();

        $user = Auth::guard('student')->user();

        return response()->json([
            'data' => [
                'user' => $this->formatUser($user),
            ],
            'message' => __('Welcome back! You have logged in successfully.'),
        ]);
    }

    /**
     * Register a new user.
     */
    public function register(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'phone' => 'nullable|string|max:20',
            'locale' => 'nullable|string|in:ar,en,ku',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'phone' => $request->phone,
            'locale' => $request->locale ?? 'ar',
            'is_active' => true,
        ]);

        Auth::guard('student')->login($user, true);
        $request->session()->regenerate();

        return response()->json([
            'data' => [
                'user' => $this->formatUser($user),
            ],
            'message' => __('Your account has been created successfully. Welcome!'),
        ], 201);
    }

    /**
     * Logout the current user.
     */
    public function logout(Request $request): JsonResponse
    {
        Auth::guard('student')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json([
            'message' => __('You have been logged out. See you soon!'),
        ]);
    }

    /**
     * Get the authenticated user's profile.
     */
    public function profile(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'message' => __('Please log in to continue.'),
            ], 401);
        }

        return response()->json([
            'user' => $this->formatUser($user),
        ]);
    }

    /**
     * Update the user's profile.
     */
    public function updateProfile(Request $request): JsonResponse
    {
        $user = $request->user();

        $request->validate([
            'name' => 'sometimes|string|max:255',
            'phone' => 'nullable|string|max:20',
        ]);

        $user->update($request->only(['name', 'phone']));

        return response()->json([
            'data' => $user->fresh(),
            'message' => __('Your profile has been updated successfully.'),
        ]);
    }

    /**
     * Request a password-reset OTP.
     */
    public function forgotPassword(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json([
                'message' => __('We couldn\'t find an account with that email. Please check and try again.'),
            ], 422);
        }

        // Generate a 6-digit OTP and store it in cache for 15 minutes
        $otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        cache()->put('password_reset_otp_' . $request->email, $otp, now()->addMinutes(15));

        // In production, you would send this via email/SMS
        // For now, return it in the response for development
        return response()->json([
            'data' => [
                'otp_code' => $otp,
            ],
            'message' => __('A verification code has been sent. Please check your email.'),
        ]);
    }

    /**
     * Reset password using OTP.
     */
    public function resetPassword(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email',
            'otp_code' => 'required|string|size:6',
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $cachedOtp = cache()->get('password_reset_otp_' . $request->email);

        if (!$cachedOtp || $cachedOtp !== $request->otp_code) {
            return response()->json([
                'message' => __('The verification code is invalid or has expired. Please request a new one.'),
            ], 422);
        }

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json([
                'message' => __('Account not found.'),
            ], 422);
        }

        $user->update([
            'password' => Hash::make($request->password),
        ]);

        cache()->forget('password_reset_otp_' . $request->email);

        return response()->json([
            'message' => __('Your password has been reset successfully. You can now log in.'),
        ]);
    }

    /**
     * Update the user's email (with OTP verification).
     */
    public function updateEmail(Request $request): JsonResponse
    {
        $request->validate([
            'new_email' => 'required|email|unique:users,email',
            'current_password' => 'required|string',
        ]);

        $user = $request->user();

        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'message' => __('The current password is incorrect.'),
            ], 422);
        }

        $otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        cache()->put('email_change_otp_' . $user->id, [
            'otp' => $otp,
            'new_email' => $request->new_email,
        ], now()->addMinutes(15));

        return response()->json([
            'data' => [
                'otp_code' => $otp,
            ],
            'message' => __('A verification code has been sent to your new email.'),
        ]);
    }

    /**
     * Verify email change OTP.
     */
    public function verifyEmailOTP(Request $request): JsonResponse
    {
        $request->validate([
            'otp_code' => 'required|string|size:6',
        ]);

        $user = $request->user();
        $cached = cache()->get('email_change_otp_' . $user->id);

        if (!$cached || $cached['otp'] !== $request->otp_code) {
            return response()->json([
                'message' => __('The verification code is invalid or has expired. Please request a new one.'),
            ], 422);
        }

        $user->update(['email' => $cached['new_email']]);
        cache()->forget('email_change_otp_' . $user->id);

        return response()->json([
            'message' => __('Your email has been updated successfully.'),
        ]);
    }

    /**
     * Update the user's password.
     */
    public function updatePassword(Request $request): JsonResponse
    {
        $request->validate([
            'current_password' => 'required|string',
            'new_password' => ['required', Rules\Password::defaults()],
            'new_password_confirmation' => 'required|same:new_password',
        ]);

        $user = $request->user();

        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'message' => __('The current password is incorrect.'),
            ], 422);
        }

        $user->update([
            'password' => Hash::make($request->new_password),
        ]);

        return response()->json([
            'message' => __('Your password has been updated successfully.'),
        ]);
    }

    /**
     * Update the user's locale.
     */
    public function updateLocale(Request $request): JsonResponse
    {
        $request->validate([
            'locale' => 'required|string|in:ar,en,ku',
        ]);

        $user = $request->user();
        $user->update(['locale' => $request->locale]);

        return response()->json([
            'data' => [
                'user' => $this->formatUser($user->fresh()),
            ],
            'message' => __('Your language preference has been updated.'),
        ]);
    }

    public function enrollments(Request $request): JsonResponse
    {
        $user = $request->user();

        return response()->json([
            'data' => [
                'course_ids' => $user->courseEnrollments()->pluck('course_id')->all(),
            ],
        ]);
    }

    /**
     * Get dashboard stats for the user.
     */
    public function dashboardStats(Request $request): JsonResponse
    {
        $user = $request->user();

        return response()->json([
            'data' => [
                'courses_enrolled' => 0,
                'courses_completed' => 0,
                'certificates' => 0,
            ],
        ]);
    }

    protected function formatUser(User $user): array
    {
        return [
            'id' => (string) $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
            'locale' => $user->locale,
            'isVerified' => ! is_null($user->email_verified_at),
            'purchasedCourseIds' => $user->courseEnrollments()->pluck('course_id')->all(),
        ];
    }
}
