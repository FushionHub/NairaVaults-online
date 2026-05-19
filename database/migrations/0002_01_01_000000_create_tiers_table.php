<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tiers', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->decimal('daily_transaction_limit', 20, 8)->default(0);
            $table->decimal('monthly_transaction_limit', 20, 8)->default(0);
            $table->decimal('single_transfer_limit', 20, 8)->default(0);
            $table->decimal('upgrade_fee', 20, 8)->default(0);
            $table->json('benefits')->nullable();
            $table->json('supported_currencies')->nullable();
            $table->integer('max_virtual_cards')->default(1);
            $table->integer('max_savings_plans')->default(3);
            $table->decimal('referral_bonus_percentage', 5, 4)->default(0.0050);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tiers');
    }
};
