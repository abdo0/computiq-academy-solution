<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->foreignId('promo_code_id')
                ->nullable()
                ->after('payment_method_id')
                ->constrained()
                ->nullOnDelete();
            $table->string('promo_code')->nullable()->after('promo_code_id');
            $table->enum('discount_type', ['fixed', 'percentage'])->nullable()->after('promo_code');
            $table->decimal('discount_value', 12, 2)->default(0)->after('discount_type');
            $table->decimal('discount_amount', 12, 2)->default(0)->after('discount_value');
            $table->decimal('subtotal_before_discount', 12, 2)->default(0)->after('discount_amount');
            $table->decimal('subtotal_after_discount', 12, 2)->default(0)->after('subtotal_before_discount');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropConstrainedForeignId('promo_code_id');
            $table->dropColumn([
                'promo_code',
                'discount_type',
                'discount_value',
                'discount_amount',
                'subtotal_before_discount',
                'subtotal_after_discount',
            ]);
        });
    }
};
