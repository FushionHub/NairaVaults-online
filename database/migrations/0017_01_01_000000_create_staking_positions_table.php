<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('staking_positions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('coin_symbol', 20);
            $table->decimal('amount', 20, 8);
            $table->decimal('apy', 5, 4);
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->decimal('rewards', 20, 8)->default(0);
            $table->string('status', 20)->default('active');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('staking_positions');
    }
};
