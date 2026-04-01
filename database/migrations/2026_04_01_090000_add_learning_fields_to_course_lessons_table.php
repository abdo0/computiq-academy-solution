<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('course_lessons', function (Blueprint $table) {
            $table->json('description')->nullable()->after('title');
            $table->string('content_type')->default('video')->after('duration_minutes');
            $table->string('video_source_type')->nullable()->after('content_type');
            $table->string('video_provider')->nullable()->after('video_source_type');
            $table->text('video_url')->nullable()->after('video_provider');
            $table->text('embed_url')->nullable()->after('video_url');
            $table->boolean('is_active')->default(true)->after('is_free');
        });
    }

    public function down(): void
    {
        Schema::table('course_lessons', function (Blueprint $table) {
            $table->dropColumn([
                'description',
                'content_type',
                'video_source_type',
                'video_provider',
                'video_url',
                'embed_url',
                'is_active',
            ]);
        });
    }
};
