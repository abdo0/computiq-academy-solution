<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->string('promo_video_source_type')->nullable()->after('image');
            $table->string('promo_video_provider')->nullable()->after('promo_video_source_type');
            $table->text('promo_video_url')->nullable()->after('promo_video_provider');
            $table->text('promo_embed_url')->nullable()->after('promo_video_url');
        });
    }

    public function down(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->dropColumn([
                'promo_video_source_type',
                'promo_video_provider',
                'promo_video_url',
                'promo_embed_url',
            ]);
        });
    }
};
