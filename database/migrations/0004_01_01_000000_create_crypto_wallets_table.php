<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('crypto_wallets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('coin_symbol', 20);
            $table->string('public_address');
            $table->text('encrypted_priv_key');
            $table->string('privy_wallet_id')->nullable();
            $table->boolean('imported')->default(false);
            $table->decimal('balance', 20, 8)->default(0);
            $table->timestamps();

            $table->unique(['user_id', 'coin_symbol']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('crypto_wallets');
    }
};
