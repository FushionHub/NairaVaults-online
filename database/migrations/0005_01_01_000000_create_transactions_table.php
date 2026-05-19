<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->nullableMorphs('transactionable');
            $table->string('type', 30);
            $table->decimal('amount', 20, 8);
            $table->string('currency', 10);
            $table->decimal('fee', 20, 8)->default(0);
            $table->string('status', 20)->default('pending');
            $table->string('reference')->unique();
            $table->string('gateway_reference')->nullable();
            $table->string('direction', 10);
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'type']);
            $table->index(['user_id', 'status']);
            $table->index('gateway_reference');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
