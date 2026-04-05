<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('course_certificate_templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained()->cascadeOnDelete()->unique();
            $table->decimal('x1', 8, 6)->default(0.220000);
            $table->decimal('y1', 8, 6)->default(0.440000);
            $table->decimal('x2', 8, 6)->default(0.780000);
            $table->decimal('y2', 8, 6)->default(0.580000);
            $table->string('text_color')->default('#111827');
            $table->unsignedInteger('font_size')->default(42);
            $table->string('font_family')->default('DejaVu Sans');
            $table->string('text_align')->default('center');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('course_certificate_templates');
    }
};
