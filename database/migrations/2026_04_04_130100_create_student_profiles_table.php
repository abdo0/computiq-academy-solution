<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('university')->nullable();
            $table->string('department')->nullable();
            $table->string('degree')->nullable();
            $table->year('start_year')->nullable();
            $table->year('graduation_year')->nullable();
            $table->string('academic_status')->nullable();
            $table->string('headline')->nullable();
            $table->text('short_bio')->nullable();
            $table->string('city')->nullable();
            $table->string('country')->nullable();
            $table->string('preferred_role')->nullable();
            $table->string('preferred_city')->nullable();
            $table->boolean('job_available')->default(false);
            $table->boolean('internship_available')->default(false);
            $table->string('linkedin_url', 500)->nullable();
            $table->string('github_url', 500)->nullable();
            $table->string('portfolio_url', 500)->nullable();
            $table->json('skills')->nullable();
            $table->json('projects')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_profiles');
    }
};
