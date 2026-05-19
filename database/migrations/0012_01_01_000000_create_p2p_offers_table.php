<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('p2p_offers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('creator_user_id');
            $table->unsignedBigInteger('counterparty_user_id')->nullable();
            $table->string('coin_symbol', 20);
            $table->decimal('amount', 20, 8);
            $table->decimal('rate_per_unit', 20, 8);
            $table->decimal('total_fiat', 20, 8);
            $table->string('currency', 10);
            $table->string('direction', 10);
            $table->string('payment_method')->nullable();
            $table->string('status', 20)->default('open');
            $table->string('escrow_reference')->nullable();
            $table->timestamp('dispute_raised_at')->nullable();
            $table->timestamps();

            $table->foreign('creator_user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('counterparty_user_id')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('p2p_offers');
    }
};
