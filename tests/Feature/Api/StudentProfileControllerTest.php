<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class StudentProfileControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function createUser(): User
    {
        return User::create([
            'name' => 'Profile User',
            'email' => 'profile@example.com',
            'password' => bcrypt('password'),
            'is_active' => true,
            'locale' => 'en',
            'active_role' => 'student',
        ]);
    }

    public function test_show_returns_default_student_profile_payload(): void
    {
        Sanctum::actingAs($this->createUser());

        $this->getJson('/api/v1/user/student-profile')
            ->assertOk()
            ->assertJsonPath('data.university', null)
            ->assertJsonPath('data.job_available', false)
            ->assertJsonPath('data.internship_available', false)
            ->assertJsonPath('data.skills', [])
            ->assertJsonPath('data.projects', []);
    }

    public function test_update_creates_or_updates_student_profile(): void
    {
        $user = $this->createUser();
        Sanctum::actingAs($user);

        $this->postJson('/api/v1/user/student-profile', [
            'university' => 'Computiq University',
            'department' => 'Engineering',
            'degree' => 'BSc',
            'start_year' => 2022,
            'graduation_year' => 2026,
            'academic_status' => 'Senior',
            'headline' => 'Frontend Developer',
            'short_bio' => 'Building products for the web.',
            'city' => 'Baghdad',
            'country' => 'Iraq',
            'preferred_role' => 'Frontend Developer',
            'preferred_city' => 'Erbil',
            'job_available' => true,
            'internship_available' => false,
            'linkedin_url' => 'https://linkedin.com/in/student',
            'github_url' => 'https://github.com/student',
            'portfolio_url' => 'https://student.test',
            'skills' => ['React', 'Laravel'],
            'projects' => ['Learning portal', 'Portfolio site'],
        ])->assertOk()
            ->assertJsonPath('data.university', 'Computiq University')
            ->assertJsonPath('data.job_available', true)
            ->assertJsonPath('data.skills.0', 'React');

        $this->assertDatabaseHas('student_profiles', [
            'user_id' => $user->id,
            'university' => 'Computiq University',
            'preferred_role' => 'Frontend Developer',
        ]);
    }
}
