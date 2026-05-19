<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fiat_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('currency', 5);
            $table->decimal('balance', 20, 8)->default(0);
            $table->string('virtual_account_number')->nullable();
            $table->string('virtual_account_bank')->nullable();
            $table->string('virtual_account_name')->nullable();
            $table->string('gateway')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['user_id', 'currency']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fiat_accounts');
    }
};
