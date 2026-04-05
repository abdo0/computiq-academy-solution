<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\StudentProfile;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StudentProfileController extends Controller
{
    public function show(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        $profile = $user->studentProfile ?: new StudentProfile();

        return response()->json([
            'data' => $this->formatProfile($profile),
        ]);
    }

    public function update(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $validated = $request->validate([
            'university' => ['nullable', 'string', 'max:255'],
            'department' => ['nullable', 'string', 'max:255'],
            'degree' => ['nullable', 'string', 'max:255'],
            'start_year' => ['nullable', 'integer', 'between:1950,2100'],
            'graduation_year' => ['nullable', 'integer', 'between:1950,2100'],
            'academic_status' => ['nullable', 'string', 'max:255'],
            'headline' => ['nullable', 'string', 'max:255'],
            'short_bio' => ['nullable', 'string', 'max:2000'],
            'city' => ['nullable', 'string', 'max:255'],
            'country' => ['nullable', 'string', 'max:255'],
            'preferred_role' => ['nullable', 'string', 'max:255'],
            'preferred_city' => ['nullable', 'string', 'max:255'],
            'job_available' => ['sometimes', 'boolean'],
            'internship_available' => ['sometimes', 'boolean'],
            'linkedin_url' => ['nullable', 'url', 'max:500'],
            'github_url' => ['nullable', 'url', 'max:500'],
            'portfolio_url' => ['nullable', 'url', 'max:500'],
            'skills' => ['nullable', 'array'],
            'skills.*' => ['string', 'max:255'],
            'projects' => ['nullable', 'array'],
            'projects.*' => ['string', 'max:500'],
        ]);

        $profile = StudentProfile::query()->updateOrCreate(
            ['user_id' => $user->id],
            [
                ...$validated,
                'job_available' => (bool) ($validated['job_available'] ?? false),
                'internship_available' => (bool) ($validated['internship_available'] ?? false),
                'skills' => array_values($validated['skills'] ?? []),
                'projects' => array_values($validated['projects'] ?? []),
            ]
        );

        return response()->json([
            'data' => $this->formatProfile($profile),
            'message' => __('Profile updated successfully.'),
        ]);
    }

    protected function formatProfile(StudentProfile $profile): array
    {
        return [
            'university' => $profile->university,
            'department' => $profile->department,
            'degree' => $profile->degree,
            'start_year' => $profile->start_year,
            'graduation_year' => $profile->graduation_year,
            'academic_status' => $profile->academic_status,
            'headline' => $profile->headline,
            'short_bio' => $profile->short_bio,
            'city' => $profile->city,
            'country' => $profile->country,
            'preferred_role' => $profile->preferred_role,
            'preferred_city' => $profile->preferred_city,
            'job_available' => (bool) $profile->job_available,
            'internship_available' => (bool) $profile->internship_available,
            'linkedin_url' => $profile->linkedin_url,
            'github_url' => $profile->github_url,
            'portfolio_url' => $profile->portfolio_url,
            'skills' => $profile->skills ?? [],
            'projects' => $profile->projects ?? [],
        ];
    }
}
