<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('virtual_cards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('fiat_account_id')->constrained()->cascadeOnDelete();
            $table->string('masked_pan');
            $table->string('expiry_month', 2);
            $table->string('expiry_year', 4);
            $table->text('cvv_reference');
            $table->string('card_holder_name');
            $table->string('issuer_card_id')->nullable();
            $table->string('card_type', 20)->default('visa');
            $table->string('status', 20)->default('active');
            $table->decimal('balance', 20, 8)->default(0);
            $table->string('currency', 5)->default('USD');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('virtual_cards');
    }
};
