<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('course_reviews', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->after('course_id')->constrained()->nullOnDelete();
            $table->unique(['course_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::table('course_reviews', function (Blueprint $table) {
            $table->dropUnique(['course_id', 'user_id']);
            $table->dropConstrainedForeignId('user_id');
        });
    }
};
