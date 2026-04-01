<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('course_exams', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_module_id')->unique()->constrained()->cascadeOnDelete();
            $table->json('title');
            $table->unsignedInteger('pass_mark')->default(70);
            $table->unsignedInteger('max_attempts')->nullable();
            $table->unsignedInteger('time_limit_minutes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('course_exam_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_exam_id')->constrained()->cascadeOnDelete();
            $table->json('question');
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('course_exam_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_exam_question_id')->constrained()->cascadeOnDelete();
            $table->json('option_text');
            $table->boolean('is_correct')->default(false);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('course_exam_options');
        Schema::dropIfExists('course_exam_questions');
        Schema::dropIfExists('course_exams');
    }
};
