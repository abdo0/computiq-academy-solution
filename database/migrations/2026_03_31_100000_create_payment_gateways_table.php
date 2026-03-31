<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_gateways', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->json('name');
            $table->enum('type', ['card', 'mobile_wallet', 'bank', 'money_transfer'])->default('card');
            $table->json('description')->nullable();
            $table->decimal('processing_fee_percentage', 8, 2)->default(0);
            $table->decimal('processing_fee_fixed', 12, 2)->default(0);
            $table->json('configuration')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0)->index();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_gateways');
    }
};
