<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('learning_paths', function (Blueprint $table) {
            $table->id();
            $table->json('title');
            $table->json('description')->nullable();
            $table->string('slug')->unique();
            $table->string('image')->nullable();
            $table->string('icon')->nullable();
            $table->string('color')->nullable();
            $table->integer('estimated_hours')->nullable();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('learning_path_course', function (Blueprint $table) {
            $table->id();
            $table->foreignId('learning_path_id')->constrained('learning_paths')->cascadeOnDelete();
            $table->foreignId('course_id')->constrained('courses')->cascadeOnDelete();
            $table->integer('sort_order')->default(0);
            
            $table->unique(['learning_path_id', 'course_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('learning_path_course');
        Schema::dropIfExists('learning_paths');
    }
};
