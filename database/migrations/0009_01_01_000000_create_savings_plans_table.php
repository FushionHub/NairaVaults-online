<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('savings_plans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('fiat_account_id')->nullable();
            $table->unsignedBigInteger('crypto_wallet_id')->nullable();
            $table->string('name')->nullable();
            $table->decimal('amount', 20, 8);
            $table->string('currency', 10);
            $table->decimal('interest_rate', 5, 4);
            $table->date('start_date');
            $table->date('maturity_date');
            $table->string('status', 20)->default('active');
            $table->decimal('penalty_rate', 5, 4)->default(0.0500);
            $table->timestamps();

            $table->foreign('fiat_account_id')->references('id')->on('fiat_accounts')->nullOnDelete();
            $table->foreign('crypto_wallet_id')->references('id')->on('crypto_wallets')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('savings_plans');
    }
};
